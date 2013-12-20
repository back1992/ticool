<?php
/**
 * RPC Wrapper class to convert from Zoo Class Objects to XApp-Connect-Types
 *
 * @package XApp-Connect\Joomla
 * @class XCZoo
 * @error @TODO
 * @author  mc007
 * @autor Luis Ramos
 */
class XCZoo extends Xapp_Joomla_Plugin
{
    /**
     * option to specify a cache prefix, currently this also used to for prefixing the Lucene indexer store
     *
     * @const DEFAULT_NS
     */
    var $CACHE_NS = 'ZOO_CACHE_NS';

    /**
     * common prefix for the zoo's folder prefix
     */
    public static $ZOO_FOLDER_PREFIX = 'components/com_zoo/';

    /***
     * Cached instance of Zoo - Model - Helper - Class
     * @var _ZooModel
     */
    private $zooModel;
    /***
     * Getter
     * @return _ZooModel
     */
    private function getModel(){
        return $this->zooModel;
    }

    /***
     * Cached Zoo-System-App, same instance as in _ZooModel::zooApp.
     * @var
     */
    private $zooApp;


    /**************************************************************************************************************/
    /*  Wrappers about the Zoo 'Application' class                                                                */
    /*                                                                                                            */

    /***
     * Get a specific Zoo application
     * @jsonpCall : http://0.0.0.0/zoo254/components/com_xas/xapp/index.php?service=XCZoo.testMethod&method=getApplication&id=1&callback=as (Blog Application)
     * @param $zooAppId
     * @return null
     */
    public function getApplication($zooAppId=1){
        $app = $this->getModel()->getApplication($zooAppId);
        return $app;
    }

    /***
     * Get all Zoo applications.
     * @jsonpCall : http://0.0.0.0/zoo254/components/com_xas/xapp/index.php?service=XCZoo.testMethod&method=getApplications&callback=as
     * @param $zooAppId
     * @return null
     */
    public function getApplications($params='{}') {

        //setup internals
        $this->onZooCall();

        $this->getCurrentUser();


        //  this only for development doesn't happen in real world. This is needed if call through a JSONP like :
        //  http://0.0.0.0/zoo254/components/com_xas/xapp/index.php?service=XCZoo.testMethod&method=getApplications&callback=as
        //  Check the parameters for JSON-RPC-Call. If its invalid, we emulate the needed parameters
        $isRPCCall = $this->isRPC($params);
        if(!$isRPCCall){
            //http://192.168.1.37/zoo254//index.php?option=com_xas&view=rpc&service=XCZoo.customTypeQuery&params={%22DSUID%22:%22bd6b4233-8b40-4c8d-a0f8-3fa71ded544d%22,%22BASEREF%22:%22http://192.168.1.37/zoo254/%22,%22REFID%22:%22zoo%22,%22CTYPE%22:%22ZooApplication%22,%22APPID%22:%22myeventsapp1d0%22,%22RT_CONFIG%22:%22debug%22,%22UUID%22:%2211166763-e89c-44ba-aba7-4e9f4fdf97a9%22,%22SERVICE_HOST%22:%22http://mc007ibi.dyndns.org:8080/XApp-portlet/%22,%22IMAGE_RESIZE_URL%22:%22http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=%22,%22SOURCE_TYPE%22:%22zooApplication%22,%22SCREEN_WIDTH%22:320}&callback=dojo.io.script.jsonp_dojoIoScript3._jsonpCallback
            $params = $this->_createRPCDefaultParameters('zoo','zooApplication','ZooApplication');
        }

        // now business as usual :

        //setup internal parameters and custom type, must be done always in a public RPC function
        $this->onBeforeCall($params);

        if($this->xcRefId!=='zoo'){
            return $this->emptyList();
        }


        $applications  = (array)$this->getModel()->getApplications();
        if($applications==null || count($applications)){
            $this->log('no zoo applictions!');
        }



        /***
         * complete required fields directly in the application object
         */
        foreach($applications as  $app){

            //adjust icon url : see : administrator/components/com_zoo/classes/application.php
            //$app->{XC_ICON_URL}= $this->cleanUrl($this->siteUrl(). $app->getIcon());
            $app->{XC_ICON_URL}= $this->completeUrlSafe($app->getIcon());

            //error_log('app icon url ' . $app->{XC_ICON_URL});
            //error_log('icon url : ' . $app->getIcon());
            //render number of categories in xapp's date field
            //$app->{XC_DATE_STRING}= count($app->getCategoryCount()) . ' Categories';
            $app->{XC_DATE_STRING}= count($this->getModel()->_getCategories($app->id)) . ' Categories';

            //try to get some text, prefer the application's 'subtitle' over the description
            $appText = $app->params->{'content.subtitle'};
            if($appText!=null && strlen($appText)>0)
            {
                $app->{XC_DESCRIPTION} = $appText;
            }
        }

        /***
         * Next step is to create a schema from default
         * @remarks : this must be done, if we don't specifiy a schema in xapp-studio for a custom type
         */
            //take a standard list schema, see xapp/connect/filter/SchemaFactory.php
            $standardListSchema = Xapp_Connect_Schema_Factory::stdCList(Xapp_Connect_Schema_Factory::SCHEMA_FORMAT_JSON_STRING);

            //remap schema identifiers to Zoo specific fields
            $identifierMap = array(
                XC_REF_ID=>'%name%',  //take name field
                XC_TITLE=>'%name%', //take name
                XC_GROUP_ID=>'%name%', //
                XC_SOURCE_TYPE=>'zooApplication',//hard set
                XC_INTRO=>'%description%',       //
                XC_DESCRIPTION=>'%description%',  //,
                XC_LIST_TITLE =>'Applications'
            );

            //replace identifiers on the schema
            $standardListSchema= Xapp_Connect_Schema_Factory::replaceIdentifiers($standardListSchema,$identifierMap);

            $standardListSchema = str_replace(XC_LIST_TITLE,'Applications',$standardListSchema);

            //update the custom type with the new schema
            CustomTypesUtils::setCIStringValue($this->xcType,'schemas',json_decode($standardListSchema));

            //update local copy of schemas
            $this->xcSchemas = json_decode($standardListSchema);



        /***
         * Now we can perform the custom type schema filter on the applications
         */
        $res = $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $applications);

        return $res;
    }
    /**************************************************************************************************************/
    /*  Wrappers about the Zoo 'Category' class                                                                */
    /*                                                                                                            */

    /***
     * Get all categories of Zoo application.
     * @jsonpCall : http://0.0.0.0/zoo254/components/com_xas/xapp/index.php?service=XCZoo.testMethod&method=getCategories&callback=as
     * @appCall : http://192.168.1.37/zoo254/index.php?option=com_xas&view=rpc&service=XCZoo.customTypeQuery&params={"DSUID":"bd6b4233-8b40-4c8d-a0f8-3fa71ded544d","BASEREF":"http://192.168.1.37/zoo254/","REFID":"1","CTYPE":"ZooCategory","APPID":"myeventsapp1d0","RT_CONFIG":"debug","UUID":"11166763-e89c-44ba-aba7-4e9f4fdf97a9","SERVICE_HOST":"http://mc007ibi.dyndns.org:8080/XApp-portlet/","IMAGE_RESIZE_URL":"http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=","SOURCE_TYPE":"zooCategory","SCREEN_WIDTH":320}&callback=bla
     * @appCall to retrieve all categories for an application : http://192.168.1.37/zoo254/index.php?option=com_xas&view=rpc&service=XCZoo.customTypeQuery&params={%22DSUID%22:%22bd6b4233-8b40-4c8d-a0f8-3fa71ded544d%22,%22BASEREF%22:%22http://192.168.1.37/zoo254/%22,%22REFID%22:%22Blog%22,%22CTYPE%22:%22ZooCategory%22,%22APPID%22:%22myeventsapp1d0%22,%22RT_CONFIG%22:%22debug%22,%22UUID%22:%2211166763-e89c-44ba-aba7-4e9f4fdf97a9%22,%22SERVICE_HOST%22:%22http://mc007ibi.dyndns.org:8080/XApp-portlet/%22,%22IMAGE_RESIZE_URL%22:%22http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=%22,%22SOURCE_TYPE%22:%22zooCategory%22,%22SCREEN_WIDTH%22:320}&callback=bla
     * @param $zooAppId
     * @return null
     */
    public function getCategories($params='{}') {

        //setup internals
        $this->onZooCall();

        $isRPCCall = $this->isRPC($params);
        if(!$isRPCCall){
            $params = $this->_createRPCDefaultParameters('zoo','zooCategory','ZooCategory');
        }

        //now business as usual :
        //setup internal parameters and custom type, must be done always in a public RPC function
        $this->onBeforeCall($params);


        //$applications  = (array)$this->getModel()->getApplications();


        /***
         * Now we need to figure out whether the passed refId is a string or a number :
         *  in case its a string, we need to get the zoo application, its referring to all categories of zoo application
         *  in case its a number, its the category id
         */

        $zooApp = null;
        $refIdInteger = intval($this->xcRefId);
        $categories = null;

        if($refIdInteger==0 && is_string($this->xcRefId) && strlen($this->xcRefId)){
            //we've got a string which means, its the applications name
            $zooApp = $this->getModel()->getApplicationByName($this->xcRefId);

        }

        //bad, no zoo app
        if($zooApp==null){

            //in this case, the app wants to retrieve the sub categories
            if($refIdInteger>0){

                //we need to find the zoo application first, given the category id
                $zooApp = $this->getModel()->getApplicationByCategory($refIdInteger);
                if($zooApp!=null){
                }else{
                    return $this->emptyList();
                }

            }

        }

        $isRootCategory=true;
        if($zooApp!=null){

            //no category id given, return all
            if($refIdInteger==0){
                $categories = $zooApp->getCategories(true,true,null);

            }else{
                $categories = $this->getModel()->getCategoriesByParent($refIdInteger);
                $isRootCategory=false;
            }

        }else{
            $this->log("Have no zoo application");
            return $this->emptyList();
        }

        if($categories){
            /*return $categories;*/
        }else{
            return $this->emptyList();
        }


        /***
         * complete required fields directly in the application object
         */
        foreach($categories as  $cat){
            $this->_completeCategoryCompact($cat);
        }


        /***
         * Next step is to create a schema from default
         * @remarks : this must be done, if we don't specifiy a schema in xapp-studio for a custom type
         */
        //take a standard list schema, see xapp/connect/filter/SchemaFactory.php
        $standardListSchema = Xapp_Connect_Schema_Factory::stdCList(Xapp_Connect_Schema_Factory::SCHEMA_FORMAT_JSON_STRING);

        //remap schema identifiers to Zoo specific fields
        $identifierMap = array(
            XC_REF_ID=>'%id%',  //take id field
            XC_TITLE=>'%name%', //take name
            XC_GROUP_ID=>'%id%', //the application item's group id is hard set to 'zoo'
            XC_SOURCE_TYPE=>'zooCategory',//hard set
            XC_INTRO=>'%description%',       //
            XC_DESCRIPTION=>'%description%',  //,
            XC_LIST_TITLE=>'Categories'
        );

        //replace identifiers on the schema
        $standardListSchema= Xapp_Connect_Schema_Factory::replaceIdentifiers($standardListSchema,$identifierMap);

        //update title of the list
        if($isRootCategory){
            $standardListSchema = str_replace(XC_LIST_TITLE,'Categories',$standardListSchema);
        }else{
            $parentCategory = $this->getModel()->getCategoryById($this->xcRefId);
            if($parentCategory){
                $standardListSchema = str_replace(XC_LIST_TITLE,$parentCategory->name,$standardListSchema);
            }
        }

        //update the custom type with the new schema
        CustomTypesUtils::setCIStringValue($this->xcType,'schemas',json_decode($standardListSchema));

        //update local copy of schemas
        $this->xcSchemas = json_decode($standardListSchema);

        /***
         * Now we can perform the custom type schema filter on the applications
         */
        $res = $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $categories);

        return $res;
    }

    /***
     * @param $zooCategory
     * @return null
     */
    private function _completeCategoryCompact($zooCategory){
        $iconUrl = null;
        $appText = null;
        if($zooCategory==null){
            return null;
        }
        $categoryParams = $zooCategory->getParams('site');
        if($categoryParams){

            //get the category text, try 1st the headline, then the description
            $itemText = $categoryParams->{'content.subtitle'};

            if($itemText!=null && strlen($itemText)>0)
            {
                $zooCategory->{XC_DESCRIPTION} = $itemText;
            }else{
                $zooCategory->{XC_DESCRIPTION} = trim(strip_tags($zooCategory->description));
            }

            //get the category icon, try 1st params, then description
            $itemIcon = $categoryParams->{'content.image'};
            if($itemIcon!=null && strlen($itemIcon)>0)
            {
                $zooCategory->{XC_ICON_URL}=$this->completeUrl($itemIcon);
            }else{
                $iconUrl = xapp_findPicture($zooCategory->description);
                if($iconUrl){
                    $zooCategory->{XC_ICON_URL}=$this->completeUrl($iconUrl);
                }
            }

            //Show item count
            $zooCategory->{XC_DATE_STRING}= $zooCategory->totalItemCount() . ' Items';
        }
        return $zooCategory;
    }
    /**************************************************************************************************************/
    /*  Wrappers about the Zoo 'Item' class                                                                */
    /**/


    /***
     * @link : http://0.0.0.0/zoo254/components/com_xas/xapp/index.php?service=XCZoo.testMethod&method=getItemDetail&refId=1&callback=as
     * @link : http://192.168.1.37/zoo254/index.php?option=com_xas&view=rpc&service=XCZoo.customTypeQuery&params={%22DSUID%22:%22bd6b4233-8b40-4c8d-a0f8-3fa71ded544d%22,%22BASEREF%22:%22http://192.168.1.37/zoo254/%22,%22REFID%22:%221%22,%22CTYPE%22:%22ZooItemDetail%22,%22APPID%22:%22myeventsapp1d0%22,%22RT_CONFIG%22:%22debug%22,%22UUID%22:%2211166763-e89c-44ba-aba7-4e9f4fdf97a9%22,%22SERVICE_HOST%22:%22http://mc007ibi.dyndns.org:8080/XApp-portlet/%22,%22IMAGE_RESIZE_URL%22:%22http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=%22,%22SOURCE_TYPE%22:%22zooItemDetail%22,%22SCREEN_WIDTH%22:320}&callback=bla
     * @param string $params
     * @return string
     */
    public  function getItemDetail($params='{}'){
        //setup internals
        $this->onZooCall();

        $isRPCCall = $this->isRPC($params);
        if(!$isRPCCall){
            $params = $this->_createRPCDefaultParameters('1','zooItemDetail','ZooItemDetail');
        }

        //now business as usual :
        //setup internal parameters and custom type, must be done always in a public RPC function
        $this->onBeforeCall($params);

        //little switch in case you're developing.
        $raw=false;

        $refIdInteger = intval($this->xcRefId);

        if($raw){
            $refIdInteger=167;

        }

        $zooItemRaw = array();
        $zooItemsFinal = array();
        $zooItem = $this->getModel()->getZooItemById($refIdInteger);
        if($zooItem==null){
            return $this->emptyList();
        }else{
            array_push($zooItemRaw,$zooItem);
        }

        foreach($zooItemRaw as  $item){
            $itemCompleted = $this->_completeItemDetail($item);
            //merge all 'textarea' fields into the xapp item detail description field
            if(xapp_property_exists($zooItem,XC_TEXT_ALL)){
                $description = implode('<br/>',$zooItem->{XC_TEXT_ALL});
                $itemCompleted->{XC_INTRO} = $itemCompleted->{XC_INTRO} . '<br/>';
                $itemCompleted->{XC_INTRO} = $itemCompleted->{XC_INTRO} . $description;
                $picturesAll = '';

                foreach($zooItem->images as $image){
                    $picturesAll.='<img src="' . $image . '"><br/>';

                }
                $itemCompleted->{XC_DESCRIPTION} = $picturesAll . '<br>'. $itemCompleted->{XC_INTRO};
                $itemCompleted->{XC_DESCRIPTION} = stripslashes(htmlMobile($itemCompleted->{XC_DESCRIPTION}));
                $itemCompleted->{XC_PICTURE_ITEMS} = stripslashes(toPictureItems($itemCompleted->{XC_DESCRIPTION},false));

            }
            $item->sparams = $item->getParams('site');

            //now write out some meta stuff like : Author, date, category...

            $subTitleInsertion='';

            if($itemCompleted->{XC_OWNER_REF_STR}){
                $subTitleInsertion=$this->translate('Written by ') . $itemCompleted->{XC_OWNER_REF_STR};
            }

            if($itemCompleted->{XC_DATE_STRING}){
                $subTitleInsertion.=$this->translate(' ') . $itemCompleted->{XC_DATE_STRING};
            }

            if(strlen($subTitleInsertion)>0){
                $html  = '<div class="'.Xapp_Connect_HTML_Mixin::CSS_ITEM_FOOTER_LEFT.'">';
                $html .= $subTitleInsertion . '</div>';
                //$insert .= '<p><span class="Text" style="font-weight:normal">Sales Price : </span><span class="Text">' . $grossValue . '</span></p>';

                //$html = "<span class=\"" . Xapp_Connect_HTML_Mixin::MIXIN_CSS_ITEM_FOOTER_LEFT . "\">" .$subTitleInsertion."<span>";

                Xapp_Connect_HTML_Mixin::addMixin($itemCompleted,Xapp_Connect_HTML_Mixin::MIXIN_QUERY_DETAIL_TITLE_AFTER,Xapp_Connect_HTML_Mixin::MIXIN_PLACEMENT_LAST,$html,'','','');
            }


            array_push($zooItemsFinal,$itemCompleted);
        }

        if($raw){
            return $zooItemsFinal;
        }


        //error_log(json_encode($zooItemsFinal));
        /***
         * Next step is to create a schema from default
         * @remarks : this must be done, if we don't specifiy a schema in xapp-studio for a custom type
         */
        //take a standard list schema, see xapp/connect/filter/SchemaFactory.php
        $standardContentSchema = Xapp_Connect_Schema_Factory::stdCContent(Xapp_Connect_Schema_Factory::SCHEMA_FORMAT_JSON_STRING);

        //remap schema identifiers to Zoo specific fields
        $identifierMap = array(
            XC_REF_ID=>'%id%',  //take id field
            XC_TITLE=>'%name%', //take name
            XC_GROUP_ID=>''.$refIdInteger, //the application item's category id
            XC_SOURCE_TYPE=>'zooItemDetail',//hard set
            XC_INTRO=>'%introText%',       //
            XC_DESCRIPTION=>'%description%'
        );

        //replace identifiers on the schema
        $standardContentSchema= Xapp_Connect_Schema_Factory::replaceIdentifiers($standardContentSchema,$identifierMap);

        //update the custom type with the new schema
        CustomTypesUtils::setCIStringValue($this->xcType,'schemas',json_decode($standardContentSchema));

        //update local copy of schemas
        $this->xcSchemas = json_decode($standardContentSchema);

        $res = $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $zooItemsFinal);
        return $res;
    }

    /**
     * @param string $params
     * @return string
     * @link : http://192.168.1.37/zoo254/index.php?option=com_xas&view=rpc&service=XCZoo.customTypeQuery&params={"DSUID":"bd6b4233-8b40-4c8d-a0f8-3fa71ded544d","BASEREF":"http://192.168.1.37/zoo254/","REFID":"2","CTYPE":"ZooItem","APPID":"myeventsapp1d0","RT_CONFIG":"debug","UUID":"11166763-e89c-44ba-aba7-4e9f4fdf97a9","SERVICE_HOST":"http://mc007ibi.dyndns.org:8080/XApp-portlet/","IMAGE_RESIZE_URL":"http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=","SOURCE_TYPE":"zooItem","SCREEN_WIDTH":320}&callback=bla
     */
    public function getItems($params='{}'){


        //setup internals
        $this->onZooCall();

        $isRPCCall = $this->isRPC($params);
        if(!$isRPCCall){
            $params = $this->_createRPCDefaultParameters('zoo','zooItem','ZooItem');
        }

        //now business as usual :
        //setup internal parameters and custom type, must be done always in a public RPC function
        $this->onBeforeCall($params);

        $raw=false;

        $refIdInteger = intval($this->xcRefId);

        if($raw){
            $refIdInteger=2;
        }


        $zooItems = $this->getModel()->getZooItems($refIdInteger);
        if($zooItems==null){
            return $this->emptyList();
        }

        if($raw){
            /*return $zooItems;*/
        }



        $zooCategory=$this->getModel()->getCategoryById($refIdInteger);
        if($zooCategory){

        }else{
            error_log('have no zoo category : ' . $refIdInteger);
            //return $this->emptyList();
        }


        $zooItemsFinal = array();

        //first round
        foreach($zooItems as  $item){
            array_push($zooItemsFinal,$this->_completeItemCompact($item));
        }


        //return $zooItems[1]->getParams('site');
        if($raw){
            return $zooItemsFinal[0];
        }

        //xapp_console('xapp console message','label','dump', (array)$zooItemsFinal[0]);
        //xapp_dumpObject(xapp_print_json($zooItemsFinal[0]));


        /***
         * Next step is to create a schema from default
         * @remarks : this must be done, if we don't specifiy a schema in xapp-studio for a custom type
         */
        //take a standard list schema, see xapp/connect/filter/SchemaFactory.php
        $standardListSchema = Xapp_Connect_Schema_Factory::stdCList(Xapp_Connect_Schema_Factory::SCHEMA_FORMAT_JSON_STRING);

        //remap schema identifiers to Zoo specific fields
        $identifierMap = array(
            XC_REF_ID=>'%id%',  //take id field
            XC_TITLE=>'%name%', //take name
            XC_GROUP_ID=>''.$refIdInteger, //the application item's category id
            XC_SOURCE_TYPE=>'zooItemDetail',//hard set
            XC_INTRO=>'%introText%',       //
            XC_DESCRIPTION=>'%description%',
            XC_LIST_TITLE=>'Categories'
        );

        //replace identifiers on the schema
        $standardListSchema= Xapp_Connect_Schema_Factory::replaceIdentifiers($standardListSchema,$identifierMap);
        $parentCategory = $this->getModel()->getCategoryById($this->xcRefId);
        if($parentCategory){
            $standardListSchema = str_replace(XC_LIST_TITLE,$parentCategory->name,$standardListSchema);
        }

        //update the custom type with the new schema
        CustomTypesUtils::setCIStringValue($this->xcType,'schemas',json_decode($standardListSchema));



        //update local copy of schemas
        $this->xcSchemas = json_decode($standardListSchema);
        /***
         * Now we can perform the custom type schema filter
         */
        $res = $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $zooItemsFinal);

        return $res;

    }

    /***
    * @param $zooItem
    * @return null
    */
    private function _completeItemCompact($zooItem){
        $iconUrl = null;
        $appText = null;
        if($zooItem==null){
            return null;
        }

        if(method_exists($zooItem,'getParams')){
            $itemParams = $zooItem->getParams('site');

            $zooItem = $this->getModel()->completeZooItemElements($zooItem,$itemParams);

            if($zooItem->images!=null && count($zooItem->images)>0){

                $zooItem->{XC_ICON_URL}=$zooItem->images[0];
            }
        }else{
            $this->log('zoo item has no params method');
        }
        return $zooItem;
    }

    private function array_insert(&$array, $value, $index)
    {
        return $array = array_merge(array_splice($array, max(0, $index - 1)), array($value), $array);
    }
    /***
     * @param $zooItem
     * @return null
     */
    private function _completeItemDetail($zooItem){
        $iconUrl = null;
        $appText = null;

        if($zooItem==null){
            return null;
        }

        if(method_exists($zooItem,'getParams')){
            $itemParams = $zooItem->getParams('site');

            $zooItem = $this->getModel()->completeZooItemElements($zooItem,$itemParams.true);

            if($zooItem->images!=null && count($zooItem->images)>0){


                if(xapp_property_exists($zooItem,XC_TEXT_ALL)){

                }
                /*
                if(xapp_property_exists($zooItem,XC_RATING)){

                        //$zooItem->{XC_RATING} = ;
                        $zooItem->{XC_RATING} =  ;
                        $zooItem->{XC_RATINGS}=;

                }
                */
                //$newIntroText = '<img src="' . $product_full . '">' . $jsonRes['product']['introText'];
                //$zooItem->{XC_ICON_URL}=$zooItem->images[0];
            }
        }else{
            $this->log('zoo item has no params method');
        }
        return $zooItem;
    }





    /***
     *
     * Simple test - function
     * @jsonpCall http://0.0.0.0/zoo254/components/com_xas/xapp/index.php?service=XCZoo.test&id=4&callback=as
     * @param int $id
     * @return mixed
     */
    public function test($id=5){

        $this->onZooCall(func_get_args());

        $zooModel = $this->getModel();

        $tags=$zooModel->getTags($id);
        $items=$zooModel->getTagItems($id,"Hardware");
        $items=$zooModel->_getCategories($id);

        /*$zoo = App::getInstance('zoo');

        foreach ($zoo->table->application->all(array('order' => 'name')) as $instance) {
            echo "\n\r".$instance->name;
            try {
                $cats=$instance->getCategories();
                foreach($cats as $cat)
                       echo "\n\r    ".$cat->name;
            } catch (AppException $e) {
                $zoo->error->raiseError(500, $e);
            }
        }



        $bla=array();
        $bla["sad"]=2;*/
        return $items;

    }

    /***
     *
     * More advanced test - function,
     * @jsonpCall http://0.0.0.0/zoo254/components/com_xas/xapp/index.php?service=XCZoo.testMethod&method=getApplcations&id=4&callback=as
     * @param int $id
     * @return mixed
     */
    public function testMethod($method='test', $id=5){

        //makes sure, we've all we need
        $this->onZooCall(func_get_args());


        $callee = $this;
        $items = null;
        if(method_exists($callee,$method)){

            //pop the method from the arguments
            $args = func_get_args();
            $args = array_pop($args);//strange, its popping the first and not the last

            //punch it
            $methodResult =  $callee->$method($args);
            if(!$methodResult){
                return '{methodResult:null}';
            }else{
                $items = $methodResult;
            }
        }else{
            error_log(self::CACHE_NS . 'no such method :' . $method . '');
        }
        return $items;
    }





    /***
     * This is creating all necessary instances to work with the Zoo-Model Objects
     * @param null $args
     */
    public function onZooCall($args=null){

        //create zoo model helper instance
        if($this->zooModel===null){

            //create an instance
            $this->zooModel= new _ZooModel();

            //wire the logger from here to the model helper
            $this->zooModel->logger = $this->logger;
        }

        //wire the Zoo main application
        if($this->zooApp===null){
            $this->zooApp = App::getInstance('zoo');
        }

        //wire the app from here to the model helper
        $this->zooModel->zooApp = $this->zooApp;
        /*
        //zoo deps really loaded ?
        if (!class_exists('App')) {
            $this->_loadZooDeps($this->getZooAdminRoot());
        }
        */
    }

    /**************************************************************************************************************/
    /*  RPC-Plugin concrete implementation section                                                                */
    /*                                                                                                            */


    /***
     * Turn off the lights
     * @param $result
     */
    public function onAfterCall($result){
        parent::onAfterCall($result);
    }


    /***
     * On before call will do custom type related work, usually, the mobile application sends a bunch of JSON formatted
     * parameters. It also does set up the custom type it self.
     * @param null $options
     */
    public function onBeforeCall($options=null){

        parent::onBeforeCall($options);

    }

    /***
     * Loads the plugin's dependencies, and nothing more !
     * @return integer
     */
    function load()
    {
        parent::load();

        //xapp_print_memory_stats('xcZoo::load:start');
        $zooBootstrap = $this->getZooAdminRoot() . 'config.php';

        //check the file really exists, otherwise the plugin will be disabled !
        if(file_exists($zooBootstrap)){

            //$this->_loadZooDeps($this->getZooAdminRoot());//doesn't work yet
            require_once($zooBootstrap);

            //include our Zoo-Model-Wrapper
            include(dirname(__FILE__) . DS . '_ZooModel.php');

            //include our Zoo-Model-Wrapper
            include(XAPP_BASEDIR . DS . "connect" . DS .'filter' . DS . 'SchemaFactory.php');

        }else{
            return false;
        }
        //xapp_print_memory_stats('xcZoo::load:end');
        return true;
    }

    /**
     * Prefix logging entries with Zoo
     * @param $message
     * @param string $ns
     * @param bool $stdError
     */
    public function log($message, $ns = "", $stdError = true)
    {
        parent::log($message, "Zoo", $stdError);
    }

    /**************************************************************************************************************/
    /*  Utils Section                                                                                             */
    /*                                                                                                            */

    /**
     * Translate a string into the current language : not working
     *
     * @param string $string The string to translate
     * @param boolean $js_safe If the string should be made javascript safe
     *
     * @return string The translated string
     */
    public function translate($string, $js_safe = false) {
        return $string;
        //$this->zooApp->system->language->load('com_zoo', $app->getPath(), null, true);
        //return $this->zooApp->system->language->_($string, $js_safe);
    }

    /***
     * @TODO
     * Boostrap Zoo, doesn't work yet !
     */
    private function _loadZooDeps($adminPath){
        if (!class_exists('App')) {


            /***
             *  This is the part from framework/config.php
             */

            $path = $adminPath . 'framework';
            // load imports
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.path');
            jimport('joomla.user.helper');
            jimport('joomla.mail.helper');

            // load classes
            JLoader::register('App', $path.'/classes/app.php');
            JLoader::register('AppController', $path.'/classes/controller.php');
            JLoader::register('AppHelper', $path.'/classes/helper.php');
            JLoader::register('AppView', $path.'/classes/view.php');
            JLoader::register('ComponentHelper', $path.'/helpers/component.php');
            JLoader::register('PathHelper', $path.'/helpers/path.php');
            JLoader::register('UserAppHelper', $path.'/helpers/user.php');

            /***
             * this is the main bootstrap :
             */

            // set defines
            define('ZOO_COPYRIGHT', '<div class="copyright"><a target="_blank" href="http://zoo.yootheme.com">ZOO</a> is developed by <a target="_blank" href="http://www.yootheme.com">YOOtheme</a>. All Rights Reserved.</div>');
            define('ZOO_TABLE_APPLICATION', '#__zoo_application');
            define('ZOO_TABLE_CATEGORY', '#__zoo_category');
            define('ZOO_TABLE_CATEGORY_ITEM', '#__zoo_category_item');
            define('ZOO_TABLE_COMMENT', '#__zoo_comment');
            define('ZOO_TABLE_ITEM', '#__zoo_item');
            define('ZOO_TABLE_RATING', '#__zoo_rating');
            define('ZOO_TABLE_SEARCH', '#__zoo_search_index');
            define('ZOO_TABLE_SUBMISSION', '#__zoo_submission');
            define('ZOO_TABLE_TAG', '#__zoo_tag');
            define('ZOO_TABLE_VERSION', '#__zoo_version');

// init vars
            $zoo = App::getInstance('zoo');
            if($zoo){
                //track copy here
                $this->zooApp=$zoo;
            }
//            $path = dirname(__FILE__);
            $cache_path = JPATH_ROOT.'/cache/com_zoo';
            $media_path = JPATH_ROOT.'/media/zoo';

// register paths
            $zoo->path->register(JPATH_ROOT.'/modules', 'modules');
            $zoo->path->register(JPATH_ROOT.'/plugins', 'plugins');
            $zoo->path->register($zoo->system->config->get('tmp_path'), 'tmp');
            $zoo->path->register($path.'/assets', 'assets');
            $zoo->path->register($cache_path, 'cache');
            $zoo->path->register($path.'/classes', 'classes');
            $zoo->path->register($path, 'component.admin');
            $zoo->path->register(JPATH_ROOT.'/components/com_zoo', 'component.site');
            $zoo->path->register($path.'/controllers', 'controllers');
            $zoo->path->register($path.'/events', 'events');
            $zoo->path->register($path.'/helpers', 'helpers');
            $zoo->path->register($path.'/installation', 'installation');
            $zoo->path->register($path.'/joomla', 'joomla');
            $zoo->path->register($path.'/joomla/elements', 'joomla.elements');
            $zoo->path->register($path.'/libraries', 'libraries');
            $zoo->path->register($media_path.'/applications', 'applications');
            $zoo->path->register($media_path.'/assets', 'assets');
            $zoo->path->register($media_path.'/elements', 'elements');
            $zoo->path->register($media_path.'/libraries', 'libraries');
            $zoo->path->register($path.'/partials', 'partials');
            $zoo->path->register($path.'/tables', 'tables');
            $zoo->path->register($path.'/installation/updates', 'updates');
            $zoo->path->register($path.'/views', 'views');

// create cache folder if none existent
            if (!JFolder::exists($cache_path) && $zoo->request->get('option', 'cmd') != 'com_cache') {
                JFolder::create($cache_path);
                $zoo->zoo->putIndexFile($cache_path);
            }

// register classes
            $zoo->loader->register('Application', 'classes:application.php');
            $zoo->loader->register('Category', 'classes:category.php');
            $zoo->loader->register('Comment', 'classes:comment.php');
            $zoo->loader->register('CommentAuthor', 'classes:commentauthor.php');
            $zoo->loader->register('CommentAuthorJoomla', 'classes:commentauthor.php');
            $zoo->loader->register('CommentAuthorFacebook', 'classes:commentauthor.php');
            $zoo->loader->register('CommentAuthorTwitter', 'classes:commentauthor.php');
            $zoo->loader->register('Item', 'classes:item.php');
            $zoo->loader->register('ItemForm', 'classes:itemform.php');
            $zoo->loader->register('ItemRenderer', 'classes:itemrenderer.php');
            $zoo->loader->register('Submission', 'classes:submission.php');

// register and connect events
            $zoo->event->register('ApplicationEvent');
            $zoo->event->dispatcher->connect('application:init', array('ApplicationEvent', 'init'));

            $zoo->event->register('ItemEvent');
            $zoo->event->dispatcher->connect('item:saved', array('ItemEvent', 'saved'));
            $zoo->event->dispatcher->connect('item:deleted', array('ItemEvent', 'deleted'));
            $zoo->event->dispatcher->connect('item:stateChanged', array('ItemEvent', 'stateChanged'));

            $zoo->event->register('CategoryEvent');
            $zoo->event->dispatcher->connect('category:saved', array('CategoryEvent', 'saved'));
            $zoo->event->dispatcher->connect('category:deleted', array('CategoryEvent', 'deleted'));
            $zoo->event->dispatcher->connect('category:stateChanged', array('CategoryEvent', 'stateChanged'));

            $zoo->event->register('CommentEvent');
            $zoo->event->dispatcher->connect('comment:saved', array('CommentEvent', 'saved'));
            $zoo->event->dispatcher->connect('comment:deleted', array('CommentEvent', 'deleted'));
            $zoo->event->dispatcher->connect('comment:stateChanged', array('CommentEvent', 'stateChanged'));

            $zoo->event->register('SubmissionEvent');
            $zoo->event->dispatcher->connect('submission:saved', array('SubmissionEvent', 'saved'));

            $zoo->event->register('LayoutEvent');
            $zoo->event->dispatcher->connect('layout:init', array('LayoutEvent', 'init'));

            $zoo->event->register('TypeEvent');
            $zoo->event->dispatcher->connect('type:beforesave', array('TypeEvent', 'beforesave'));
            $zoo->event->dispatcher->connect('type:copied', array('TypeEvent', 'copied'));
            $zoo->event->dispatcher->connect('type:deleted', array('TypeEvent', 'deleted'));

            $zoo->event->register('ElementEvent');
            $zoo->event->dispatcher->connect('element:configform', array('ElementEvent', 'configForm'));

        }
    }

    /***
     * Returns Zoo's admin folder
     */
    private function getZooAdminRoot(){
        return JPATH_ADMINISTRATOR . DS . self::$ZOO_FOLDER_PREFIX;
    }






    /***
     * Important function to distinguish between a real xapp app call and a JSONP-Test - Call for developing
     * @param $params
     * @return bool
     */
    protected  function isRPC($params){

        if(!is_string($params)){
            return $params;
        }

        $_decoded =  json_decode($params);
        if($_decoded!=null){
            if(is_array($_decoded)){
                if(xapp_array_isset($_decoded,XC_REF_ID)){
                    return true;
                }
            }elseif(is_object($_decoded)){
                if(property_exists($_decoded,XC_REF_ID)){
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * @param array
     * @param map
     * @return array array with remapped keys map(old_key_val=>new_key_val)
     */
    protected function array_remap_keys($input, $map) {
        $remaped_array = array();
        foreach($input as $key => $val) {
            if(array_key_exists($key, $map)) {
                $remaped_array[$map[$key]] = $val;
            }
        }
        return $remaped_array;
    }
    /***
     * Wrapper for array_remap_keys but works with an array of items
     * @param $input
     * @param $map
     * @return mixed
     */
    protected function array_items_remap_keys($input, $map) {
        $remaped_array = array();
        foreach($input as $item) {
            $item=$this->array_remap_keys($item,$map);
            array_push($remaped_array,$item);
        }

        return $input;
    }
}
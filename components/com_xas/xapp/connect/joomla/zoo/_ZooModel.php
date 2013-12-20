<?php
/**
 * RPC-Based interface for Joomla Core types
 *
 * @package XApp-Connect\Joomla
 * @class XCJoomla
 * @error @TODO
 * @author  mc007
 * @author  Luis Ramos
 *
 */
class _ZooModel extends Xapp_Joomla_Plugin
{

    /***
     * Cached Zoo-System-App. This set by XCZoo !
     * @var
     */
    public $zooApp;


    /**************************************************************************************************************/
    /*  Wrappers about the Zoo 'Application' class                                                                */
    /*                                                                                                            */

    /***
     * Get all Zoo applications
     * @param $zooAppId
     * @return null
     */
    public function getApplications() {

        $result = (array)$this->zooApp->table->application->all(array('order' => 'name'));

        return $result;

    }

    /***
     * Get Zoo application by name
     * @param $zooAppId
     * @return null
     */
    public function getApplicationByName($title) {

        $apps = $this->getApplications();
        if($apps!==null){
            foreach($apps as  $app){
                if($app->name ===$title){
                    return $app;
                }
            }
        }
        return null;
    }
    /***
    * Get Zoo application by name
    * @param $zooAppId
    * @return null
    */
    public function getApplicationByCategory($catId) {

        $apps = $this->getApplications();
        if($apps!==null){
            foreach($apps as  $app){

                $cats=$app->getCategories();
                if($cats){
                    foreach($cats as  $cat){
                        if($cat->id==$catId){
                            return $app;
                        }
                    }
                }

            }
        }
        return null;
    }

    /***
     * Get a specific Zoo application
     * @param $zooAppId
     * @return null
     */
    public function getApplication($zooAppId){

        $apps=$this->zooApp->table->application->all(array('order' => 'name'));

        if(xapp_array_isset($apps,$zooAppId)){
            return $apps[$zooAppId];
        }
        return null;
    }

    /**************************************************************************************************************/
    /*  Wrappers about the Zoo 'Category' class                                                                */
    /*                                                                                                            */


    public function test($id=5){

        $tags=$this->getTags($id);
        $items=$this->getTagItems($id,"Hardware");
        $items=$this->_getCategories($id);
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
     * Retrieve all the categories from a Zoo App
     * @param $zooAppId
     * @param $refId
     * @return array
     */
    public function _getCategories($zooAppId) {
        $app=$this->getApplication($zooAppId);
        $cats=$app->getCategories();
        return $cats;
    }

    /***
     * Retrieve all sub categories
     * @param int $refId
     * @return array
     */
    public function getCategoriesByParent($refId=0) {

        $result = array();
        $categories = $this->zooApp->table->category->getAll(array('order' => 'name'));

        foreach($categories as $cat){
            if($cat->parent === ''.$refId){
                array_push($result,$cat);
            }
        }
        return $result;

    }

    /**
     * Get Zoo category by id
     * @TODO : bad impl.
     * @param int $refId
     * @return null
     */
    public function getCategoryById($refId=0) {

        $categories = $this->zooApp->table->category->getAll(array('order' => 'name'));

        foreach($categories as $cat){
            if($cat->id === ''.$refId){
                return $cat;
            }
        }
        return null;

    }
    /***
     * Get Zoo Category by Zoo app instance and category id
     * @TODO : bad impl.
     * @param $zooApp
     * @param $refId
     * @return null
     */
    public function getCategoryByApplication($zooApp,$refId) {

        $categories = $zooApp->getCategories(true);

        foreach($categories as $cat){
            if($cat->id === ''.$refId){
                return $cat;
            }
        }
        return null;

    }

    /**************************************************************************************************************/
    /*  Wrappers about the Zoo 'Item' class                                                                        */
    /*                                                                                                             */

    /**@todo
     * @param $refId
     */
    public function getZooItemById($refId){

        $item = $this->zooApp->table->item->get($refId);
        return $item;
    }

    /**@todo
     * @param $refId
     */
    public function getZooItemComments($refId){}
    /**@todo
     * @param $refId
     */
    public function getZooItemRating($refId){}


    public function completeZooItemElements($zooItem,$zooParams,$debug=false) {
        if(!$zooItem){
            error_log('no item');
            return;
        }
        if(!$zooParams){
            error_log('no zoo params');
        }

        $zooElements =$result = array_merge($zooItem->getCoreElements(),$zooItem->getElements());
        if(!$zooElements){
            error_log('no zoo elements');
            return;
        }
        $idx=0;
        foreach($zooElements as $key => $val) {
            /*xapp_console('xapp console message','label','dump', (array)$val);*/
            $this->_prepareZooElement($zooItem,$val,$zooParams,$debug);
        }
        $zooItem->newElements = $zooElements;
        error_log($zooItem->{XC_OWNER_REF_STR});
        return $zooItem;
    }


    private function _prepareZooElement($zooItem,$zooElement,$zooParams,$debug=false){

        //$config = $zooElement->config
        $type = $zooElement->getElementType();
        $name = $zooElement->config['name'];
        //error_log(' element ' . $name . ' type : ' . $type);

        if(!xapp_property_exists($zooItem,XC_TEXT_ALL)){
            $zooItem->{XC_TEXT_ALL}=array();
        }


        switch($type){
            case 'text':{
                $zooElement->value=$zooElement->render();
                if($name!==null && strlen($name)){
                    switch($name){
                        case "Subtitle":{
                            $zooItem->{XC_INTRO}=$zooElement->value;
                            $zooItem->{XC_DESCRIPTION}=$zooElement->value;
                            break;
                        }
                    }
                }
                break;
            }
            case 'rating':{
                $zooElement->value=$zooElement->render();
                if($name!==null && strlen($name)){
                    switch($name){
                        case "Rating":{
                            $zooItem->{XC_RATING} = $zooElement->getRating();
                            $zooItem->{XC_RATINGS} = $zooElement->get('votes');
                            //error_log(json_encode($zooElement));
                            break;
                        }
                    }
                }
                break;
            }
            case 'textarea':{
                $zooElement->value=$zooElement->render();
                if($name!==null && strlen($name)){
                    array_push($zooItem->{XC_TEXT_ALL},$zooElement->value);
                }
                break;
            }
            case 'date':{
                $zooElement->value=$zooElement->render();
                if($name!==null && strlen($name)){
                    switch($name){
                        case "Date":{
                            $zooItem->{XC_DATE_STRING}=$zooElement->value;
                            break;
                        }
                    }
                }
                break;
            }
            case 'relateditems':{
                $zooElement->value=$zooElement->render();
                $name = $zooElement->config['name'];
                if($name!==null && strlen($name)){
                    switch($name){
                        case "Author":{
                            $zooItem->{XC_OWNER_REF_STR}=$zooElement->value;
                            break;
                        }
                    }
                }
                //error_log('a: ' . $zooElement->value. ' for ' .$zooElement->getControlName());
                break;
            }
            case 'itemcommentslink':
                $zooElement->value=$this->completeUrl($zooElement->render());
                break;
            case 'image':{

                $zooElement->value=$this->completeUrl($zooElement->get('file'));

                if($zooItem->images==null){
                    $zooItem->images=array();
                }
                array_push($zooItem->images,$zooElement->value);

                break;

            }

            default :
                {
                    $zooElement->value=$zooElement->render();
                }
        }
        return $zooElement;
    }

    /**
     * @param int $refId
     * @return null
     */
    public function getZooItems($refId=0) {
        $zooApp = $this->getApplicationByCategory($refId);

        if($zooApp==null){
            error_log('no such app');
            return null;
        }else{
            /*$this->zooApp = $zooApp->app;*/
        }

        $zooCategory = $this->getCategoryByApplication($zooApp,$refId);

        if($zooCategory==null){
            error_log('no such category');
            return null;
        }
        return $zooCategory->getItems(true);

    }

    /***
     * Retrieve all the tags from a Zoo App
     * @param $appId
     * @return array
     */
    public function getTags($appId=false) {
        return $this->zooApp->table->tag->getAll($appId);
    }

    /***
     * Retrieve all the items assigned to a tag
     * @param $zooAppId
     * @param $tag
     * @return array
     */
    public function getTagItems($zooAppId,$tag) {
        return $this->zooApp->table->item->getByTag($zooAppId,$tag);

    }


    /**
     * @param $message
     * @param string $ns
     * @param bool $stdError
     */
    public function log($message, $ns = "", $stdError = true)
    {
        parent::log($message, "_ZooModel", $stdError);
    }
    /**
     * @param array
     * @param map
     * @return array array with remaped keys map(old_key_val=>new_key_val)
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
<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * RPC-Based interface for Joomla Core types
 *
 * @package XApp-Connect\Joomla
 * @class XCJoomla
 * @error @TODO
 * @author  mc007
 */
class XCJoomla extends Xapp_Joomla_Plugin
{


    /**
     * option to specify a cache config
     *
     * @const DEFAULT_NS
     */
    var $CACHE_NS = 'JOOMLA_CACHE_NS';


    private function _replaceUserVariables($str,$vars){
        $replaceVars = true;

        $result =  $str;
        if($replaceVars){
            $userVars= (array)$vars;
            if($userVars){
                $_keys = array();
                $_values = array();
                foreach ($userVars as $key => $value)
                {
                    array_push($_keys,'%'.$key.'%');
                    array_push($_values,$value);
                }
                $result = str_replace(
                    $_keys,
                    $_values,
                    $result
                );
            }

        }

        return $result;
    }

    private function getLoginFormData(){
        $this->getUserId();
        $file = XAPP_FORM_DATA_PATH . 'login.json';
        $formData  = XApp_Utils_JSONUtils::read_json($file,'json',false,false);
        return $formData;

    }
    private function getLoginFormDataFailed(){
        $this->getUserId();
        $file = XAPP_FORM_DATA_PATH . 'loginFailed.json';
        $formData  = XApp_Utils_JSONUtils::read_json($file,'json',false,false);
        return $formData;

    }

    private function getLoggedInFormData(){
        $this->getUserId();
        $file = XAPP_FORM_DATA_PATH . 'loggedIn.json';
        $formData  = XApp_Utils_JSONUtils::read_json($file,'json',false,false);
        if($formData==null){
            error_log('have no form datate : ' . $file);
        }else{
            error_log('have logged in form data' . $formData. ' at ' . $file);
        }
        return $formData;

    }

    /***
     * http://192.168.1.37/zoo254//index.php?option=com_xas&view=rpc&service=XCJoomla.processLoginData&callback=login&params={"DSUID":"8afeed00-181d-4df7-b8b0-a62b70652d65","BASEREF":"http://192.168.1.37/zoo254/","REFID":"joomlaLogin","APPID":"mygeneralapp8f","RT_CONFIG":"debug","UUID":"11166763-e89c-44ba-aba7-4e9f4fdf97a9","SERVICE_HOST":"http://www.mc007ibi.dyndns.org:8080/XApp-portlet/","IMAGE_RESIZE_URL":"http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=","SOURCE_TYPE":"joomlaLogin","SCREEN_WIDTH":320,"username":"admin","password":"asdasd"}
     * @param string $params
     * @return mixed|string
     */
    public function processLoginData($params='{}'){


        $mainframe =JFactory::getApplication('site');
        $mainframe->initialise(); $user =JFactory::getUser();
        $username=$user->get('username'); // username text same as database table
        //error_log('current user : ' . $username);
        //error_log('processLoginData :  ' . $params);
        $session = JFactory::getSession();
        //error_log('xcjoomla::session id ' . $session->getId());



        //error_log('xcjoomla' . json_encode($session));
        //error_log('xcjoomla::session name : ' . $session->getName());
        //error_log('xcjoomla::session id ' . $session->getId());
        //error_log('xcjoomla::session is new ' . $session->isNew());
        //error_log('xcjoomla::session token is ' . $session->getToken(false));




        //error_log('json error login params' . $params);

        $params = is_string($params) ? json_decode($params) : $params;
        if(!$params){
            //error_log('json error login params' . $this->getLastJSONError());

            return;
        }

        $jAuth = new XAppJoomlaAuth();

        //$isLoggedIn = $this->getUserId();
        //error_log('xcjoomla::is logged in : ' . $isLoggedIn);
        //error_log('xcjoomla::loggin into joomla with : ' . $params->username . ' and : ' . $params->password);

        $authRes = $jAuth->loginUser($params->username,$params->password);
        if($authRes==-1){
            //error_log('xcjoomla::login result into joomla with : ' .$authRes);
            return $this->doLoginFormFailed();
        }




        //$user = JFactory::getUser();//c1182f33cc67e456f068e1333b8342b4:1yCoaJMK9gLmdDgaJvtocUeWXYpO8eLz
        /*
        error_log($user->password);//c1182f33cc67e456f068e1333b8342b4:1yCoaJMK9gLmdDgaJvtocUeWXYpO8eLz
        $hashparts = explode(':',$user->password);

        //echo $hashparts[0]; //this is the hash  4e9e4bcc5752d6f939aedb42408fd3aa
        //echo $hashparts[1];
        $userhash = md5($user->password.$hashparts[1]);
        error_log('user hash ' . $userhash);
*/

        /*

        $creds = array();
        $creds['session']=$session->getId();
        $creds['username']=$params->username;


        //$salt  = JUserHelper::genRandomPassword(32);
        //$crypt = JUserHelper::getCryptedPassword($password_set.$time, $salt);
       // $data['password'] = $crypt.':'.$salt;


        error_log('xcjoomla::after login session id ' . $session->getId());

        $response = new stdClass();
        $jAuth->initSSOSession($session->getId());
        $jAuth->onAuthenticate($creds,null,$response);
*/


        //error_log('xcjoomla::auth result : ' . $authRes);
        //error_log('xcjoomla::session token aufter auth is ' . $session->getToken(false));

        return $this->doLogoutForm();

    }
    /***
     * @param string $params
     * @link http://192.168.1.37/zoo254//index.php?option=com_xas&view=rpc&service=XCJoomla.processLoginData&callback=login&params={%22DSUID%22:%228afeed00-181d-4df7-b8b0-a62b70652d65%22,%22BASEREF%22:%22http://192.168.1.37/zoo254/%22,%22REFID%22:%22joomlaLogin%22,%22APPID%22:%22mygeneralapp8f%22,%22RT_CONFIG%22:%22debug%22,%22UUID%22:%2211166763-e89c-44ba-aba7-4e9f4fdf97a9%22,%22SERVICE_HOST%22:%22http://www.mc007ibi.dyndns.org:8080/XApp-portlet/%22,%22IMAGE_RESIZE_URL%22:%22http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=%22,%22SOURCE_TYPE%22:%22joomlaLogin%22,%22SCREEN_WIDTH%22:320,%22username%22:%22asdfasdf%22,%22gender%22:%22on%22}&uuid=11166763-e89c-44ba-aba7-4e9f4fdf97a9&appIdentifier=mygeneralapp8f&callback=dojo.io.script.jsonp_dojoIoScript1._jsonpCallback
     * @return mixed
     */
    public function logout($params='{}'){
        $isRPCCall = $this->isRPC($params);
        if(!$isRPCCall){
            //http://192.168.1.37/zoo254//index.php?option=com_xas&view=rpc&service=XCZoo.customTypeQuery&params={%22DSUID%22:%22bd6b4233-8b40-4c8d-a0f8-3fa71ded544d%22,%22BASEREF%22:%22http://192.168.1.37/zoo254/%22,%22REFID%22:%22zoo%22,%22CTYPE%22:%22ZooApplication%22,%22APPID%22:%22myeventsapp1d0%22,%22RT_CONFIG%22:%22debug%22,%22UUID%22:%2211166763-e89c-44ba-aba7-4e9f4fdf97a9%22,%22SERVICE_HOST%22:%22http://mc007ibi.dyndns.org:8080/XApp-portlet/%22,%22IMAGE_RESIZE_URL%22:%22http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=%22,%22SOURCE_TYPE%22:%22zooApplication%22,%22SCREEN_WIDTH%22:320}&callback=dojo.io.script.jsonp_dojoIoScript3._jsonpCallback
            $params = $this->_createRPCDefaultParameters('zoo','zooApplication','ZooApplication');
        }
        $this->onBeforeCall($params);

        //error_log('do logout ' . $this->getUserId());
        $jAuth =  new XAppJoomlaAuth();
        $jAuth->logout($this->getUserId());
        $loginForm = $this->doLoginForm();
        //xapp_dump(json_decode($loginForm));
        return $loginForm;

    }

    private function doLoginForm(){
        $formData = $this->getLoginFormData();

        $this->xcOptions->vars->ACTION_URL=$this->getGatewayUrl();
        $this->xcOptions->vars->ACTION_URL.='&service=XCJoomla.processLoginData';
        $formData = $this->_replaceUserVariables($formData,$this->xcOptions->vars);
        return $formData;
    }

    private function doLoginFormFailed(){
        $formData = $this->getLoginFormDataFailed();

        $this->xcOptions->vars->ACTION_URL=$this->getGatewayUrl();
        $this->xcOptions->vars->ACTION_URL.='&service=XCJoomla.processLoginData';
        $this->xcOptions->vars->HEADER_TEXT='Login failed!';
        $formData = $this->_replaceUserVariables($formData,$this->xcOptions->vars);
        //error_log('sending back : ' . $formData);
        return $formData;
    }
    private function doLogoutForm(){
        $formData = $this->getLoggedInFormData();
        $user = $this->getCurrentUser();
        //error_log('do logout form ! : ' . $formData);

        //error_log('do logout form ::0');
        $this->xcOptions->vars->TITLE =JText::_( 'Welcome' ) . ' ' .  $user->name;
        $this->xcOptions->vars->INTRO_TEXT = JText::_( 'Welcome' ) . ' ' .  $user->name . '.<br/>You are logged in!';

        $this->xcOptions->vars->ACTION_URL=$this->getGatewayUrl();
        //error_log('do logout form ::1');
        $this->xcOptions->vars->ACTION_URL.='&service=XCJoomla.logout';
        //error_log('do logout form ::2');
        $formData = $this->_replaceUserVariables($formData,$this->xcOptions->vars);
        //error_log('do logout form ::3');
        return $formData;
    }
    public function login($params='{}'){

        //xapp_show_errors();

        $isRPCCall = $this->isRPC($params);
        if(!$isRPCCall){
            //http://192.168.1.37/zoo254//index.php?option=com_xas&view=rpc&service=XCZoo.customTypeQuery&params={%22DSUID%22:%22bd6b4233-8b40-4c8d-a0f8-3fa71ded544d%22,%22BASEREF%22:%22http://192.168.1.37/zoo254/%22,%22REFID%22:%22zoo%22,%22CTYPE%22:%22ZooApplication%22,%22APPID%22:%22myeventsapp1d0%22,%22RT_CONFIG%22:%22debug%22,%22UUID%22:%2211166763-e89c-44ba-aba7-4e9f4fdf97a9%22,%22SERVICE_HOST%22:%22http://mc007ibi.dyndns.org:8080/XApp-portlet/%22,%22IMAGE_RESIZE_URL%22:%22http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=%22,%22SOURCE_TYPE%22:%22zooApplication%22,%22SCREEN_WIDTH%22:320}&callback=dojo.io.script.jsonp_dojoIoScript3._jsonpCallback
            $params = $this->_createRPCDefaultParameters('zoo','zooApplication','ZooApplication');
            //error_log(' login:not a rpc call!');
        }
        //error_log('login with paramsStr  ' . $params);
        $this->onBeforeCall($params);
        //error_log('login with params  ' . json_encode($params));
        //xapp_dumpObject($params,'login::params');

        $isLoggedIn = $this->isLoggedIn();
        //in case the user is not logged in, we send the JSON - Data containing all form elements
        if($isLoggedIn==false){
            return $this->doLoginForm();
        }else{
            return $this->doLogoutForm();
        }
    }

    /*
    public function customTypeQuery($params='{}')
    {
        $res=parent::customTypeQuery($params);
        $res['joomla']=true;

        return $res;


    }
    */
    public function _getArticlesByCategoryId($refId="root",$published=1){
        $this->_loadArticleDeps();

        /*
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        */

        $model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
        if($model){
        }else{
        }

        $app = JFactory::getApplication();

        $model->setState('params', JFactory::getApplication()->getParams());
        $model->setState('filter.published', $published);
        // Access filter
        $access = !JComponentHelper::getParams('com_content')->get('show_noauth');
        $authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
        $model->setState('filter.access', $access);
        // Category filter
        $model->setState('filter.category_id',array($refId));
        // Filter by language
        $model->setState('filter.language', $app->getLanguageFilter());

        $items = $model->getItems();

        foreach ($items as &$item) {
            $item->slug = $item->id.':'.$item->alias;
            $item->catslug = $item->catid.':'.$item->category_alias;
            $item->author = $item->author . ' ::  ' .  $item->catid.':'.$item->category_alias . " ref : " . $this->xcRefId;

            if ($access || in_array($item->access, $authorised)) {
                // We know that user has the privilege to view the article
                $item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
            } else {
                $item->link = JRoute::_('index.php?option=com_users&view=login');
            }
        }
        return $items;
    }
    public function getArticles($params = "{}"){
        $this->onBeforeCall($params);
        $articles = $this->_getArticlesByCategoryId($this->xcRefId);
        $res= $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $articles);
        return $res;
    }

    public function _getCategories($refId=null){

        $options = array();
        $options['published'] = 1;

        if($refId==null){
            $refId="root";
        }

        $categories = JCategories::getInstance('Content',$options);
        $subCategories = $categories->get($refId);

        $items =$subCategories->getChildren();
        $res = array();
        $idx = 0;
        foreach($items as $item) {
            $res[$idx]['title']=$item->title;
            $res[$idx]['refId']=$item->id;
            $res[$idx]['groupId']=$item->parent_id;
            $res[$idx]['introText']=$item->description;
            $idx++;
        }
        return $res;

    }
    public function getCategories($params = "{}")
    {
        jimport('joomla.application.categories');
        $this->onBeforeCall($params);
        $cats = $this->_getCategories($this->xcRefId);
        $res= $this->applyFilter(Xapp_Connect_Filter::FILTER_XC_SCHEMA, $cats);
        //on after call will trigger index
        //$this->onAfterCall($res);
        return $res;

    }

    private function _loadArticleDeps(){
        if(defined("_JEXEC")){

        }else{
            define("_JEXEC",true);
        }

        jimport('joomla.application.categories');
        $com_path = JPATH_SITE.'/components/com_content/';
        require_once $com_path.'router.php';
        require_once $com_path.'helpers/route.php';
        require_once JPATH_SITE.'/components/com_content/helpers/route.php';

        JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');


    }

    public function test(){

        $this->_loadArticleDeps();

        xapp_hide_errors();

        $debugStr = "";
        /*$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
        if($model){

            $debugStr.= "have model";
        }else{
            $debugStr.="have no model";
        }

        $app = JFactory::getApplication();
        $appParams = $app->getParams();

        $model->setState('params', JFactory::getApplication()->getParams());
        $model->setState('filter.published', 1);
        // Access filter
        $access = !JComponentHelper::getParams('com_content')->get('show_noauth');
        $authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
        $model->setState('filter.access', $access);
        // Category filter
        $model->setState('filter.category_id',8);
        // Filter by language
        $model->setState('filter.language', $app->getLanguageFilter());

        $items = $model->getItems();
        */

        $articles=$this->_getArticlesByCategoryId(8);

        $debugStr.=xapp_print_json($articles);

        return $debugStr;


        //return $this->_getCategories(8);

        /*
        //$categories = new JCategories('com_content');
        $categories = JCategories::getInstance('Content');
        $subCategories = $categories->get();

        $items = $subCategories->getChildren();

        $categoryJSON = json_encode($items);
        xapp_dumpObject($items);

        //return $categoryJSON;

        //return '{}';
        */
    }
    /**
     * init, concrete Joomla-Plugin class implementation
     *
     * @return void
     */
    private function init()
    {

    }

    /***
     * We override base search because we've got multiple types to search
     * @param $query
     * @return array|null
     */
    public function searchMultiple($query,$customTypes){

    }

    /***
     * @param $query
     * @param $customTypes
     */
    private function toIndexParameters($customTypeName){
        $cType= CustomTypeManager::instance()->getType(
            $customTypeName,
            null,
            null,
            null,//platform : defaults to IPHONE_NATIVE
            'debug',
            false);

        if($cType){
            return $this->getIndexOptions($cType);
        }else{
            $this->log('have no ct');
        }
        return null;
    }

    /***
     * We override base search because we've got multiple types to search
     * @param $query
     * @return array|null
     */
    public function search($query){

        $indexParameters = array(
            $this->toIndexParameters('VMartCategory'),
            $this->toIndexParameters('VMartProduct')
        );

        $searchResults = array();

        foreach($indexParameters as $iParams){
            $foundItems=true;
            if(!is_array($iParams)){
                continue;
            }

            $cTypeSearchResults = $this->searchEx($query,$iParams,'Shopping');
            if(count($cTypeSearchResults)){
                $searchResults= array_merge($searchResults,$cTypeSearchResults);
            }else{
                $searchResults=array_merge($searchResults,array());
            }
        }
        return $searchResults;
    }

    public function searchTest($query){
        $this->search($query);
    }

    /***
     * Turn off the lights
     * @param $result
     */
    public function onAfterCall($result){
        $this->productModel=null;
        $this->class_category=null;
        $this->version=null;
        $this->db=null;
        $this->class_media=null;
        $this->currency=null;
        $this->ratingModel=null;
        parent::onAfterCall($result);
    }

    /***
     * @param $refId
     * @param $sourceType
     * @return array
     */
    private function createDSParamsStruct($refId, $sourceType)
    {
        $res = array();
        $res['dsUid'] = 'DSUID';
        $res['dsRef'] = $refId;
        $res['dsType'] = $sourceType;

        return $res;
    }

    /***
     * @param $inUrl
     * @return mixed
     */
    private function getShareUrl($inUrl)
    {
        return $this->cleanUrl($this->rootUrl() . $inUrl);
    }

    /***
     * @param $dst
     * @param $inData
     * @return mixed
     */
    private function addMediaItems($dst, $inData)
    {
        $cnt = 0;
        foreach ($inData as $media) {
            $product_full = $this->siteUrl() . $media->file_url;
            $product_thumbs = $this->siteUrl() . $media->file_url_thumb;

            $check_full = JPATH_SITE . DS . $media->file_url;
            $check_thumb1 = JPATH_SITE . DS . $media->file_url_thumb;

            //$dst[$cnt]['title']=$media->title;

            if (file_exists($check_thumb1)) {
                $dst[$cnt]['product_thumb_image'] = $product_thumbs;
            } else {
                $dst[$cnt]['product_thumb_image'] = $this->default_image;
            }

            if (file_exists($check_full)) {
                $dst[$cnt]['fullSizeLocation'] = $product_full;
            } else {
                $dst[$cnt]['fullSizeLocation'] = $this->default_image;
            }
            $cnt++;
        }
        return $dst;
    }

    /***
     *
     * @return integer
     */
    function load()
    {
        parent::load();
        xapp_hide_errors();
        return true;
    }

    /**
     * @param $message
     * @param string $ns
     * @param bool $stdError
     */
    public function log($message, $ns = "", $stdError = true)
    {
        parent::log($message, "Joomla", $stdError);
    }

    /**
     * init, concrete Joomla-Plugin class implementation
     *
     * @return void
     */
    function setup()
    {
        parent::setup();
    }

}
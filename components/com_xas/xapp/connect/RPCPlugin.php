<?php
/**
 * @version 0.1.0
 * @package XApp-Connect
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

// *****************************************************************
// XAPP_CONNECT_VAR
// all about XApp-Connect variables. They're used for schema evaluation but also to control caching or storing
// application parameters like appId,etc...
// The following are constants and wont change at all
// *****************************************************************

/**
 * DSUID is a unique identifier to a datasource.
 * @const XAPP_CONNECT_VAR_DSUID
 */
define('XAPP_CONNECT_VAR_DSUID',                'DSUID');

/**
 * BASEREF is the destination end point and being used to complete urls within a schema.
 * @const XAPP_CONNECT_VAR_BASEREF
 */
define('XAPP_CONNECT_VAR_BASEREF',               'BASEREF');

/**
 * RT_CONFIG specifies the run-time configuration due to the entire RPC operation.
 * @const XAPP_CONNECT_VAR_RT_CONFIG
 */
define('XAPP_CONNECT_VAR_RT_CONFIG',            'RT_CONFIG');

/**
 * CTYPE specifies the unique name of a custom type.
 * @const XAPP_CONNECT_VAR_CTYPE
 */
define('XAPP_CONNECT_VAR_CTYPE',                'CTYPE');

/**
 * UUID specifies an unique user id for a XApp-Studio user
 * @const XAPP_CONNECT_VAR_UUID
 */
define('XAPP_CONNECT_VAR_UUID',                 'UUID');

/**
 * APPID specifies an unique application id from XApp-Studio
 * @const XAPP_CONNECT_VAR_UUID
 */
define('XAPP_CONNECT_VAR_APPID',                'APPID');

/**
 * SOURCE_TYPE specifies the unique identifier of custom type.
 * @const XAPP_CONNECT_VAR_SOURCE_TYPE
 */
define('XAPP_CONNECT_VAR_SOURCE_TYPE',          'SOURCE_TYPE');

/**
 * PREVENT_CACHE disables the reponse cache for the RPC operation.
 * @const XAPP_CONNECT_VAR_PREVENT_CACHE
 */
define('XAPP_CONNECT_VAR_PREVENT_CACHE',        'preventCache');

/**
 * SERVICE_HOST is the uplink url to a XApp-Studio instance
 * @const XAPP_CONNECT_VAR_PREVENT_CACHE
 */
define('XAPP_CONNECT_VAR_SERVICE_HOST',         'SERVICE_HOST');

/**
 * REFID is the current reference id
 */
define('XAPP_CONNECT_VAR_REFID',         'REFID');


/**
 * XApp-Connect-RPC plugin base class.
 *
 * The class
 *
 * @package XApp-Connect
 * @class Xapp_Connect_RPCPlugin
 * @error @TODO
 * @author  mc007
 */
class Xapp_Connect_RPCPlugin extends  Xapp_Connect_Plugin
{

    public $loaded=false;
    public $forceNewCType=null;
    private function init(){}

    /***
     * Holds the XApp-Connect-Request variables and they are passed through the client's RPC call.
     * <code>
     * (
     *   [tablePrefix] =>
     *   [database] =>
     *   [vars] => stdClass Object
     *   (
     *       [DSUID] => 44053850-26ff-42d2-85f1-77b110977a29
     *       [BASEREF] => http://192.168.1.37:8888/joomla257/
     *       [REFID] => 0
     *       [CTYPE] => VMartCategory
     *       [APPID] => myeventsapp1d0
     *       [RT_CONFIG] => debug
     *       [UUID] => 11166763-e89c-44ba-aba7-4e9f4fdf97a9
     *       [SERVICE_HOST] => http://www.mc007ibi.dyndns.org:8080/XApp-portlet/
     *       [IMAGE_RESIZE_URL] => http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=
     *       [SOURCE_TYPE] => vmCategory
     *       [SCREEN_WIDTH] => 300
     *       [preventCache] => 1
     *   )
     * )
     * </code>
     *
     * @var object
     */

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
     * @param $refId : a reference Id
     * @param $sourceType : the urlSchema of the custom type
     * @param $customTypeName : the name of the custom type
     * @return mixed
     */
    protected  function _createRPCDefaultParameters($refId,$sourceType,$customTypeName){

        /**
         * A valid JSON-RPC-2.0-POST call must look like this in HTTP-POST :
         * {"refId":"zoo",
         *  "schemas":{},
         *   "options":"{
         *     \\"tablePrefix\\":\\"\\",
         *     \\"database\\":null,
         *     \\"vars\\":{
         *          \\"DSUID\\":\\"bd6b4233-8b40-4c8d-a0f8-3fa71ded544d\\",
         *          \\"BASEREF\\":\\"http://192.168.1.37/zoo254/\\",
         *          \\"REFID\\":\\"zoo\\",
         *          \\"CTYPE\\":\\"ZooApplication\\",
         *          \\"APPID\\":\\"myeventsapp1d0\\",
         *          \\"RT_CONFIG\\":\\"debug\\",
         *          \\"UUID\\":\\"11166763-e89c-44ba-aba7-4e9f4fdf97a9\\",
         *          \\"SERVICE_HOST\\":\\"http://www.mc007ibi.dyndns.org:8080/XApp-portlet/\\",
         *          \\"IMAGE_RESIZE_URL\\":\\"http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=\\",
         *          \\"SOURCE_TYPE\\":\\"zooApplication\\",
         *          \\"SCREEN_WIDTH\\":\\"300\\",
         *          \\"preventCache\\":true}}"
         *      }
         *
         **/

        $ctypesServiceUrl = xapp_get_option(XC_CONF_SERVICE_HOST,$this->serviceConfig);
        $usLiveHost = true;
        if($usLiveHost){
            $ctypesServiceUrl = "http://www.xapp-studio.com/XApp-portlet/";
        }

        $result = '{"refId":"zoo","schemas":{},"options":"{\\"tablePrefix\\":\\"\\",\\"database\\":\\"\\",\\"vars\\":{\\"DSUID\\":\\"bd6b4233-8b40-4c8d-a0f8-3fa71ded544d\\",\\"BASEREF\\":\\"_BASE_REF_\\",\\"REFID\\":\\"_REF_ID_\\",\\"CTYPE\\":\\"_CTYPE_NAME_\\",\\"APPID\\":\\"myeventsapp1d0\\",\\"RT_CONFIG\\":\\"_RT_CONFIG_\\",\\"UUID\\":\\"11166763-e89c-44ba-aba7-4e9f4fdf97a9\\",\\"SERVICE_HOST\\":\\"_SERVICE_HOST_\\",\\"IMAGE_RESIZE_URL\\":\\"http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=\\",\\"SOURCE_TYPE\\":\\"_SOURCE_TYPE_\\",\\"SCREEN_WIDTH\\":320,\\"preventCache\\":true}}"}';
        $result = str_replace('_CTYPE_NAME_',$customTypeName,$result);
        $result = str_replace('_SERVICE_HOST_',$ctypesServiceUrl,$result);
        $result = str_replace('_SOURCE_TYPE_',$sourceType,$result);
        $result = str_replace('_REF_ID_',$refId,$result);
        $result = str_replace('_RT_CONFIG_','debug',$result);
        $result = str_replace('_BASE_REF_',$this->siteUrl(),$result);

        //{"refId":"zoo","schemas":{},"options":"{\\"tablePrefix\\":\\"\\",\\"database\\":\\"\\",\\"vars\\":{\\"DSUID\\":\\"bd6b4233-8b40-4c8d-a0f8-3fa71ded544d\\",\\"BASEREF\\":\\"http://192.168.1.37/zoo254/\\",\\"REFID\\":\\"zoo\\",\\"CTYPE\\":\\"ZooApplication\\",\\"APPID\\":\\"myeventsapp1d0\\",\\"RT_CONFIG\\":\\"debug\\",\\"UUID\\":\\"11166763-e89c-44ba-aba7-4e9f4fdf97a9\\",\\"SERVICE_HOST\\":\\"http://mc007ibi.dyndns.org:8080/XApp-portlet/\\",\\"IMAGE_RESIZE_URL\\":\\"http://192.168.1.37:8080/XApp-portlet/servlets/ImageScaleIcon?src=\\",\\"SOURCE_TYPE\\":\\"zooApplication\\",\\"SCREEN_WIDTH\\":320,\\"preventCache\\":true}}"}
        return $result;

    }

    public function runCustomType($params){

        $ctManager = CustomTypeManager::instance();
        //$ctManager->test();
        //construct options
        $options = new stdClass();
        $options->vars = new stdClass();
        $options->vars->{XAPP_CONNECT_VAR_DSUID}=$params[XAPP_CONNECT_VAR_DSUID];
        $options->vars->{XAPP_CONNECT_VAR_BASEREF}=$params[XAPP_CONNECT_VAR_BASEREF];
        $options->vars->{XAPP_CONNECT_VAR_RT_CONFIG}=$params[XAPP_CONNECT_VAR_RT_CONFIG];
        $options->vars->{XAPP_CONNECT_VAR_APPID}=$params[XAPP_CONNECT_VAR_APPID];
        $options->vars->{XAPP_CONNECT_VAR_UUID}=$params[XAPP_CONNECT_VAR_UUID];
        $options->vars->{XAPP_CONNECT_VAR_BASEREF}=$params[XAPP_CONNECT_VAR_BASEREF];
        $options->vars->{XAPP_CONNECT_VAR_CTYPE}=$params[XAPP_CONNECT_VAR_CTYPE];

        $options->vars->{XAPP_CONNECT_VAR_SERVICE_HOST}=$params[XAPP_CONNECT_VAR_SERVICE_HOST];
        if(xapp_array_get($params,XAPP_CONNECT_VAR_PREVENT_CACHE)!=null){
            $options->vars->{XAPP_CONNECT_VAR_PREVENT_CACHE}=$params[XAPP_CONNECT_VAR_PREVENT_CACHE];
        }
        $customTypeResult = $ctManager->runCTypeEx($params[XAPP_CONNECT_VAR_CTYPE],$params[XAPP_CONNECT_VAR_REFID],$options,true,null,$this,$params);

        return $customTypeResult;
    }

    /***
     * @param string $params
     * @return mixed
     */
    public function customTypeQuery($params='{}')
    {
        //xapp_show_errors();
        $paramsOut = (array)json_decode($params,false);
        if($this->getLastJSONError()!=null){
            $this->log('json error : in custom type jsonp query' . $this->getLastJSONError());
            $params = str_replace('\\','',$params);
            $paramsOut = (array)json_decode($params,false);
        }
        $res = $this->runCustomType($paramsOut);
        return $res;
    }
    /***
     *
     * @param $item
     * @return string
     */
    public  function toDSURL($item)
    {
        $res = 'tt://dsUrl/';
        if($item['dataSource']){
            $res.=$item['dataSource'].'/';
        }
        if($item['sourceType']){
            $res.=$item['sourceType'].'/';
        }
        if($item['refId']){
            $res.=$item['refId'];
        }
        return $res;
    }

    public function onBeforeCall($_options=null){
        xapp_hide_errors();
        $this->parseOptions($_options);
    }

    public function onAfterCall($result){

        if($this->xcType!=null){

            if($this->getIndexOptions($this->xcType)!=null){
                $this->indexDocument($result,$this->xcType,$this->CACHE_NS);
            }
        }


    }

    public function getXCOption($key){

        //xapp_dump($this->xcOptions);

        if(is_array($this->xcOptions)){
            if($key && $this->xcOptions && $this->xcOptions['vars']){
                return $this->xcOptions['vars'][$key];
            }
        }else if(is_object($this->xcOptions)){
            if($key && $this->xcOptions && $this->xcOptions->vars){
                if(property_exists($this->xcOptions->vars,$key)){
                    return $this->xcOptions->vars->$key;
                }
            }
        }
        return null;
    }


    /**
     *
     */
    private function setupCustomType(){

        $this->xcType = CustomTypeManager::instance()->getType(
            $this->getXCOption(XAPP_CONNECT_VAR_CTYPE),
            $this->getXCOption(XAPP_CONNECT_VAR_UUID),
            $this->getXCOption(XAPP_CONNECT_VAR_APPID),
            null,//platform : defaults to IPHONE_NATIVE
            $this->getXCOption(XAPP_CONNECT_VAR_RT_CONFIG),
            $this->getXCOption(XAPP_CONNECT_VAR_PREVENT_CACHE));
    }

    /***
     *
     * @param json-str $_options
     */

    private function parseOptions($_options){
        $params = is_string($_options) ? json_decode($_options) : $_options;
        if(!$params){
            error_log('json error ' . $this->getLastJSONError());
            return;
        }

        if($params!=null){

            //keep XApp-Connect-Options
            if($params->options){
                $this->xcOptions = is_string($params->options) ?  json_decode($params->options) : $params->options;
                if($this->xcOptions!==null){
                    $this->setupCustomType($this->xcOptions);
                }
            }

            if($params->schemas!=null){

                $this->xcSchemas = $params->schemas;
            }else{
                $ctypeSchemas = CustomTypesUtils::getCIStringValue($this->xcType,'schemas');
                $ctypeSchemas = preg_replace('/[\x00-\x1F\x7F]/', '', $ctypeSchemas);
                $ctypeSchemas = preg_replace('/\r\n?/', "", $ctypeSchemas);
                $ctypeSchemas = str_replace(array("\n", ""), "", $ctypeSchemas);
                $this->xcSchemas = json_decode($ctypeSchemas);
            }


            //replace schemas with ctype copy
            if(!xapp_conf(XC_CONF_ALLOW_REMOTE_SCHEMA) && $this->xcType!=null){
                $ctypeSchemas = CustomTypesUtils::getCIStringValue($this->xcType,'schemas');
                $ctypeSchemas = preg_replace('/[\x00-\x1F\x7F]/', '', $ctypeSchemas);
                $ctypeSchemas = preg_replace('/\r\n?/', "", $ctypeSchemas);
                $ctypeSchemas = str_replace(array("\n", ""), "", $ctypeSchemas);
                $this->xcSchemas = json_decode($ctypeSchemas);
            }

            //store ref id
            if($params->refId){
                $this->xcRefId =$params->refId;
            }

            //error_log('xxx2');
        }
    }
    /***
     * Standard function to determine the last Json decode or encode error
     * @return string
     */
    public  function getLastJSONError(){

        $result = null;
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $result = ' - No errors';
                break;
            case JSON_ERROR_DEPTH:
                $result = ' - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $result =  ' - Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $result = ' - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $result = ' - Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $result = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $result = ' - Unknown error';
                break;
        }


        return $result;
    }

    protected  function emptyList($title=null){
        $res = "{\"class\":\"pmedia.types.CList\",\"title\":\"" .$title ."\",\"order\":\"0\",\"items\":[]}";
        return $res;
    }

    /**
     * Get the user's browser default language
     *
     * @return string The language code
     *
     * @since 1.0.0
     */
    public function getBrowserDefaultLanguage() {
        $langs = array();

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {

            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

            if (count($lang_parse[1])) {

                $langs = array_combine($lang_parse[1], $lang_parse[4]);

                foreach ($langs as $lang => $val) {
                    if ($val === '') $langs[$lang] = 1;
                }

                arsort($langs, SORT_NUMERIC);
            }
        }

        return array_shift(explode('-', array_shift(array_keys($langs))));

    }


    /***
     * local object dumper
     * @param $obj
     * @param string $prefix
     * @return mixed|null
     */
    public function dumpObject($obj,$prefix=''){
        $_print = $this->getXCOption(XAPP_CONNECT_VAR_RT_CONFIG) ==='debug';
        if($_print){
            $d = print_r($obj,true);
            error_log('dump : ' .$prefix . ' : ' . $d);
            return $d;
        }
        return null;
    }
    public function log($message,$prefix='',$stdError=true){
        $_print = $this->getXCOption(XAPP_CONNECT_VAR_RT_CONFIG) ==='debug';
        if($_print){
            parent::log($message,$prefix,true);
            error_log('log : ' .$prefix . ' : ' . $message);
        }
        return null;
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

    /**
     * class destructor clears cache
     *
     * @error 16407
     * @return void
     */
    public function __destruct()
    {
        @clearstatcache();
    }
}
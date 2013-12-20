<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\Router
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
/**
 * Wrapper for SQL2JSONService. This is being used between index.php and SQL2JSONService.php
 *
 * Its purpose is to sanitize some request parameters and to init some variables.
 *
 * You can also use SchemaProcessor.php directly, doing the same thing.
 *
 */

function printJSONError()
{
    switch (json_last_error())
    {
        case JSON_ERROR_NONE:
            error_log(' - No errors',0);
            break;
        case JSON_ERROR_DEPTH:
            error_log(' - Maximum stack depth exceeded',0);
            break;
        case JSON_ERROR_STATE_MISMATCH:
            error_log(' - Underflow or the modes mismatch',0);
            break;
        case JSON_ERROR_CTRL_CHAR:
            error_log(' - Unexpected control character found',0);
            break;
        case JSON_ERROR_SYNTAX:
            error_log(' - Syntax error, malformed JSON',0);
            break;
        case JSON_ERROR_UTF8:
            error_log(' - Malformed UTF-8 characters, possibly incorrectly encoded',0);
            break;
        default:
            error_log(' - Unknown error',0);
            break;
    }
}
/***
 * Entry point for JSON-RPC2.0 function.
 * @param $queries
 * @param $schemas
 * @param $options
 * @return bool|mixed|RpcError|string
 */
function login($user,$password,$options)
{
    xapp_hide_errors();
    $writer =  new Xapp_Log_Writer_File(XAPP_BASEDIR .'/cache/');
    $logging_options = array(
        Xapp_Log::PATH  => XAPP_BASEDIR .'/cache/',
        Xapp_Log::EXTENSION  => 'log',
        Xapp_Log::NAME  => 'log',
        Xapp_Log::WRITER  => array($writer)
    );
    $log = new Xapp_Log_Error($logging_options);

    if($options!=null && is_string($options)){
        $options=json_decode($options);
    }
    if( (bool)xc_conf(XC_CONF_JOOMLA))
    {
        
        $auth = new XAppJoomlaAuth();
        $auth->logger=$log;
        $authRes = $auth->loginUser($user,$password);
        if($authRes==-1){
            $authResponse =array();
            $authResponse['code']=-1;
            $authResponse['message']=XAPP_TEXT('JLIB_LOGIN_AUTHENTICATE');
            return $authResponse;
        }else if($authRes!=-1){
            $authResponse =array();
            $authResponse['code']=1;
            $authResponse['message']=XAPP_TEXT('SUCCESS');
            return $authResponse;
        }
    }

    if( (bool)xc_conf(XC_CONF_WORDPRESS))
    {
        $auth = new XAppWordpressAuth();
        $auth->logger=$log;
        $authRes = $auth->loginUser($user,$password);
        if($authRes==-1){
            throw new Xapp_Rpc_Gateway_Exception("Wrong Username/Password", 1401511);
        }
    }
    return "-1";
}
/***
 * Entry point for JSON-RPC2.0 function.
 * @param $queries
 * @param $schemas
 * @param $options
 * @return bool|mixed|RpcError|string
 */
function templatedQuery($queries,$schemas,$options)
{

    xapp_hide_errors();

    $srv = new SQL2JSONService();

    $writer =  new Xapp_Log_Writer_File(XAPP_BASEDIR .'/cache/');

    $logging_options = array(
        Xapp_Log::PATH  => XAPP_BASEDIR .'/cache/',
        Xapp_Log::EXTENSION  => 'log',
        Xapp_Log::NAME  => 'log',
        Xapp_Log::WRITER  => array($writer)
    );
    $log = new Xapp_Log_Error($logging_options);
    $srv->logger=$log;


    if($queries!=null && is_string($queries))
    {
        $queries=json_encode($queries);
        $queries=json_decode($queries);
    }

    if($options!=null && is_string($options)){
        $options=json_decode($options);
    }
    /***
     * setup cache
     */

    if(CustomTypesUtils::$cache==null){
        CustomTypesUtils::$cache = CacheFactory::createDefaultCache();
    }

    try {
        $skipSchemaCheck =true;
        $cTypeCached = CustomTypesUtils::getTypeFromCache($options->vars->CTYPE,$options->vars->UUID,$options->vars->APPID,'IPHONE_NATIVE',$options->vars->RT_CONFIG);
        if($cTypeCached==null){
            $serviceHost = xc_conf(XC_CONF_SERVICE_HOST);
            CustomTypesUtils::getCTypesFromUrl($serviceHost,$options->vars->UUID,$options->vars->APPID,'IPHONE_NATIVE',$options->vars->RT_CONFIG);
        }else{

        }
    }catch (Exception $e){
        throw new Xapp_Rpc_Gateway_Exception("CType sync failed", 1401511);
    }


    if(!$skipSchemaCheck ||  (bool)xc_conf(XC_CONF_CHECK_SCHEMA))
    {
        //try and trigger download

        if(!$options->vars->RT_CONFIG||
           !$options->vars->CTYPE)
        {
            $srv->log('couldn`t verify schema, parameters invalid : rtc = ' . $options->vars->RT_CONFIG . ' || ctype : '.$options->vars->CTYPE);
            if(!$options->vars->RT_CONFIG){
                $srv->log('     rtconfig missing');
            }
            if(!$options->vars->CTYPE){
                $srv->log('     ctype missing');
            }
            return false;
        }

        $_schemaStr = json_encode($schemas);
        $ctype = CustomTypesUtils::getType($options->vars->CTYPE);
        if($ctype){
            $_schemaStr = str_replace('\/','/',$_schemaStr);
            $ctypeSchemas = CustomTypesUtils::getCIStringValue($ctype,'schemas');
            $ctypeSchemas = preg_replace('/[\x00-\x1F\x7F]/', '', $ctypeSchemas);
            $ctypeSchemas = preg_replace('/\r\n?/', "", $ctypeSchemas);
            $ctypeSchemas = str_replace(array("\n", ""), "", $ctypeSchemas);
            $comp = strcmp($ctypeSchemas,$_schemaStr);
            if($comp>5){
                $srv->log('schema doesnt match ' . $options->vars->CTYPE . ' diff ' . $comp);
                return '';
            }
        }else{
            $srv->log('couldn`t verify schema, cType missing');
            return false;
        }
    }



    if(is_array($schemas)){
        $_schemasOut = array();
        foreach($schemas as $schema)
        {
            $sObject=new stdClass();
            if(is_array($schema)){
                foreach ($schema as $key => $value)
                {
                    $sObject->$key = $value;
                }
            }
            array_push($_schemasOut,$sObject);
        }
        $schemas=$_schemasOut;
    }

    $replaceVars = true;
    $result =  $srv->templatedQuery($queries,$schemas,$options);
    if($replaceVars){

        if($options!=null && isset($options->vars))
        {
                $_keys = array();
                $_values = array();
                foreach ($options->vars as $key => $value)
                {
                    array_push($_keys,$key);
                    array_push($_values,$value);
                }
                $result = str_replace(
                    $_keys,
                    $_values,
                    $result
                );
        }
    }

    /***
     * in order to index older XApp-Connect types, we'll simply create a fake plugin
     * instance and let
     */
    $plgMgr = XApp_PluginManager::instance();

    //construct the args
    $plgParams = new stdClass();
    $plgParams->options = json_encode($options);
    $plgParams->schemas = json_encode($schemas);
    $plgParams->refId=null;

    $paramsStr = json_encode($plgParams);
    $fakePlugin = $plgMgr->createPluginInstance('XApp_FakePlugin',false);

    $fakePlugin->onBeforeCall($paramsStr);
    if($fakePlugin->xcType!=null){
        $fakePlugin->onAfterCall($result);
    }
    return $result;
}
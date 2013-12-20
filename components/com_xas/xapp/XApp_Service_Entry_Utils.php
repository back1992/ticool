<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 * Function to get plugin descriptions
 * @param string $type
 * @package XApp-Connect\Main
 * @param string $runTimeConfiguration
 * @return array
 */
function xapp_get_plugin_infos(){

    $res = array();
    $pluginManager = XApp_PluginManager::instance();
    if($pluginManager){
        $res['items']= $pluginManager->getPluginInfos(XAPP_PLUGIN_DIR,XAPP_PLUGIN_TYPE);
        $res['class']= 'jsontype.ComposerPackages';
    }
    return $res;
}

function xapp_setup_language($lang=null){

    if(!function_exists('XAPP_TEXT')){

        if( (bool)xc_conf(XC_CONF_JOOMLA))
        {

            $langToUse = $lang !==null ? $lang : XApp_Service_Entry_Utils::getBrowserDefaultLanguage();

            //error_log('------------setting up language helper : ' . $langToUse);

            XApp_Service_Entry_Utils::$languageClass = JLanguage::getInstance( $langToUse );
            XApp_Service_Entry_Utils::$language = $langToUse;


            function XAPP_TEXT($string,$lang=null,$component=null){


                $result = ''. $string;


                /***
                 * Catch language change
                 */
                if($lang!==null && XApp_Service_Entry_Utils::$language!=null){
                    if(XApp_Service_Entry_Utils::$language!==$lang){

                       //error_log('changing language to : ' . $lang);

                       $newInstance = JLanguage::getInstance( $lang );

                       if($newInstance){
                           //error_log(' have new instance!');
                           XApp_Service_Entry_Utils::$languageClass = JLanguage::getInstance( $lang );
                           JFactory::$language=XApp_Service_Entry_Utils::$languageClass;
                       }
                    }
                }



                if(XApp_Service_Entry_Utils::$languageClass!==null){

                    if($component){
                        XApp_Service_Entry_Utils::$languageClass->load($component);
                    }
                    $result =XApp_Service_Entry_Utils::$languageClass->_($string,true);
                    //error_log(' $languageClass result : ' . $result);
                    if($result!==null && $result!==$string){
                        //return $result;
                    }
                }
                //error_log('detected browser language : ' . XApp_Service_Entry_Utils::getBrowserDefaultLanguage());
                //error_log('JText - Result ' . JText::_($string));

                $result = JText::_($string,true);

                return $result;
            }

        }
    }
}

/***
 * Collection of handy utils for the service entry point
 * Class XApp_Service_Entry_Utils
 */
class XApp_Service_Entry_Utils {

    /***
     * XApp Service Config
     * @var
     */
    public static $serviceConf=null;


    public static $languageClass=null;
    public static $language=null;

    /**
     * Get the user's browser default language
     *
     * @return string The language code
     *
     * @since 1.0.0
     */
    public  static function getBrowserDefaultLanguage() {
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
     * Fixes common lang version problems
     */
    public static function init(){

        if( !defined( __DIR__ ) ){
            define( __DIR__, dirname(__FILE__) );
            error_log('had no __DIR__');
        }

        $XDIR = NULL;
        if (defined('__DIR__')) {
            $XDIR = __DIR__;
        }
        else {
            $XDIR = dirname(__FILE__);
        }
    }

    /***
     * Include and setup RainTpl
     */
    public static function setupRainTpl(){

        if(!class_exists('Tpl')){
            /***
             * Template Engine
             */
            include (XAPP_BASEDIR.'TplNew.php');
        }

        Tpl::$cache_dir=XAPP_BASEDIR .'/cache/';
    }
    /***
     * Include and setup RainTpl
     */
    public static function setupLucene(){

        if(!class_exists('LuceneIndexer')){
            /***
             * Template Engine
             */
            if(file_exists(XAPP_LIB . "lucene/LuceneIndexer.php")){

                include_once XAPP_LIB . "lucene/LuceneIndexer.php";
                set_include_path(get_include_path().PATH_SEPARATOR.XAPP_LIB."/lucene");
            }
        }

    }

    /***
     * Setup logger with writer
     */
    public static function setupLogger($writer=false){

        $writer = null;
        $log = null;
        $logging_options = null;

        if($writer){

            $writer =  new Xapp_Log_Writer_File(XAPP_BASEDIR .'/cache/');
            $logging_options = array(
                Xapp_Log::PATH  => XAPP_BASEDIR .'/cache/',
                Xapp_Log::EXTENSION  => 'log',
                Xapp_Log::NAME  => 'error',
                Xapp_Log::WRITER  => array($writer),
                Xapp_Log_Error::STACK_TRACE => false
            );
            $log = new Xapp_Log_Error($logging_options);
        }else{
            $logging_options = array(
                Xapp_Log::PATH  => XAPP_BASEDIR .'/cache/',
                Xapp_Log::EXTENSION  => 'log',
                Xapp_Log::NAME  => 'error',
                Xapp_Log_Error::STACK_TRACE => false
            );
            $log = new Xapp_Log_Error($logging_options);
        }

        return $log;
    }

    /***
     * Include XApp-JSON-Store Files
     */
    public static function includeXAppJSONStoreClasses(){

        if(!class_exists('Xapp_Util_JsonStorage')){
            require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Storage.php');
            require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Std/Std.php');
            require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Std/Query.php');
            require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Json/Json.php');
            require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Json/Query.php');
            require_once (XAPP_LIB.'/rpc/lib/vendor/xapp/Util/Json/Store.php');

        }
    }
    /***
     * Include XApp-Connect-Core Classes
     */
    public static function includeXAppConnectCore(){


        include(XAPP_BASEDIR . "connect/Indexer.php");//lucene wrapper

        include(XAPP_BASEDIR . "connect/Plugin.php");//plugin def
        include(XAPP_BASEDIR . "connect/IPlugin.php");//to be implemented
        include(XAPP_BASEDIR . "connect/RPCPlugin.php");//base class
        include(XAPP_BASEDIR . "connect/Configurator.php");//remove !
        include(XAPP_BASEDIR . "connect/joomla/JoomlaPlugin.php");//joomla basics

        include(XAPP_BASEDIR . "connect/FakePlugin.php");//Fake plugin will emulate a RPC plugin for older versions of XApp-Connect-Types.

        include(XAPP_BASEDIR . "connect/CustomTypeManager.php");//Sync and tools to xapp-studio.com !
        include(XAPP_BASEDIR . "connect/PluginManager.php");//Sends Messages to ./connect/Joomla/* or /connect/wordpress

        include(XAPP_BASEDIR . "connect/filter/Filter.php");//base filter class
        include(XAPP_BASEDIR . "connect/filter/Schema.php");//schema filter (Supports : Inline PHP scripting from client : Applies Result Schema on MySQL or Class queries)

    }

    /***
     * Returns PHP - POST as object
     * @return mixed|null
     */
    public static function getRawPostDecoded(){
        $_postData = file_get_contents("php://input");
        if($_postData && strlen($_postData)>0){
            $_postData=json_decode($_postData);
            if($_postData!=null){
                return $_postData;
            }
        }
        return null;
    }

    /***
     * Little helper to determine a RPC2.0 method from RAW-POST
     * @return null
     */


    public static function getUrl(){
        $pageURL = 'http';
        if (xapp_array_isset($_SERVER,"HTTPS") && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    /***
     * Little helper to determine a RPC2.0 method from RAW-POST
     * @return null
     */
    public static function isJSONP(){

        $method = $_SERVER['REQUEST_METHOD'];
        if($method==='GET'){
            $pageURL = self::getUrl();
            if(strpos($pageURL,'xapp_get_plugin_infos')!==false){
                return true;
            }
            if(strpos($pageURL,'callback')!==false){
                return true;
            }
        }

        return false;
    }

    /**
     * get the server referer from request. returns null if not found
     *
     * @error 14417
     * @return null|string
     */
    public static function getReferer($domainOnly=true)
    {
        if(strtolower(php_sapi_name()) !== 'cli')
        {
            if(getenv('HTTP_ORIGIN') && strcasecmp(getenv('HTTP_ORIGIN'), 'unknown'))
            {
                $ref = getenv('HTTP_ORIGIN');
            }
            else if(isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] && strcasecmp($_SERVER['HTTP_ORIGIN'], 'unknown'))
            {
                $ref = $_SERVER['HTTP_ORIGIN'];
            }
            else if(getenv('HTTP_REFERER') && strcasecmp(getenv('HTTP_REFERER'), 'unknown'))
            {
                $ref = getenv('HTTP_REFERER');
            }
            else if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && strcasecmp($_SERVER['HTTP_REFERER'], 'unknown'))
            {
                $ref = $_SERVER['HTTP_REFERER'];
            }else{
                $ref = false;
            }

            if($ref !== false && !empty($ref)){
                if(!$domainOnly){
                    return $ref;
                }
                if(($host = parse_url($ref, PHP_URL_HOST)) !== false)
                {
                    return trim($host);
                }
            }
        }
        return null;
    }

    /***
     * Little helper to determine debug config
     * @return null
     */
    public static function isDebug(){
        $pageURL = self::getUrl();
        $referer = self::getReferer(false);
        if($referer!==null &&  strpos($referer,'runTimeConfiguration=debug')!==false){
            error_log('is debug : ' . $referer);
            return true;
        }
        if(strpos($pageURL,'debug=true')!==false){
            error_log('is debug : ' . $referer);
            return true;
        }
        return false;
    }

    /***
     * Returns the JSON-RPC-SMD Method of the current PHP POST
     * @return null
     */
    public static function getSMDMethod(){
        $_postData = self::getRawPostDecoded();
        if($_postData!=null){
            if($_postData->method!=null){
                return $_postData->method;
            }
        }
        return null;
    }

    /***
     * @TODO, enable, increase APC !!
     */
    function xapp_tune_apc(){

        $apc = ini_get('apc.max_file_size');
        $apcSet = ini_set('apc.max_file_size', $apc);
        echo ('apc'  .$apc . ' could set : ' . $apcSet);
        if($apcSet){
            echo ('could set apc cache size');
        }else{
            echo ('could not set apc cache size');
        }

    }
}

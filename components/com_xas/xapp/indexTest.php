<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

define('XAPPED', true);

/*****
 * Load System Config

 * this will replace in the deployment step (xas-joomla/install/scripts/buildRelease.sh) to : conf.inc.release.php. This config doesn't allow lots of things
 */
define('XAPP_CONNECT_CONFIG', "conf.inc.debug.php");

/***
 * Working directory
 */
define("XAPP_BASEDIR", realpath(dirname(__FILE__)) . "/");

/***
 * Libs
 */
define("XAPP_LIB", realpath(dirname(__FILE__)) . "/lib/");

/***
 * Custom Type location for deployed version
 */
define("XAPP_CTYPES", XAPP_BASEDIR . "ctypes/");

/***
 * Global cache directory (must write = 755)
 */
define("XAPP_DEFAULT_LOG_PATH", XAPP_BASEDIR .'/cache/');


/***
 * Turn on profiler, see xapp-gateway setup
 */
//define("XAPP_CONF_PROFILER_MODE", null);


/***
 * global logger
 * @TODO : remove this
 */
$xapp_logger=null;

/***
 * includes, wp & joomla
 */
require_once(XAPP_BASEDIR . "includes.php");
/***
 * system constants
 */
require_once(XAPP_BASEDIR . "defines.php");

/***
 * wrapper for xapp_conf
 */
include(XAPP_BASEDIR . "conf.php");//conf wrapper

/***
 * ?
 */
include(XAPP_BASEDIR . XAPP_CONNECT_CONFIG);//conf data


/***
 * Lucene Includes
 * @TODO, load on plugin introspection (nodejs like package.json)
 */
include_once XAPP_LIB . "lucene/LuceneIndexer.php";
set_include_path(get_include_path().PATH_SEPARATOR.XAPP_LIB."/lucene");

/***
 * Template - Engine - Setup
 * @TODO : remove
 */
$config = array(
    "base_url"      => null,
    "tpl_dir"       => "templates/test/",
    "cache_dir"     => "cache/",
    "debug"         => true,
    "auto_escape"   => false,
    'php_enabled'       => true
);

Tpl::configure( $config );


/***
 * Joomla include switch, loads Joomla Core
 */
if( (bool)xc_conf(XC_CONF_JOOMLA))
{
    require_once(XAPP_BASEDIR . "jincludes.php");
    include_once XAPP_LIB . "db/JoomlaDB.php";
    include_once XAPP_LIB . "joomla/XAppJoomlaAuth.php";
}

/***
 * @TODO to be moved, otherwise collisions
 * RPC Function to search over all registered Custom Types
 * @param $query
 * @return string
 */
function search($query){

    //has singleton self constructor
    $plgManager = XApp_PluginManager::instance();

    if( (bool)xc_conf(XC_CONF_JOOMLA))
    {
        /***
         * @TODO : Replace with new package.info ala Node.JS
         */

        //Prepare plugins
        $plgManager->loadPlugin(XAPP_BASEDIR . "connect/joomla/driver/",'VMart');

        //hard coded :
        $plgManager->createPluginInstance('VMart',false);//true RPC driver

        //old MySQL driver based Custom Types, just wrapped
        $plgManager->createFakePluginInstance('K2',false);
        $plgManager->createFakePluginInstance('JA',false);
        $plgManager->createFakePluginInstance('JC',false);

        //tell plugins, we're going to search
        $plgManager->onSearchBegin();


        //collect & result results from plugins
        $searchResults = array();
        $searchResults['items']=array();

        $plgInstances = $plgManager->getPluginInstances();

        $foundItems=false;
        foreach($plgInstances as $plg){
            $foundItems=true;
            if(!method_exists($plg,'search')){
                error_log('no such method : search');
                continue;
            }
            $plgSearchResults = $plg->search($query);
            if(count($plgSearchResults)){
                $searchResults['items'] = array_merge($searchResults['items'],$plgSearchResults);
            }else{
                $searchResults['items'] = array_merge($searchResults['items'],array());
            }
        }
        $searchResults["class"] = "pmedia.types.CList";

        $searchResults["sourceType"]=null;

        if($foundItems==false){
            $searchResults['items']=null;
        }
        //xapp_dumpObject($searchResults,'plg all search results');
        //xapp_print_memory_stats('xapp-search:end');// you better check, lucene is slow !

        /***
         * @TODO : turn the lights off : plugin::onSearchEnd()
         */
        return json_encode($searchResults);
    }
    return "{}";
}
/***
 * Little helper to decode RPC args from PHP-RAW-POST
 * @TODO, move to xapp::gateway
 * @return null
 */
function getRawPostDecoded(){
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
function getSMDMethod(){
    $_postData = getRawPostDecoded();
    if($_postData!=null){
        if($_postData->method!=null){
            return $_postData->method;
        }
    }
    return null;
}

try{

    /***
     * Handle Fatal Error
     */
    $writer =  new Xapp_Log_Writer_File(XAPP_BASEDIR .'/cache/');
    $logging_options = array(
        Xapp_Log::PATH  => XAPP_BASEDIR .'/cache/',
        Xapp_Log::EXTENSION  => 'log',
        Xapp_Log::NAME  => 'error',
        Xapp_Log::WRITER  => array($writer),
        Xapp_Log_Error::STACK_TRACE => true
    );
    $log = new Xapp_Log_Error($logging_options);

    //defined at head
    global $xapp_logger;
    //assign to global
    $xapp_logger=$log;

    //setup xapp-php
    $conf = array
    (
        XAPP_CONF_DEBUG_MODE => true,
        XAPP_CONF_AUTOLOAD => false,
        XAPP_CONF_DEV_MODE => true,
        XAPP_CONF_HANDLE_BUFFER => true,
        XAPP_CONF_HANDLE_SHUTDOWN => false,
        XAPP_CONF_HTTP_GZIP => true,
        XAPP_CONF_CONSOLE => false,
        XAPP_CONF_HANDLE_ERROR => false,
        XAPP_CONF_HANDLE_EXCEPTION => true,
        XAPP_CONF_LOG_ERROR => $log,
        XAPP_CONF_PROFILER_MODE=>null,
        //XAPP_CONF_PROFILER_MODE=>XAPP_BASEDIR .'/cache/' : activated at head
    );


    Xapp::run($conf);

    xapp_print_memory_stats('xapp-run');//disabled in release

    include(XAPP_BASEDIR . XAPP_CONNECT_CONFIG);//conf data
    include(XAPP_BASEDIR . "connect/driver/rpc/IRPCClass.php");//empty
    include(XAPP_BASEDIR . "connect/driver/rpc/RPCClass.php");//empty


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

    $method = $_SERVER['REQUEST_METHOD'];

    /***
     * Includes
     */
    if($method==='GET'){
        //@TODO : package.json
        include(XAPP_BASEDIR . "connect/joomla/driver/VMart.php");//load VMart for SMD introspection only
        include(XAPP_BASEDIR . "connect/joomla/driver/XCJoomla.php");//load Joomla for SMD introspection only
    }

    $xappConnectConfigurator = Xapp_Connect_Configurator::instance($xappConnectServiceConf);// @TODO : die !

    xapp_set_option(XC_CONF_LOGGER,$log,$xappConnectServiceConf);

    //xapp-php imports
    xapp_import('xapp.Rpc.*');
    xapp_import('xapp.Log.*');
    xapp_import('xapp.Cache.*');

    /**************************************************************************************/
    /*                          RPC-SMD Service                                           */

    //Options for SMD based RPC classes
    $opt = array
    (
        Xapp_Rpc_Smd::IGNORE_METHODS=> array('load', 'setup','log','onBeforeCall','onAfterCall','dumpObject','applyFilter','getLastJSONError','cleanUrl','rootUrl','siteUrl','getXCOption','getIndexer','getIndexOptions','getIndexOptions','indexDocument','onBeforeSearch','toDSURL','searchTest'),
        Xapp_Rpc_Smd::IGNORE_PREFIXES => array('_', '__')
    );
    $smd = new Xapp_Rpc_Smd_Json($opt);


    //Options for RPC server
    $opt = array
    (
        Xapp_Rpc_Server::ALLOW_FUNCTIONS => true,
        Xapp_Rpc_Server::APPLICATION_ERROR => false,
        Xapp_Rpc_Server::METHOD_AS_SERVICE =>false,
        Xapp_Rpc_Server::DEBUG => true,
        Xapp_Rpc_Server::SMD => $smd
    );

    $server = Xapp_Rpc::server('json', $opt);


    /**************************************************************************************/
    /*                          RPC-SMD-JSONP Service Variant : In progress               */

    //Options for SMD based JSONP-RPC classes
    /*

    $opt = array
    (
        Xapp_Rpc_Smd::IGNORE_METHODS=> array('load', 'setup','log','onBeforeCall','onAfterCall','dumpObject','applyFilter','getLastJSONError','cleanUrl','rootUrl','siteUrl','getXCOption','getIndexer','getIndexOptions','getIndexOptions','indexDocument','onBeforeSearch','toDSURL','searchTest'),
        Xapp_Rpc_Smd::IGNORE_PREFIXES => array('_', '__')
    );
    $smd = new Xapp_Rpc_Smd_Jsonp($opt);

    //Options for RPC server
    $opt = array
    (
        Xapp_Rpc_Server::ALLOW_FUNCTIONS => true,
        Xapp_Rpc_Server::APPLICATION_ERROR => false,
        Xapp_Rpc_Server::METHOD_AS_SERVICE =>false,
        Xapp_Rpc_Server::DEBUG => true,
        Xapp_Rpc_Server::SMD => $smd
    );

    $server = Xapp_Rpc::server('jsonp', $opt);

    */

    /**************************************************************************************/
    /*                          Custom-Type-Cache                                         */
    $cache = Xapp_Cache::instance("ct","file",array(
        Xapp_Cache_Driver_File::PATH=>xapp_get_option(XC_CONF_CACHE_PATH,$xappConnectServiceConf),
        Xapp_Cache_Driver_File::CACHE_EXTENSION=>"xcCTcache",
        Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>2

    ));
    xapp_print_memory_stats('xapp-rpc-setup');

    /***
     * On post, we setup cache, custom type manager, and xapp gateway
     */
    if($method==='POST'){
        $serviceClass=null;

        //setup plugin manager
        $pluginManager = XApp_PluginManager::instance(array(
            //cache configuration
            XApp_PluginManager::CACHE_CONF=>array(
                Xapp_Cache_Driver_File::PATH=>xapp_get_option(XC_CONF_CACHE_PATH,$xappConnectServiceConf),
                Xapp_Cache_Driver_File::CACHE_EXTENSION=>"PluginManager",
                Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>500),//the cache config is shared to plugins !

            //service configuration
            XApp_PluginManager::SERVICE_CONF=>$xappConnectServiceConf,//xapp connect config
            //service configuration
            XApp_PluginManager::LOGGING_CONF=>$logging_options,//logger or/and logger config
        ));

        //setup custom type manager
        $ctManager = CustomTypeManager::instance(array(

            //cache configuration
            CustomTypeManager::CACHE_CONF=>array(
                Xapp_Cache_Driver_File::PATH=>xapp_get_option(XC_CONF_CACHE_PATH,$xappConnectServiceConf),
                Xapp_Cache_Driver_File::CACHE_EXTENSION=>"ctManager",
                Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>500),

            //service configuration
            CustomTypeManager::SERVICE_CONF=>$xappConnectServiceConf
        ));


        /***
         * Load the plugin
         */
        $method = getSMDMethod();

        //error_log('method : ' .$method);

        //1st, look for custom and include it
        if($method!=null && strpos($method,'.')!=-1){

            $methodSplitted = explode('.', $method);

            if($methodSplitted && count($methodSplitted)==2){

                $pluginManager->loadPlugin(XAPP_BASEDIR . "connect/joomla/driver/",$methodSplitted[0]);

                if(class_exists($methodSplitted[0])){
                    $serviceClass=$methodSplitted[0];
                }else{
                    error_log('couldnt include class : ' .$methodSplitted[0]);
                }
            }

        }
        if($serviceClass!=null){
            $plugin  = $pluginManager->createPluginInstance($serviceClass);
            if($plugin!=null){
                $server->register($plugin,array('_load'));
            }


        }
    }elseif($method==='GET'){
        $server->register("VMart");
        $server->register("XCJoomla");

        $testClass = "XCJoomla";

        $testClassInstance = new $testClass();

        $testResult = $testClassInstance->test();
        echo $testResult;
        exit;

    }

    $server->register('templatedQuery');
    $server->register('login');
    $server->register('search');

    $opt = array
    (
        Xapp_Rpc_Gateway::OMIT_ERROR => false
    );
    $gateway = Xapp_Rpc_Gateway::create($server, $opt);
    $gateway->run();

    xapp_print_memory_stats('xapp-connect-entry-end');


}
catch(Exception $e)
{
    /***
     * @TODO : turn the lights off !
     */
    Xapp_Rpc_Server_Json::dump($e);
}

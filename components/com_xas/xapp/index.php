<?php

/**
 * @version 0.1.0
 * @package XApp-Connect\Main
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);


/***
 * PHP Version handling
 */
if( !defined( __DIR__ ) )define( __DIR__, dirname(__FILE__) );

define('XAPPED', true);
define('XAPP_CONNECT_CONFIG', "conf.inc.release.php");
define("XAPP_BASEDIR", realpath(dirname(__FILE__)) . "/");
define("XAPP_LIB", realpath(dirname(__FILE__)) . "/lib/");
define("XAPP_CTYPES", XAPP_BASEDIR . "ctypes/");
define("XAPP_DEFAULT_LOG_PATH", XAPP_BASEDIR .'/cache/');
define("XAPP_CONNECT_PATH", XAPP_BASEDIR . "connect/");
define("XAPP_FORM_DATA_PATH", XAPP_CONNECT_PATH . "forms/");

/***
 * Plugin Config Location
 */
define("XAPP_PLUGIN_DIR", XAPP_BASEDIR .'/plugins-enabled/');

/***
 * Plugin Type
 */
define("XAPP_PLUGIN_TYPE", 'Joomla');


//benchmarking
//define("XAPP_CONF_PROFILER_MODE", null);

/***
 * Just in case, here is a file logger : look in ./cache
 */
$xapp_logger=null;

/***
 * Handy tools for this service entry point
 */
include(XAPP_BASEDIR . 'XApp_Service_Entry_Utils.php');//conf data
XApp_Service_Entry_Utils::init();



/***
 * Includes and very basics
 */
require_once(XAPP_BASEDIR . "includes.php");
require_once(XAPP_BASEDIR . "defines.php");
include(XAPP_BASEDIR . "conf.php");//conf wrapper

/***
 * The xapp connect config has many debugging switchs
 */
include(XAPP_BASEDIR . XAPP_CONNECT_CONFIG);//conf data

/***
 * Include Joomla's core files
*/
if( (bool)xc_conf(XC_CONF_JOOMLA))
{
    require_once(XAPP_BASEDIR . "jincludes.php");
    include_once XAPP_LIB . "db/JoomlaDB.php";
    include_once XAPP_LIB . "joomla/XAppJoomlaAuth.php";
}

/***
 * Include lucene
 */
if( (bool)xc_conf(XC_CONF_HAS_LUCENE))
{
    XApp_Service_Entry_Utils::setupLucene();
}


/***
 * @TODO : to be moved out
 * @param $query
 * @return string
 */
function search($query){


    //error_log('search ' .$query);
    /***
     * Lucene Includes
     */
    //include_once XAPP_LIB . "lucene/LuceneIndexer.php";
    //set_include_path(get_include_path().PATH_SEPARATOR.XAPP_LIB."/lucene");

    $plgManager = XApp_PluginManager::instance();

    if( (bool)xc_conf(XC_CONF_JOOMLA))
    {
        //xapp_print_memory_stats('xapp-search:start');
        /***
         * @TODO : Replace with new package.info ala Node.JS
         */

        //Prepare plugins
        //$plgManager->loadPlugin(XAPP_BASEDIR . "connect/joomla/driver/",'VMart');

        //hard coded :
        $plgManager->createPluginInstance('VMart',false);

        $plgManager->createFakePluginInstance('K2',false);
        $plgManager->createFakePluginInstance('JA',false);
        $plgManager->createFakePluginInstance('JC',false);



        $plgManager->onSearchBegin();

        $searchResults = array();
        $searchResults['items']=array();

        $plgInstances = $plgManager->getPluginInstances();

        //error_log('have ' . count($plgInstances) . ' plugins to search');

        $foundItems=false;
        foreach($plgInstances as $plg){
            $foundItems=true;
            //$plg->onBeforeSearch();
            //error_log('searching in plugin : '.$plg->CACHE_NS);
            if(!method_exists($plg,'search')){
                //error_log('no such method : search');
                continue;
            }

            $plgSearchResults = $plg->search($query);
            //xapp_dumpObject($plgSearchResults,'plg search results');

            if(count($plgSearchResults)){
                $searchResults['items'] = array_merge($searchResults['items'],$plgSearchResults);
                //xapp_dumpObject($searchResults['items'],'plg search results in');
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

        //xapp_print_memory_stats('xapp-search:end');

        return json_encode($searchResults);

    }

    //xapp_dumpObject($plgManager->getPluginInstances());

    return "{}";
}

try{

    /***
     * Register fatal error handler, its written to the log file as well
     */
    $log = XApp_Service_Entry_Utils::setupLogger(XApp_Service_Entry_Utils::isDebug());

    global $xapp_logger;
    $xapp_logger=$log;//track global, see top

    /***
     * xapp-php core config
     */
    $conf = array
    (
        XAPP_CONF_DEBUG_MODE => true,
        XAPP_CONF_AUTOLOAD => false,
        XAPP_CONF_DEV_MODE => false,
        XAPP_CONF_HANDLE_BUFFER => true,
        XAPP_CONF_HANDLE_SHUTDOWN => false,
        XAPP_CONF_HTTP_GZIP => true,
        XAPP_CONF_CONSOLE => null,
        XAPP_CONF_HANDLE_ERROR => null,
        XAPP_CONF_HANDLE_EXCEPTION => true,
        XAPP_CONF_EXECUTION_TIME => null,
        XAPP_CONF_LOG_ERROR => $log,
        XAPP_CONF_PROFILER_MODE=>null
    );

    Xapp::run($conf);
    xapp_print_memory_stats('xapp-run');
    include(XAPP_BASEDIR . XAPP_CONNECT_CONFIG);//conf data


    //xapp_console('xapp console message','label','dump', $conf);
    //xapp_cdump('conf',$conf);
    //function xapp_console($m = null, $l = null, $t = 'info', Array $o = array())


    include(XAPP_BASEDIR . "connect/Indexer.php");
    include(XAPP_BASEDIR . "connect/Plugin.php");
    include(XAPP_BASEDIR . "connect/IPlugin.php");
    include(XAPP_BASEDIR . "connect/RPCPlugin.php");
    include(XAPP_BASEDIR . "connect/Configurator.php");
    include(XAPP_BASEDIR . "connect/joomla/JoomlaPlugin.php");

    //Fake plugin will emulate a RPC plugin for older versions of XApp-Connect-Types.
    include(XAPP_BASEDIR . "connect/FakePlugin.php");

    include(XAPP_BASEDIR . "connect/CustomTypeManager.php");
    include(XAPP_BASEDIR . "connect/PluginManager.php");

    include(XAPP_BASEDIR . "connect/filter/Filter.php");
    include(XAPP_BASEDIR . "connect/filter/Schema.php");

    xapp_setup_language();

    //xapp_print_memory_stats('xapp-connect-includes');


    //load plugins for SMD introspection
    $method = $_SERVER['REQUEST_METHOD'];

    /***
     * New : plugin configurations in composer compatible format
     *       the config holds a path prefix and other neat things
     *       like client side resources, resolved by xapp variables
     */

    //minimal instance
    $xappPluginManager = new XApp_PluginManager();
    $loadedPlugins = null;

    //load plugins for SMD Introspection

    if($method==='GET'){
        //include(XAPP_BASEDIR . "connect/joomla/driver/VMart.php");
        $loadedPlugins = $xappPluginManager->loadPlugins(XAPP_PLUGIN_DIR,XAPP_BASEDIR,XAPP_PLUGIN_TYPE);
    }

    //@TODO : REMOVE
    $xappConnectConfigurator = Xapp_Connect_Configurator::instance($xappConnectServiceConf);
    xapp_set_option(XC_CONF_LOGGER,$log,$xappConnectServiceConf);


    /***
     * load minimal bundle of xapp-php
     */
    xapp_import('xapp.Rpc.*');
    xapp_import('xapp.Log.*');
    xapp_import('xapp.Cache.*');


    /***
     * In progress, automatic JSONP wrapping of previous 'SMD calls'
     */
    $isJSONP = false;
    $hasJSONP = xapp_get_option(XC_CONF_ALLOW_JSONP,$xappConnectServiceConf);
    if($hasJSONP){
        $isJSONP = XApp_Service_Entry_Utils::isJSONP();
    }

    //$hasJSONP=false;

    if($method==='POST'){
        $hasJSONP=false;
    }

    if($hasJSONP && $isJSONP){

        /**************************************************************************************/
        /*                          RPC-SMD-JSONP Service Variant : In progress               */

        //@TODO
        /***
         *
         * Server side :
         *  + create in RPCPlugin a method : customTypeQuery(params={}) : DONE
         *  + create new cache key per JSONP-Request : DONE
         *  + extend Custom-Type Utils and run relations : DONE
         *  + add CMS filters at concrete class
         *  +
         *
         * Client side:
         *  + transform driver calls to JSONP at ConnectManager : DONE
         *  + fix list models for fragmented rendering : DONE
         *
         */


        //Options for SMD based JSONP-RPC classes
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
            Xapp_Rpc_Server::METHOD_AS_SERVICE =>true,
            Xapp_Rpc_Server::DEBUG => true,
            Xapp_Rpc_Server::SMD => $smd
        );

        $server = Xapp_Rpc::server('jsonp', $opt);

    }else{

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




    }





    /***
     * Old xapp-connect version, new is 'customTypeQuery' only.
     */
    $server->register('templatedQuery');

    /**************************************************************************************/
    /*                          Custom-Type-Cache                                         */
    $cache = Xapp_Cache::instance("ct","file",array(
        Xapp_Cache_Driver_File::PATH=>xapp_get_option(XC_CONF_CACHE_PATH,$xappConnectServiceConf),
        Xapp_Cache_Driver_File::CACHE_EXTENSION=>"xcCTcache",
        Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>2

    ));

    xapp_print_memory_stats('xapp-rpc-setup');


    $pluginManager=null;
    $ctManager = null;

    //setup plugin & custom type manager for RPC-POST or JSONP
    if($method==='POST' || $isJSONP){
        //setup plugin manager
        $pluginManager = XApp_PluginManager::instance(array(
            //cache configuration
            XApp_PluginManager::CACHE_CONF=>array(
                Xapp_Cache_Driver_File::PATH=>xapp_get_option(XC_CONF_CACHE_PATH,$xappConnectServiceConf),
                Xapp_Cache_Driver_File::CACHE_EXTENSION=>"PluginManager",
                Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>500),

            //service configuration
            XApp_PluginManager::SERVICE_CONF=>$xappConnectServiceConf,
            //service configuration
            XApp_PluginManager::LOGGING_CONF=>$log->options
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
    }


    if($method==='POST'){


        /***
         * service class is the name of the plugin (see configs in ./plugins-enabled)
         */
        $serviceClass=null;

        /***
         * Load the plugin and its deps plugin conf by XC-Service Class Name
         */
        $method = XApp_Service_Entry_Utils::getSMDMethod();

        //there is the service class and method
        if($method!=null && strpos($method,'.')!=-1){

            $methodSplitted = explode('.', $method);

            if($methodSplitted && count($methodSplitted)==2){


                $pluginConfig =$pluginManager->hasPluginConfiguration(XAPP_PLUGIN_DIR,$methodSplitted[0],XAPP_PLUGIN_TYPE);
                if($pluginConfig!=null){
                    $pluginManager->loadPluginWithConfiguration(XAPP_BASEDIR,$pluginConfig);
                    $serviceClass = $pluginConfig->name;
                }
            }

        }

        /***
         * Now fire it up and bind it to xapp-php-rpc server
         */
        if($serviceClass!=null){
            $plugin  = $pluginManager->createPluginInstance($serviceClass);
            if($plugin!=null){
                $server->register($plugin,array('_load'));
            }

        }

    }elseif($method==='GET'){
        /***
         *  In GET there is only soft loading for SMD introspection
         */


        /***
         * When the plugin allows SMD introspection, register it for the RPC Server
         */
        if(!$isJSONP)
        {
            if($loadedPlugins && count($loadedPlugins)>0)
            {
                foreach($loadedPlugins as $pluginConfig)
                {
                    if( property_exists($pluginConfig,'showSMD')  && $pluginConfig->showSMD==true){
                        $server->register($pluginConfig->name);
                    }
                }
            }
        }else{


            /***
             * its jsonp call in get modus
             */

            //get service class
            $parts = parse_url(XApp_Service_Entry_Utils::getUrl());
            parse_str($parts['query'], $query);
            $serviceClass = null;
            if(array_key_exists('service',$query)){

                $method = $query['service'];

                if($method!=null && strpos($method,'.')!=-1){

                    $methodSplitted = explode('.', $method);

                    if($methodSplitted && count($methodSplitted)==2){


                        $pluginConfig =$pluginManager->hasPluginConfiguration(XAPP_PLUGIN_DIR,$methodSplitted[0],XAPP_PLUGIN_TYPE);
                        if($pluginConfig!=null){
                            $pluginManager->loadPluginWithConfiguration(XAPP_BASEDIR,$pluginConfig);
                            $serviceClass = $pluginConfig->name;
                        }
                    }

                }
            }

            /***
             * Now fire it up and bind it to xapp-php-rpc server
             */
            if($serviceClass!=null){
                $plugin  = $pluginManager->createPluginInstance($serviceClass);
                if($plugin!=null){
                    $server->register($plugin,array('_load'));
                }

            }

            /***
             * New : get plugin resources
             */
            $server->register('xapp_get_plugin_infos');

        }
    }

    XApp_Service_Entry_Utils::setupRainTpl();

    /***
     * Register global functions
     */
    $server->register('login');
    $server->register('search');

    /***
     * Create RPC Gateway, taking care of lots of other things too
     */
    $opt = array
    (
        Xapp_Rpc_Gateway::OMIT_ERROR => true,
        Xapp_Rpc_Gateway::VALIDATE => XApp_Service_Entry_Utils::isDebug()
    );
    $gateway = Xapp_Rpc_Gateway::create($server, $opt);

    //punch it
    $gateway->run();

    xapp_print_memory_stats('xapp-connect-entry-end');


}
catch(Exception $e)
{
    Xapp_Rpc_Server_Json::dump($e);
}
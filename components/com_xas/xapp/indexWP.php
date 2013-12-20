<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

define('XAPPED', true);

//attention : conf.inc.debug.wp.php will be replaced by build script to conf.inc.release.wp.php
define('XAPP_CONNECT_CONFIG', "conf.inc.debug.wp.php");
define("XAPP_BASEDIR", realpath(dirname(__FILE__)) . "/");
define("XAPP_LIB", realpath(dirname(__FILE__)) . "/lib/");
define("XAPP_CTYPES", XAPP_BASEDIR . "ctypes/");
define("XAPP_DEFAULT_LOG_PATH", XAPP_BASEDIR .'/cache/');
/***
 * Plugin Config Location
 */
define("XAPP_PLUGIN_DIR", XAPP_BASEDIR .'/plugins-enabled/');

/***
 * Plugin Type
 */
define("XAPP_PLUGIN_TYPE", 'Wordpress');

//global logger
$xapp_logger=null;


require_once(XAPP_BASEDIR . "includesMainWP.php");
require_once(XAPP_BASEDIR . "defines.php");
include(XAPP_BASEDIR . "conf.php");//conf wrapper
include(XAPP_BASEDIR . XAPP_CONNECT_CONFIG);//conf data


include(XAPP_BASEDIR . 'XApp_Service_Entry_Utils.php');//conf data

/***
 * Lucene Includes
 */
include_once XAPP_LIB . "lucene/LuceneIndexer.php";
set_include_path(get_include_path().PATH_SEPARATOR.XAPP_LIB."/lucene");


$config = array(
    "base_url"      => null,
    "tpl_dir"       => "templates/test/",
    "cache_dir"     => "cache/",
    "debug"         => true,
    "auto_escape"   => false,
    'php_enabled'       => true
);

Tpl::configure( $config );



if( (bool)xc_conf(XC_CONF_WORDPRESS))
{
    include './wpincludes.php';
    include_once XAPP_LIB . "wordpress/XAppWordpressAuth.php";
}
try{

    /***
     * Fatal error
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


    global $xapp_logger;//track this global
    $xapp_logger=$log;



    //setup XApp-PHP basics
    $conf = array
    (
        XAPP_CONF_DEBUG_MODE => false,
        XAPP_CONF_AUTOLOAD => false,
        XAPP_CONF_DEV_MODE => true,
        XAPP_CONF_HANDLE_BUFFER => true,
        XAPP_CONF_HANDLE_SHUTDOWN => false,
        XAPP_CONF_HTTP_GZIP => true,
        XAPP_CONF_CONSOLE => false,
        XAPP_CONF_HANDLE_ERROR => false,
        XAPP_CONF_HANDLE_EXCEPTION => true,
        XAPP_CONF_EXECUTION_TIME => null,
        XAPP_CONF_LOG_ERROR => $log,
        XAPP_CONF_PROFILER_MODE=>null
        /***
         * @TODO : Where to keep profiling settings ?
         */
        //XAPP_CONF_PROFILER_MODE=>XAPP_BASEDIR .'/profile/'
        //XAPP_CONF_PROFILER_MODE=>XAPP_BASEDIR .'/cache/'
    );

    Xapp::run($conf);


    //load our XApp-Connect config
    include(XAPP_BASEDIR . XAPP_CONNECT_CONFIG);//conf data



    //base classes
    include(XAPP_BASEDIR . "connect/Indexer.php");
    include(XAPP_BASEDIR . "connect/Plugin.php");
    include(XAPP_BASEDIR . "connect/IPlugin.php");
    include(XAPP_BASEDIR . "connect/RPCPlugin.php");

    include(XAPP_BASEDIR . "connect/wordpress/WordpressPlugin.php");
    //manager
    include(XAPP_BASEDIR . "connect/CustomTypeManager.php");
    include(XAPP_BASEDIR . "connect/PluginManager.php");

    //filter
    include(XAPP_BASEDIR . "connect/filter/Filter.php");
    include(XAPP_BASEDIR . "connect/filter/Schema.php");

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
        $loadedPlugins = $xappPluginManager->loadPlugins(XAPP_PLUGIN_DIR,XAPP_BASEDIR,XAPP_PLUGIN_TYPE);
    }

    xapp_set_option(XC_CONF_LOGGER,$log,$xappConnectServiceConf);

    //more XApp-PHP stuff to include
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

    /**************************************************************************************/
    /*                          Custom-Type-Cache                                         */
    $cache = Xapp_Cache::instance("ct","file",array(
        Xapp_Cache_Driver_File::PATH=>xapp_get_option(XC_CONF_CACHE_PATH,$xappConnectServiceConf),
        Xapp_Cache_Driver_File::CACHE_EXTENSION=>"xcCTcache",
        Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>2

    ));


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
            XApp_PluginManager::LOGGING_CONF=>$logging_options
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


    /***
     * @TODO : Wire auth with XApp-Connect base classes
     */
    $server->register('login');
    $opt = array
    (
        Xapp_Rpc_Gateway::OMIT_ERROR => true
    );
    $gateway = Xapp_Rpc_Gateway::create($server, $opt);
    $gateway->run();

    /***
     * @TODO : Who turns the light off ?
     */

}
catch(Exception $e)
{
    Xapp_Rpc_Server_Json::dump($e);
}

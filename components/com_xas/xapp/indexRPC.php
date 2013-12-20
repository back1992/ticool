<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);

define('XAPPED', true);
define("XAPP_BASEDIR", realpath(dirname(__FILE__)) . "/");
define("XAPP_LIB", realpath(dirname(__FILE__)) . "/lib/");
define("XAPP_CTYPES", XAPP_BASEDIR . "ctypes/");

define("XAPP_DEFAULT_LOG_PATH", XAPP_BASEDIR .'/cache/');

require_once(XAPP_BASEDIR . "includes.php");
require_once(XAPP_BASEDIR . "defines.php");

include(XAPP_BASEDIR . "conf.php");//conf wrapper

include(XAPP_BASEDIR . "conf.inc.php");//conf data

$config = array(
    "base_url"      => null,
    "tpl_dir"       => "templates/test/",
    "cache_dir"     => "cache/",
    "debug"         => true,
    "auto_escape"   => false,
    'php_enabled'       => true
);

Tpl::configure( $config );

if( (bool)xc_conf(XC_CONF_JOOMLA))
{


    include './jincludes.php';
    include_once XAPP_LIB . "db/JoomlaDB.php";
    include_once XAPP_LIB . "joomla/XAppJoomlaAuth.php";
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



    $conf = array
    (
        XAPP_CONF_DEBUG_MODE => false,
        XAPP_CONF_AUTOLOAD => false,
        XAPP_CONF_DEV_MODE => true,
        XAPP_CONF_HANDLE_BUFFER => true,
        XAPP_CONF_HANDLE_SHUTDOWN => true,
        XAPP_CONF_HTTP_GZIP => true,
        XAPP_CONF_CONSOLE => false,
        XAPP_CONF_HANDLE_ERROR => true,
        XAPP_CONF_HANDLE_EXCEPTION => true,
        XAPP_CONF_LOG_ERROR => $log

    );
    //XAPP_CONF_PROFILER_MODE=>XAPP_BASEDIR .'/profile/'
    //XAPP_CONF_PROFILER_MODE=>XAPP_BASEDIR .'/cache/'
    Xapp::run($conf);

    include(XAPP_BASEDIR . "conf.inc.php");//conf data
    include(XAPP_BASEDIR . "RPCTestClass.php");

    xapp_import('xapp.Rpc.*');
    xapp_import('xapp.Log.*');
    //xapp_import('xapp.Profile.*');
    /*xapp_import('xapp.Cache.*');*/

    $opt = array
    (
        Xapp_Rpc_Server::ALLOW_FUNCTIONS => true,
        Xapp_Rpc_Server::APPLICATION_ERROR => false,
        Xapp_Rpc_Server::DEBUG => true
    );


    $server = Xapp_Rpc::server('json', $opt);
    $server->register('RPCTestClass');
    $opt = array
    (
    );
    $gateway = Xapp_Rpc_Gateway::create($server, $opt);
    $gateway->run();
}
catch(Exception $e)
{
    Xapp_Rpc_Server_Json::dump($e);
}

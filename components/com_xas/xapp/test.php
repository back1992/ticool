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

require_once (XAPP_LIB .'utils2/Iterator.php');
require_once (XAPP_LIB .'utils2/ObjectIterator.php');
require_once (XAPP_LIB .'utils2/ArrayIterator.php');


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



    /***
     * 1st Test : ObjectIterator
     */

    $_cls = new stdClass();
    $_cls->key = 'UUID';
    $_cls->value = 'my uuid';
    $_cls->UUID = 'UUIDKey';

    $objIt = new Xapp_ObjectIterator($_cls);

    /***    Test iterator access    ***/
    print('1. object iterator loop test ');
    while ($objIt->valid())
    {
        $current = $objIt->current();
        var_dump($current);
        $objIt->next();
    }

    print('2. object iterator access begin() - test');
    var_dump($objIt->begin());

    print('3. object iterator access end() - test');
    var_dump($objIt->end());

    print('4. object array access - test');
    var_dump($objIt->at(0));

    print('5. object array access - last pos - test : ' .$objIt->size());
    var_dump($objIt->at($objIt->size()-1));

    print('6. object key access - test ');
    var_dump($objIt->get("key"));

    print('7. object indexOf - test');
    var_dump($objIt->indexOf('UUID'));


    /***
     * 2nd Test : ArrayIterator
     */
    $arr = array
    (
        XAPP_CONF_DEBUG_MODE => false,
        XAPP_CONF_AUTOLOAD => false,
        XAPP_CONF_DEV_MODE => true,
        XAPP_CONF_HANDLE_BUFFER => true,
        XAPP_CONF_HANDLE_SHUTDOWN => true,
        XAPP_CONF_HTTP_GZIP => true,
        XAPP_CONF_CONSOLE => false,
        XAPP_CONF_HANDLE_ERROR => true,
        XAPP_CONF_HANDLE_EXCEPTION => true
    );

    $arrIt = new Xapp_ArrayIterator($arr);

    print('1. array iterator loop test ');
    while ($arrIt->valid())
    {
        $current = $arrIt->current();
        var_dump($current);
        $arrIt->next();
    }

    print('2. array iterator access begin() - test');
    var_dump($arrIt->begin());

    print('3. array iterator access end() - test');
    var_dump($arrIt->end());

    print('4. array key get access - test');
    var_dump($arrIt->get('DEBUG_MODE'));

    print('5. array key set access - test');
    var_dump($arrIt->set('DEBUG_MODE',true));

    print('6. array index access - test');
    var_dump($arrIt->at(0));

    print('7. array indexOf - test');
    var_dump($arrIt->indexOf('AUTOLOAD'));






    /*
    for ($it = $objIt->begin(); $it!=$objIt->end();$it = $objIt->next()){
        error_log('step');
        //var_dump($objIt->current());
    }
    */

    //var_dump($objIt->end());
    /*
    while($objIt->next()){
        //error_log('')
    }
    */
    /*
    error_log('has ' . $objIt->has('UUID'));
    //var_dump($objIt->has('UUID'));
    //var_dump($objIt->at('UUID'));

    var_dump($objIt->begin());
    var_dump($objIt->next());
    var_dump($objIt->next());
    var_dump($objIt->next());
*/



    //$objIt->dump();






    //XAPP_CONF_PROFILER_MODE=>XAPP_BASEDIR .'/cache/'

    /*
    Xapp::run($conf);

    include(XAPP_BASEDIR . "conf.inc.php");//conf data

    xapp_import('xapp.Rpc.*');
    xapp_import('xapp.Log.*');
    //xapp_import('xapp.Cache.*');


    $opt = array
    (
        Xapp_Rpc_Server::ALLOW_FUNCTIONS => true,
        Xapp_Rpc_Server::APPLICATION_ERROR => false,
        Xapp_Rpc_Server::DEBUG => true
    );


    $server = Xapp_Rpc::server('json', $opt);
    $server->register('templatedQuery');
    $server->register('login');
    $opt = array
    (

    );
    $gateway = Xapp_Rpc_Gateway::create($server, $opt);
    $gateway->run();

    */

}
catch(Exception $e)
{
    Xapp_Rpc_Server_Json::dump($e);
}

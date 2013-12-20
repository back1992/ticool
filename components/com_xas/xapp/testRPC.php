<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('magic_quotes_gpc', 'On');

define('XAPPED', true);

require_once dirname(__FILE__) . '/lib/rpc/lib/vendor/autoload.php';
require_once dirname(__FILE__) . '/lib/rpc/lib/vendor/xapp/Core/core.php';

$conf = array
(
    XAPP_CONF_DEBUG_MODE => false,
    XAPP_CONF_AUTOLOAD => true,
    XAPP_CONF_DEV_MODE => true,
    XAPP_CONF_HANDLE_BUFFER => true,
    XAPP_CONF_HANDLE_SHUTDOWN => true,
    XAPP_CONF_HTTP_GZIP => false,
    XAPP_CONF_CONSOLE => true,
    XAPP_CONF_HANDLE_ERROR => false,
    XAPP_CONF_HANDLE_EXCEPTION => false,
    XAPP_CONF_LOG_ERROR => false,
    XAPP_CONF_PROFILER_MODE => false
);

Xapp::run($conf);

class P{
    function _load(){}
}

class Plugin extends P
{
    function _load(){parent::_load();}
}

class VMart extends Plugin
{
    /**
     * init, concrete Joomla-Plugin class implementation
     *
     * @return void
     */
    private function init(){}

    /***
     *
     * @param $refId
     * @return array|null
     */
    public function getCategories($refId=0){
        return "[{l:2}]";
    }


    /***
     *
     * @return integer
     */
    function _load(){
        parent::load();
        //error_log('loading vmart');
        $vmartTestFile = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php';
        if(file_exists($vmartTestFile)){
            $this->_log('blah');
        }else{
            error_log('vmart plugin doesnt exist ' . $vmartTestFile);

            return false;
        }
        return true;
    }

    public function _log($message,$ns="",$stdError=true){
        parent::_log($message,"VMart",$stdError);
    }

    /**
     * init, concrete Joomla-Plugin class implementation
     *
     * @return void
     */
    function _setup(){
        parent::_setup();
    }
}

$class = new Vmart();

try
{

    $opt = array
    (
        Xapp_Rpc_Server::ALLOW_FUNCTIONS => true,
        Xapp_Rpc_Server::APPLICATION_ERROR => false,
        Xapp_Rpc_Server::METHOD_AS_SERVICE =>false,
        Xapp_Rpc_Server::DEBUG => true

    );
    $server = Xapp_Rpc::server('json', $opt);
    $server->register($class, array('_load'));

    $opt = array
    (
        Xapp_Rpc_Gateway::OMIT_ERROR => true
    );
    $gateway = Xapp_Rpc_Gateway::create($server, $opt);
    $gateway->run();
}
catch(Exception $e)
{
    print_r($e);
}
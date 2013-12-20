<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Rpc.Server.Exception');
xapp_import('xapp.Rpc.Fault');
xapp_import('xapp.Rpc.Server');
xapp_import('xapp.Rpc.Smd.Json');
xapp_import('xapp.Rpc.Request.Json');
xapp_import('xapp.Rpc.Response.Json');

/**
 * Rpc server json class
 *
 * @package Rpc
 * @subpackage Rpc_Server
 * @class Xapp_Rpc_Server_Json
 * @error 146
 * @author Frank Mueller <support@xapp-studio.com>
 */
class Xapp_Rpc_Server_Json extends Xapp_Rpc_Server
{
    /**
     * defines what rpc json server version to use
     *
     * @const VERSION
     */
    const VERSION                   = 'RPC_SERVER_JSON_VERSION';

    /**
     * defines if json server will output dojo compatible response at all times
     *
     * @const DOJO_COMPATIBLE
     */
    const DOJO_COMPATIBLE           = 'RPC_SERVER_JSON_DOJO_COMPATIBLE';


    /**
     * contains singleton instance of this class
     *
     * @var null|Xapp_Rpc_Server
     */
    protected static $_instance = null;

    /**
     * contains the parameters from json request object defined by "params"
     *
     * @var null
     */
    protected $_params = null;

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::VERSION               => XAPP_TYPE_STRING,
        self::DOJO_COMPATIBLE       => XAPP_TYPE_BOOL
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::VERSION               => 1,
        self::DOJO_COMPATIBLE       => 1
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::DEBUG                 => false,
        self::ALLOW_FUNCTIONS       => false,
        self::VERSION               => '2.0',
        self::DOJO_COMPATIBLE       => false,
        self::OMIT_ERROR            => false,
        self::METHOD_AS_SERVICE     => false,
        self::APPLICATION_ERROR     => false
    );

    /**
     * fault map defaults to common error faults common to json and xml
     * can be extended and remapped in concrete server to match fault
     * numbers defined in specifications. all faults go through getFault method
     * which will check for fault map and code mapping will otherwise return code
     * unmapped
     *
     * @var array
     */
    public $faultMap = array
    (
        -32500 => -32000,
        -32600 => -32600,
        -32601 => -32601,
        -32602 => -32602,
        -32603 => -32603,
        -32700 => -32700
    );


    /**
     * class constructor will set missing instances of smd, request and response and
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    public function __construct($options = null)
    {
        xapp_set_options($options, $this);
        if(!xapp_is_option(self::SMD, $this))
        {
            xapp_set_option(self::SMD, Xapp_Rpc_Smd_Json::instance(), $this);
        }
        if(!xapp_is_option(self::REQUEST, $this))
        {
            xapp_set_option(self::REQUEST, new Xapp_Rpc_Request_Json(), $this);
        }
        if(!xapp_is_option(self::RESPONSE, $this))
        {
            xapp_set_option(self::RESPONSE, new Xapp_Rpc_Response_Json(), $this);
        }
        parent::__construct();
    }


    /**
     * creates and returns singleton instance of class
     *
     * @error 14602
     * @param null|array|object $options expects optional options
     * @return Xapp_Rpc_Server_Json
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }


    /**
     * init server by checking for json object and method parameter or service $_GET parameter
     * to extract class and method or function to call from request. throw fault if server does
     * not allow functions just class methods. pass class options to response and store parameters
     * from json request object in param variable
     *
     * @error 14603
     * @return void
     * @throws Xapp_Rpc_Fault
     */
    protected function init()
    {
        $response = $this->response();

        if($this->request()->isParam('method'))
        {
            $method = $this->request()->getParam('method');
            if(strpos($method, '.') !== false)
            {
                $method = explode('.', trim($method));
                $this->_class = $method[0];
                $this->_function = $method[1];
                $this->_service = $this->_class . '.' . $this->_function;
            }else if($this->service() !== null){
                $this->_class = $this->service();
                $this->_function = trim($method);
                $this->_service = $this->_class . '.' . $this->_function;
            }else{
                if(xapp_get_option(self::ALLOW_FUNCTIONS, $this))
                {
                    $this->_function = trim($method);
                    $this->_service = $this->_function;
                }else{
                    Xapp_Rpc_Fault::t("functions as method are not supported by server", 1460301, -32012);
                }
            }
        }
        else if($this->service() !== null)
        {
            $service = $this->service();
            if(strpos($service, '.') !== false)
            {
                $service = explode('.', trim($this->service()));
                $this->_class = $service[0];
                $this->_function = $service[1];
            }else{
                $this->_function = trim($service);
            }
        }
        if($this->request()->isParam('params'))
        {
            $this->_params = $this->request()->getParam('params');
        }
        if(xapp_has_option(self::DOJO_COMPATIBLE, $this))
        {
            xapp_set_option(Xapp_Rpc_Response_Json::DOJO_COMPATIBLE, xapp_get_option(self::DOJO_COMPATIBLE, $this), $response);
        }
    }


    /**
     * validate json request object testing all request object parameters for validity. also checking all additional
     * parameters for validity and throwing fault if necessary
     *
     * @error 14604
     * @return void
     * @throws Xapp_Rpc_Fault
     */
    protected function validate()
    {
        error_log('validate serv');
        if($this->request()->isPost())
        {
            if($this->request()->getRaw() === "")
            {
                Xapp_Rpc_Fault::t("empty or invalid request object", array(1460401, -32600));
            }
            if($this->request()->getVersion() !== xapp_get_option(self::VERSION, $this))
            {
                Xapp_Rpc_Fault::t("rpc version miss match", array(1460402, -32013));
            }
            if($this->request()->isParam('method') && !is_string($this->request()->getParam('method')))
            {
                Xapp_Rpc_Fault::t("method must be a string value", array(1460403, -32014));
            }
            if($this->request()->isParam('method') && !$this->smd()->has($this->getClass(), $this->getFunction()))
            {
                Xapp_Rpc_Fault::t("method or function is not registered as service", array(1460404, -32601));
            }
            if(!$this->request()->isParam('id'))
            {
                Xapp_Rpc_Fault::t("id must be set", array(1460405, -32015));
            }
            if(!is_numeric($this->request()->getParam('id')) &&  !is_string($this->request()->getParam('id')))
            {
                Xapp_Rpc_Fault::t("id must be string or integer", array(1460406, -32016));
            }
            if($this->getClass() !== null)
            {
                $method = $this->smd()->get($this->getClass() . '.' . $this->getFunction());
            }else{
                $method = $this->smd()->get($this->getFunction());
            }
            if(!empty($method->parameters))
            {
                $params = $this->request()->getParam('params');
                $_params = (array)$params;

                //unnamed parameters key variable var
                if(is_null($params) || array_values($_params) !== $_params)
                {
                    $key = 'n';
                //named parameters key variable var
                }else{
                    $key = 'i';
                }
                $i = 0;
                foreach($method->parameters as $k => $v)
                {
                    $n = $v->name;
                    if(!$v->optional && (!array_key_exists($$key, $_params) || !xapp_is_value($_params[$$key])))
                    {
                        Xapp_Rpc_Fault::t(xapp_sprintf("param: %s must be set", array($$key)), array(1460407, -32602));
                    }
                    if(isset($v->type) && array_key_exists($$key, $_params) && !in_array(xapp_type($_params[$$key]), (array)$v->type))
                    {
                        Xapp_Rpc_Fault::t(xapp_sprintf("param: %s must be of the following types: %s", array($$key, implode('|', (array)$v->type))), array(1460408, -32602));
                    }
                    $i++;
                }
            }
            if(xapp_is_option(self::ADDITIONAL_PARAMETERS, $this))
            {
                foreach(xapp_get_option(self::ADDITIONAL_PARAMETERS, $this) as $k => $v)
                {
                    $type = (isset($v[0])) ? (array)$v[0] : false;
                    $optional = (isset($v[1])) ? (bool)$v[1] : true;

                    if(!$optional && !$this->request()->hasParam($k))
                    {
                        Xapp_Rpc_Fault::t(xapp_sprintf("additional param: %s must be set", array($k)), array(1460409, -32602));
                    }
                    if($type && !in_array(xapp_type($this->request()->getParam($k)), $type))
                    {
                        Xapp_Rpc_Fault::t(xapp_sprintf("additional param: %s must be of the following types: %s", array($k, implode('|', $type))), array(1460410, -32602));
                    }
                }
            }
        }
    }


    /**
     * executing requested service if found passing result from service invoking to response
     * or pass compile smd map to response if rpc server was invoked via GET
     *
     * @error 14605
     * @return void
     */
    protected function execute()
    {
        if($this->request()->isGet())
        {
            $this->response()->body($this->smd()->compile());
        }else{
            $result = $this->invoke($this->getFunction(), $this->getClass(), $this->_params);
            $this->response()->setVersion($this->request()->getVersion());
            $this->response()->set('result', $result);
            if(version_compare((string)xapp_get_option(self::VERSION), '2.0', '<'))
            {
                $this->response()->set('error', null);
            }
            $this->response()->set('id', $this->request()->get('id'));
            $this->response()->body($this->response()->data());
        }
    }


    /**
     * flush response
     *
     * @error 14606
     * @return mixed|void
     */
    protected function flush()
    {
       $this->response()->flush();
    }


    /**
     * shutdown server
     *
     * @error 14607
     * @return mixed|void
     */
    protected function shutdown()
    {

    }


    /**
     * handle exception by constructing json error object, setting it to response and instantly
     * flushing the error to output. if omit option is found in global rpc array the error message
     * will be omitted
     *
     * @error 14608
     * @param Exception $error expects instance of Exception
     * @return void
     */
    public function error(Exception $error)
    {
        $this->response()->setVersion(xapp_get_option(self::VERSION, $this));
        $e = array();
        if($error instanceof Xapp_Rpc_Fault)
        {
            $e['code'] = $this->getFault($error->getCode());
        }else{
            $e['code'] = $error->getCode();
        }
        if(isset($GLOBALS['_RPC']) && isset($GLOBALS['_RPC']['OMIT_ERROR']) && (bool)$GLOBALS['_RPC']['OMIT_ERROR'])
        {
            $e['message'] = null;
        }else{
            $e['message'] = $error->getMessage();
            if(($error instanceof Xapp_Rpc_Fault) && $error->hasData())
            {
                $e['data'] = $error->getData();
            }
        }
        $this->response()->set('error', $e);
        $this->response()->set('id', $this->request()->get('id'));
        $this->response()->body($this->response()->data());
        $this->response()->flush();
    }
}
<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Rpc.Server.Exception');
xapp_import('xapp.Rpc.Fault');
xapp_import('xapp.Rpc.Server.Json');
xapp_import('xapp.Rpc.Smd.Jsonp');
xapp_import('xapp.Rpc.Response.Json');
xapp_import('xapp.Rpc.Request.Json');

/**
 * Rpc server jsonp class
 *
 * @package Rpc
 * @subpackage Rpc_Server
 * @class Xapp_Rpc_Server_Jsonp
 * @error 147
 * @author Frank Mueller <support@xapp-studio.com>
 */
class Xapp_Rpc_Server_Jsonp extends Xapp_Rpc_Server_Json
{
    /**
     * option can contain jsonp callback parameter name
     *
     * @const CALLBACK
     */
    const CALLBACK                  = 'RPC_SERVER_JSONP_CALLBACK';


    /**
     * contains singleton instance of class
     *
     * @var null|Xapp_Rpc_Server_Jsonp
     */
    protected static $_instance = null;

    /**
     * contains jsonp callback parameter name if set
     *
     * @var null|string
     */
    protected $_callback = null;

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::CALLBACK              => XAPP_TYPE_STRING
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::CALLBACK              => 1
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
        self::DOJO_COMPATIBLE       => true,
        self::OMIT_ERROR            => false,
        self::METHOD_AS_SERVICE     => true,
        self::APPLICATION_ERROR     => false,
        self::CALLBACK              => 'callback'
    );


    /**
     * class constructor sets class options if smd instance is not set in class options
     * will create smd instance with appropriate options. copies callback string if set
     * in options to callback property. calls parent constructor to initialize instance
     *
     * @error 14701
     * @param null|array|object $options expects optional options
     */
    public function __construct($options = null)
    {
        xapp_set_options($options, $this);
        if(!xapp_is_option(self::SMD, $this))
        {
            $opt = array
            (
                Xapp_Rpc_Smd_Jsonp::TRANSPORT => 'JSONP',
                Xapp_Rpc_Smd_Jsonp::ENVELOPE => 'URL',
                Xapp_Rpc_Smd_Jsonp::RELATIVE_TARGETS => false,
                Xapp_Rpc_Smd_Jsonp::CALLBACK => xapp_get_option(self::CALLBACK, $this)
            );
            xapp_set_option(self::SMD, Xapp_Rpc_Smd_Jsonp::instance($opt), $this);
        }
        if(xapp_is_option(self::CALLBACK))
        {
            $this->callback(xapp_get_option(self::CALLBACK), $this);
        }
        parent::__construct();
    }


    /**
     * creates and returns singleton instance of class
     *
     * @error 14702
     * @param null|array|object $options expects optional options
     * @return Xapp_Rpc_Server_Jsonp
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
     * setter/getter for jsonp callback if used. if first parameter callback is set sets
     * callback string returns callback value
     *
     * @error 14703
     * @param null|string $callback expects callback string when setting
     * @return null|string
     */
    public function callback($callback = null)
    {
        if($callback !== null)
        {
            $this->_callback = trim((string)$callback);
        }
        return $this->_callback;
    }


    /**
     * init server by extracting class and function/method value from service parameter found
     * in $_GET parameter. the service must be always contained in service parameter and can also
     * be set by htaccess rewrite rule resolving the url path to service $_GET parameter like:
     * http://foo.com/gateway/class/method.. resulting in service $_GET parameter service=class/method
     * which can be resolved by server to method/function and class. will remove all additional parameter
     * from get array so params array only contains parameter needed for method or function call
     *
     * @error 14704
     * @return void
     * @throws Xapp_Rpc_Fault
     */
    protected function init()
    {
        $response = $this->response();
        $service = $this->service();
        $get = $this->request()->getGet();

        if(xapp_has_option(self::DOJO_COMPATIBLE, $this))
        {
            xapp_set_option(Xapp_Rpc_Response_Json::DOJO_COMPATIBLE, xapp_get_option(self::DOJO_COMPATIBLE, $this), $response);
        }
        if($service !== null)
        {
            if(strpos($service, '/') !== false || strpos($service, '.') !== false)
            {
                $service = explode('.', str_replace(array('/'), '.', trim($service)));
                $this->_class = $service[0];
                $this->_function = $service[1];
            }else{
                if(xapp_get_option(self::ALLOW_FUNCTIONS, $this))
                {
                    $this->_function = trim($service);
                }else{
                    Xapp_Rpc_Fault::t("functions are not supported by server", array(1470401, -32012));
                }
            }
        }
        if(!empty($get))
        {
            if(isset($get['service']))
            {
                unset($get['service']);
            }
            if($this->callback() !== null)
            {
                if(isset($get[$this->callback()]))
                {
                    unset($get[$this->callback()]);
                }
            }
            if(xapp_is_option(self::ADDITIONAL_PARAMETERS, $this))
            {
                foreach(xapp_get_option(self::ADDITIONAL_PARAMETERS, $this) as $k => $v)
                {
                    if(isset($get[$k])) unset($get[$k]);
                }
            }
            $this->_params = $get;
        }
    }


    /**
     * validate jsonp request testing for request parameter to be valid and checking all
     * additional parameters
     *
     * @error 14705
     * @return void
     * @throws Xapp_Rpc_Fault
     */
    protected function validate()
    {
        $get = $this->request()->getGet();
        $service = $this->service();
        $method = array();

        if($this->smd()->has($this->getClass(), $this->getFunction()))
        {
            if($this->getClass() !== null)
            {
                $method = $this->smd()->get($this->getClass() . '.' . $this->getFunction());
            }else{
                $method = $this->smd()->get($this->getFunction());
            }
        }
        if($service !== null && empty($method))
        {
            Xapp_Rpc_Fault::t("method or function is not registered as service", array(1470501, -32601));
        }
        if(!empty($method) && empty($get))
        {
            Xapp_Rpc_Fault::t("empty request parameter", array(1470502, -32600));
        }
        if(!empty($method) && !empty($method->parameters))
        {
            foreach($method->parameters as $k => $v)
            {
                if(!$v->optional && !array_key_exists($v->name, $get))
                {
                    Xapp_Rpc_Fault::t(xapp_sprintf("param: %s must be set", array($v->name)), array(1470503, -32602));
                }
                if(isset($v->type) && array_key_exists($v->name, $get) && !in_array(xapp_type($get[$v->name], true), (array)$v->type))
                {
                    Xapp_Rpc_Fault::t(xapp_sprintf("param: %s must be of the following types: %s", array($v->name, implode('|', (array)$v->type))), array(1470504, -32602));
                }
            }
        }
        if(xapp_is_option(self::ADDITIONAL_PARAMETERS, $this))
        {
            foreach(xapp_get_option(self::ADDITIONAL_PARAMETERS, $this) as $k => $v)
            {
                $type = (isset($v[0])) ? (array)$v[0] : false;
                $optional = (isset($v[1])) ? (bool)$v[1] : true;

                if(!$optional && !array_key_exists($k, $get))
                {
                    Xapp_Rpc_Fault::t(xapp_sprintf("additional param: %s must be set", array($k)), array(1470505, -32602));
                }
                if($type && !in_array(xapp_type($get[$k], true), $type))
                {
                    Xapp_Rpc_Fault::t(xapp_sprintf("additional param: %s must be of the following types: %s", array($k, implode('|', $type))), array(1470506, -32602));
                }
            }
        }
    }


    /**
     * executing requested service if found passing result from service invoking to response
     * or pass compile smd map to response if no service was called. if a callback was supplied
     * will wrap result into callback function
     *
     * @error 14706
     * @return void
     */
    protected function execute()
    {
        $get = $this->request()->getGet();

        if($this->service() !== null)
        {
            $result = $this->invoke($this->getFunction(), $this->getClass(), $this->_params);
            if($this->callback() !== null && array_key_exists($this->callback(), $get))
            {
                if(!is_array($result))
                {
                    $result = $get[$this->callback()] . '([' . $this->response()->encode($result) . '])';
                }else{
                    $result = $get[$this->callback()] . '(' . $this->response()->encode($result) . ')';
                }
            }else{
                $result = $this->response()->encode($result);
            }
            $this->response()->body($result);
        }else{
            $this->response()->body($this->smd()->compile());
        }
    }
}
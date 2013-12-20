<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Rpc');
xapp_import('xapp.Rpc.Smd');
xapp_import('xapp.Rpc.Server.Exception');
xapp_import('xapp.Rpc.Request');
xapp_import('xapp.Rpc.Response');
xapp_import('xapp.Rpc.Fault');

/**
 * Rpc server class
 *
 * @package Rpc
 * @class Xapp_Rpc_Server
 * @error 142
 * @author Frank Mueller <support@xapp-studio.com>
 */
abstract class Xapp_Rpc_Server extends Xapp_Rpc implements Xapp_Singleton_Interface
{
    /**
     * contains request instance
     *
     * @const REQUEST
     */
    const REQUEST               = 'RPC_SERVER_REQUEST';

    /**
     * contains response instance
     *
     * @const RESPONSE
     */
    const RESPONSE              = 'RPC_SERVER_RESPONSE';

    /**
     * contains smd service mapping instance
     *
     * @const SMD
     */
    const SMD                   = 'RPC_SERVER_SMD';

    /**
     * tells the server to allow debugging by a) returning full application
     * error details to client
     *
     * @const DEBUG
     */
    const DEBUG                 = 'RPC_SERVER_DEBUG';

    /**
     * tells server if to allow function or only class methods
     *
     * @const ALLOW_FUNCTIONS
     */
    const ALLOW_FUNCTIONS       = 'RPC_SERVER_ALLOW_FUNCTIONS';

    /**
     * set additional parameters that are not part of the service function or method
     * but part of the service itself, like session, signature, etc. the additional
     * parameter must be passed as an array where each parameter is an array also:
     *
     * <code>
     *  ADDITIONAL_PARAMETERS => array('session' => array('string', false), ...)
     * </code>
     *
     * the key can be the parameter name or in case of unnamed parameter the array index.
     * the array itself must contain as first value the data type of parameter as string or
     * multiple as array and boolean value if optional or not as second value. additional
     * parameters can also be set with addParam function
     *
     * @const ADDITIONAL_PARAMETERS
     */
    const ADDITIONAL_PARAMETERS = 'RPC_SERVER_ADDITIONAL_PARAMETERS';

    /**
     * boolean value defining whether to omit error message in response showing
     * only error code or not
     *
     * @const OMIT_ERROR
     */
    const OMIT_ERROR            = 'RPC_SERVER_OMIT_ERROR';

    /**
     * set if method will be identified by "service" get parameter or within
     * request post/get parameter method, e.g. for json as method "class.method". this value is
     * important for smd mapping and will be passed to smd instance. the server
     * class will try to look for service parameter in any case to find class,
     * method or function
     *
     * @const METHOD_AS_SERVICE
     */
    const METHOD_AS_SERVICE      = 'RPC_SERVER_METHOD_AS_SERVICE';

    /**
     * define whether to return exceptions as are from within application returning
     * all error messages readable or summarize application error as one containing
     * only the application error code. use this option to make sure that all sensitive
     * error messages from your application are not readable - just the code for reference
     *
     * @const APPLICATION_ERROR
     */
    const APPLICATION_ERROR     = 'RPC_SERVER_APPLICATION_ERROR';

    /**
     * defines if server is used in rewrite mode and contains the rewrite pattern so that
     * smd can resolve the right target. see smd constant and function getTarget for more
     *
     * @see Xapp_Rpc_Smd::getTarget
     * @const REWRITE_URL
     */
    const REWRITE_URL           = 'RPC_SERVER_REWRITE_URL';


    /**
     * contains the request service string if present in request. the service
     * string can contain dot like notation class.method or function
     *
     * @var null|string
     */
    protected $_service = null;

    /**
     * contains class if service requested a method of a class
     *
     * @var null|string
     */
    protected $_class = null;

    /**
     * contains class method if requesting a method of a class or function name
     *
     * @var null|string
     */
    protected $_function = null;

    /**
     * contains instances of classes that are registered instead of class names as string
     *
     * @var array
     */
    protected $_objects = array();

    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::REQUEST               => 'Xapp_Rpc_Request',
        self::RESPONSE              => 'Xapp_Rpc_Response',
        self::SMD                   => 'Xapp_Rpc_Smd',
        self::DEBUG                 => XAPP_TYPE_BOOL,
        self::ALLOW_FUNCTIONS       => XAPP_TYPE_BOOL,
        self::ADDITIONAL_PARAMETERS => XAPP_TYPE_ARRAY,
        self::OMIT_ERROR            => XAPP_TYPE_BOOL,
        self::METHOD_AS_SERVICE     => XAPP_TYPE_BOOL,
        self::APPLICATION_ERROR     => XAPP_TYPE_BOOL,
        self::REWRITE_URL           => array(XAPP_TYPE_BOOL, XAPP_TYPE_STRING)
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::REQUEST               => 0,
        self::RESPONSE              => 0,
        self::SMD                   => 0,
        self::DEBUG                 => 1,
        self::ALLOW_FUNCTIONS       => 1,
        self::ADDITIONAL_PARAMETERS => 0,
        self::OMIT_ERROR            => 1,
        self::METHOD_AS_SERVICE     => 1,
        self::APPLICATION_ERROR     => 1,
        self::REWRITE_URL           => 0
    );


    /**
     * init server
     *
     * @return mixed
     */
    abstract protected function init();

    /**
     * validate request
     *
     * @return mixed
     */
    abstract protected function validate();

    /**
     * execute request
     *
     * @return mixed
     */
    abstract protected function execute();

    /**
     * flush result
     *
     * @return mixed
     */
    abstract protected function flush();

    /**
     * shutdown server
     *
     * @return mixed
     */
    abstract protected function shutdown();

    /**
     * dump error
     *
     * @param Exception $error
     * @return mixed
     */
    abstract public function error(Exception $error);


    /**
     * class constructor that must be called from child class to initialize
     * options and pass options to smd instance. always make sure the concrete
     * server calls parent::__construct() in constructor
     *
     * @error 14201
     * @throw Xapp_Rpc_Server_Exception
     */
    protected function __construct()
    {
        $smd = $this->smd();

        if(xapp_is_option(self::ADDITIONAL_PARAMETERS, $this))
        {
            $params = xapp_get_option(self::ADDITIONAL_PARAMETERS, $this);
            foreach($params as $k => &$v)
            {
                if(is_array($v) && isset($v[0]) && !empty($v[0]))
                {
                    $v[0] = Xapp_Rpc_Smd::mapType($v[0]);
                }else{
                    throw new Xapp_Rpc_Server_Exception("additional parameter: $k must have a valid data type value", 1420101);
                }
            }
            xapp_reset_option(self::ADDITIONAL_PARAMETERS, $params, $this);

        }
        if($smd !== null && xapp_is_option(self::ADDITIONAL_PARAMETERS, $this))
        {
            xapp_set_option(Xapp_Rpc_Smd::ADDITIONAL_PARAMETERS, xapp_get_option(self::ADDITIONAL_PARAMETERS, $this), $smd);
        }
        if($smd !== null && xapp_is_option(self::METHOD_AS_SERVICE, $this))
        {
            xapp_set_option(Xapp_Rpc_Smd::METHOD_AS_SERVICE, xapp_get_option(self::METHOD_AS_SERVICE, $this), $smd);
        }
        if($smd !== null && xapp_is_option(self::REWRITE_URL, $this))
        {
            xapp_set_option(Xapp_Rpc_Smd::REWRITE_URL, xapp_get_option(self::REWRITE_URL, $this), $smd);
        }
    }


    /**
     * setup server instance by setting variables, calling pre execute handler and initializing concrete
     * server implementation
     *
     * @error 14218
     * @return void
     */
    final public function setup()
    {
        if(xapp_is_option(self::OMIT_ERROR, $this))
        {
            $this->request()->set('OMIT_ERROR', true, 'RPC');
        }
        if($this->request()->is('service', 'GET'))
        {
            $service = $this->request()->get('service', 'GET');
            $service = str_replace(array('/'), '.', trim($service, '/. '));
            $this->_service = $service;
        }
        $this->preHandle();
        $this->init();
    }


    /**
     * factory class creates singleton instance of server implementation
     * defined by first parameter driver
     *
     * @error 14202
     * @param string $driver expects the server type, e.g. "json" to instantiate json server
     * @param null|array|object $options expects optional options array
     * @return Xapp_Rpc_Server
     * @throws Xapp_Rpc_Server_Exception
     */
    public static function factory($driver, $options = null)
    {
        $class = __CLASS__ . '_' . ucfirst(strtolower(trim($driver)));
        if(class_exists($class, true))
        {
            return $class::instance($options);
        }else{
            throw new Xapp_Rpc_Server_Exception("rpc server class: $class not found", 1420201);
        }
    }


    /**
     * register service to server. a service can by anything of the following:
     * - function (a user php function defined and existing)
     * - class (a user class)
     * - class.method (a single method of a class ignoring all others)
     * - directory (a directory too look for classes in)
     *
     * the smd mapper will try to find all the above values to map. the most
     * clean way is to always use classes and omit certain methods using the
     * second parameter which can contain an array of class methods to ignore
     *
     * @error 14203
     * @param null|string $mixed expects any of the above values
     * @param array $ignore expects optional ignore method array
     * @return Xapp_Rpc_Server
     */
    public function register($mixed = null, Array $ignore = array())
    {
        if(is_object($mixed))
        {
            $this->_objects[strtolower(get_class($mixed))] = $mixed;
        }
        $this->smd()->set($mixed, $ignore);
        return $this;
    }


    /**
     * unregister service class from smd map. only classes can be unregistered
     * individually - pass class name as object, class name string or "class.method"
     * dot notation. unregister everything if you leave first parameter to null. NOTE:
     * calling this function like $server->unregister() will unregister all services!
     *
     * @error 14217
     * @param null|string|object $class expects class as explained above
     * @return Xapp_Rpc_Server
     */
    public function unregister($class = null)
    {
        if(is_object($class))
        {
            $name = strtolower(get_class($class));
        }else{
            $name = $class;
        }
        if(array_key_exists($name, $this->_objects))
        {
            unset($this->_objects[strtolower(get_class($class))]);
        }
        $this->smd()->reset($class);
        return $this;
    }


    /**
     * add additional parameter instead of using class option directly set parameters
     * with this function. the parameter will be directly passed to the smd instance
     * if already set. the first parameter \$name can be either the name of additional
     * parameter or null which will create unnamed additional parameter with index starting
     * with 0. NOTE: use this function only after smd instance has been set, it is advised
     * to set additional parameters via class options
     *
     * @error 14204
     * @param null|string|integer $name expects optional name
     * @param string|array $type expects data type as string or array
     * @param bool $optional expects boolean value whether parameter is optional or not
     * @return Xapp_Rpc_Server
     */
    public function addParam($name = null, $type, $optional = false)
    {
        $smd = $this->smd();

        if($name !== null)
        {
            $param = array($name => array($type, $optional));
        }else{
            $param = array(array($type, $optional));
        }

        xapp_set_option(self::ADDITIONAL_PARAMETERS, $param, $this);
        if($smd !== null)
        {
            xapp_set_option(Xapp_Rpc_Smd::ADDITIONAL_PARAMETERS, $param, $smd);
        }
        return $this;
    }


    /**
     * setter/getter for smd instance. if you set smd instance not via class options
     * but at later stage you must make sure that smd instance has all options set required
     * to work with server instance since server instance passes options to smd instance
     * in class constructors
     *
     * @error 14205
     * @param Xapp_Rpc_Smd $smd expects smd instance when to set instance
     * @return null|Xapp_Rpc_Smd
     */
    public function smd(Xapp_Rpc_Smd $smd = null)
    {
        if($smd !== null)
        {
            xapp_set_option(self::SMD, $smd, $this);
        }
        return xapp_get_option(self::SMD, $this);
    }


    /**
     * setter/getter for request instance. if you set request instance not via class options
     * but at later stage you must make sure that request instance has all options set required
     * to work with server instance since server instance passes options to request instance
     * in class constructors
     *
     * @error 14206
     * @param Xapp_Rpc_Request $request expects request instance when to set instance
     * @return null|Xapp_Rpc_Request
     */
    public function request(Xapp_Rpc_Request $request = null)
    {
        if($request !== null)
        {
            xapp_set_option(self::REQUEST, $request, $this);
        }
        return xapp_get_option(self::REQUEST, $this);
    }


    /**
     * setter/getter for response instance. if you set response instance not via class options
     * but at later stage you must make sure that response instance has all options set required
     * to work with server instance since server instance passes options to response instance
     * in class constructors
     *
     * @error 14207
     * @param Xapp_Rpc_Response $response expects request instance when to set instance
     * @return null|Xapp_Rpc_Response
     */
    public function response(Xapp_Rpc_Response $response = null)
    {
        if($response !== null)
        {
            xapp_set_option(self::RESPONSE, $response, $this);
        }
        return xapp_get_option(self::RESPONSE, $this);
    }


    /**
     * setter/getter for service parameter. set only manual if service parameter can not
     * be accessed via $_GET. returns the service name if set
     *
     * @error 14208
     * @param null|string $service expects the service string when setting
     * @return null|string
     */
    public function service($service = null)
    {
        if($service !== null)
        {
            $this->_service = trim((string)$service, '/. ');
        }
        return $this->_service;
    }


    /**
     * get class of requested service if class->method is used in request
     *
     * @error 14209
     * @return null|string
     */
    public function getClass()
    {
        return $this->_class;
    }


    /**
     * get function/method of requested service. if class is requested will
     * contain method name if not function name
     *
     * @error 14210
     * @return null|string
     */
    public function getFunction()
    {
        return $this->_function;
    }


    /**
     * invoke class method or function from request object returning result or throwing error if executing
     * callable failed. throws exception with extended exception properties if in debug mode. summarizes
     * all caught applications exceptions when invoking callable to a general application error so sensitive
     * error messages can be omitted. when invoked with named parameters will reorder parameters to reflect
     * order of method/function call since call_user_func_array needs to pass parameter named or unnamed in
     * correct order
     *
     * @error 14211
     * @param string $function expects the function name or in case of classes the method name
     * @param null|string $class expects optional class name
     * @param null|string|array $params expects optional parameter to pass to function
     * @return mixed
     * @throws Xapp_Rpc_Server_Exception
     * @throws Xapp_Rpc_Fault
     * @throws Exception
     */
    protected function invoke($function, $class = null, $params = null)
    {
        $reflection = null;
        $callable = null;
        $return = null;
        $key = null;

        try
        {
            if($class !== null)
            {
                $key = "$class.$function";
                try
                {
                    if(array_key_exists(strtolower($class), $this->_objects))
                    {
                        $reflection = new ReflectionClass($this->_objects[strtolower($class)]);
                    }else{
                        $reflection = new ReflectionClass($class);
                    }
                    if($reflection->hasMethod($function))
                    {
                        $method = $reflection->getMethod($function);
                        if($method->isPublic())
                        {
                            if($method->isStatic())
                            {
                                $callable = array($reflection->getName(), $function);
                            }else{
                                $callable = array(((array_key_exists(strtolower($class), $this->_objects)) ? $this->_objects[strtolower($class)] : $reflection->newInstance()), $function);
                            }
                        }else{
                            Xapp_Rpc_Fault::t("method: $function of class: $class is not public", array(1421105, -32601));
                        }
                    }else{
                        Xapp_Rpc_Fault::t("method: $function of class: $class does not exist", array(1421104, -32601));
                    }
                }
                catch(ReflectionException $e)
                {
                    throw new Xapp_Rpc_Server_Exception(xapp_sprintf("unable to initialize class due to reflection error: %d - %s", array($e->getCode(), $e->getMessage())), 1421103);
                }
            }else{
                $key = $function;
                $callable = $function;
            }
            if(is_callable($callable))
            {
                if(!is_null($params) && array_values((array)$params) !== (array)$params)
                {
                    $tmp = array();
                    $arr = (array)$params;
                    $map = $this->smd()->get($key);
                    if(!is_null($map))
                    {
                        foreach((array)$map->parameters as $p)
                        {
                            if(array_key_exists($p->name, $arr))
                            {
                                $tmp[$p->name] = $arr[$p->name];
                            }
                        }
                    }
                    $params = $tmp;
                }
                xapp_event('xapp.rpc.server.invoke', array($function, $class, $params));
                if(is_array($callable) && is_object($callable[0]) && $reflection->implementsInterface('Xapp_Rpc_Interface_Callable'))
                {
                    $callable[0]->onBeforeCall();
                }
                $return = call_user_func_array($callable, (array)$params);
                if(is_array($callable) && is_object($callable[0]) && $reflection->implementsInterface('Xapp_Rpc_Interface_Callable'))
                {
                    $callable[0]->onAfterCall();
                }
                $reflection = null;
                $callable = null;
                return $return;
            }else{
                throw new Xapp_Rpc_Server_Exception("unable to invoke function since function is not a callable", 1421102);
            }
        }
        catch(Exception $e)
        {
            if(!($e instanceof Xapp_Rpc_Server_Exception))
            {
                $data = array();
                if(xapp_is_option(self::DEBUG, $this))
                {
                    $data['message'] = $e->getMessage();
                    $data['code'] = $e->getCode();
                    if($e instanceof ErrorException)
                    {
                        $data['severity'] = $e->getSeverity();
                    }
                    $data['file'] = $e->getFile();
                    $data['line'] = $e->getLine();
                }
                if(xapp_is_option(self::APPLICATION_ERROR, $this))
                {
                    if(($code = (int)$e->getCode()) > 0)
                    {
                        Xapp_Rpc_Fault::t(xapp_sprintf("application error: %d", array($code)), array(1421101, -32500), XAPP_ERROR_IGNORE, $data);
                    }else{
                        Xapp_Rpc_Fault::t("application error", array(1421101, -32500), XAPP_ERROR_IGNORE, $data);
                    }
                }else{
                    if(xapp_is_option(self::DEBUG, $this))
                    {
                        Xapp_Rpc_Fault::t($e->getMessage(), array(1421101, $e->getCode()), XAPP_ERROR_IGNORE, $data);
                    }else{
                        throw $e;
                    }
                }
            }else{
                throw $e;
            }
        }
        return null;
    }


    /**
     * pre handle function gets called before the concrete server implementation
     * server handlers are called in succession
     *
     * @error 14212
     * @return void
     */
    protected function preHandle()
    {

    }


    /**
     * handles the request, executes service and flushes result to output stream.
     * nothing will happen unless this function is called! if the server was called
     * with GET and not GET parameters are set will flush smd directly
     *
     * @error 14213
     * @return void
     */
    final public function handle()
    {
        xapp_debug('rpc server handler started', 'rpc');
        xapp_event('xapp.rpc.server.handle', array(&$this));

        if($this->request()->isGet() && !$this->request()->hasParam())
        {
            $this->response()->body($this->smd()->compile());
            $this->response()->flush();
        }
        $this->validate();
        $this->execute();
        $this->flush();

        xapp_debug('rpc server handler stopped', 'rpc');
    }


    /**
     * post handle function gets called after the concrete server implementation
     * server handlers are called in succession
     *
     * @error 14214
     * @return void
     */
    protected function postHandle()
    {

    }


    /**
     * map rpc faults by looking if faultMap exists a property that contains a mapping of
     * generic rpc fault codes to concrete server implementation fault codes
     *
     * @error 14215
     * @param int $code expects the fault code to map
     * @return int
     */
    protected function getFault($code)
    {
        if(xapp_property_exists($this, 'faultMap') && array_key_exists((int)$code, $this->faultMap))
        {
            return (int)$this->faultMap[(int)$code];
        }else{
            return (int)$code;
        }
    }


    /**
     * server teardown method will shutdown concrete server implementation and call post
     * service handler after successful shutdown
     *
     * @error 14219
     * @return void
     */
    final public function teardown()
    {
        $this->shutdown();
        $this->postHandle();
    }


    /**
     * prevent cloning
     *
     * @error 14216
     * @return void
     */
    protected function __clone(){}


    /**
     * instead of calling handle method do echo $server to execute server
     *
     * @error 14217
     * @return void
     */
    public function __toString()
    {
        $this->handle();
    }
}
<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html

 */

/**
 * XApp-Connect-Plugin base class.
 *
 * The class provides the minimum functionality like caching, logging and
 * resolving of run time variables.
 *
 * Typically, a plugin should run as a singleton !
 *
 * Remarks :
 *  - A plugin should implement the XApp-Plugin interface :
 *      setup()
 *      load()
 *  - You can pass an existing cache and log driver within the SERVICE_CONF, otherwise
 *    the service configuration will be used to create those logging & cache instances!
 *  - The plugin base class equals the concept of the "fat" interface and therefore
 *    rather "mixes in" common functionality (Horizontal programming). This is similar to
 *    "Traits" in Php5.4
 *
 * @package XApp-Connect
 * @class Xapp_Connect_Plugin
 * @error @TODO
 * @author  mc007
 */
class Xapp_Connect_Plugin extends Xapp_Connect_Indexer implements Xapp_Singleton_Interface
{


    public $xcOptions = null;

    public $xcSchemas =null;

    public $xcRefId =null;

    public $xcType =null;

    public function _setVar($key,$value){

        if($this->xcOptions && $this->xcOptions->vars){

            $this->xcOptions->vars->{$key}=$value;
            /*
            if($this->xcOptions->vars->{$key} !=null){

            }
            */
        }
    }

    /**
     * option to specify a cache config
     *
     * @const CACHE_CONF
     */
    const CACHE_CONF         = 'XAPP_CACHE_CONF';

    /**
     * option to specify logging config
     *
     * @const LOGGING_CONF
     */
    const LOGGING_CONF         = 'XAPP_LOGGING_CONF';

    /**
     * option to specify service config
     *
     * @const SERVICE_CONF
     */
    const SERVICE_CONF         = 'XAPP_SERVICE_CONF';

    /***
     * internal logger or reference to external logger
     */
    var $logger=null;

    /***
     * service configuration shortcut.
     */
    var $serviceConfig=null;

    /***
     * internal cache instance
     */
    var $cache=null;

    /**
     * option to specify a cache namespace
     *
     * @const DEFAULT_NS
     */
    var $CACHE_NS         = 'XAPP_CONNECT_PLUGIN_CACHE_NS';

    /**
     * @TODO : a bitmask of subscribed callbacks. A plugin ideally subscribes
     * to common functions. A plugin manager is in progress.
     * The callback implementation should be done using the "Aspect" oriented
     * programming pattern.
     * @var int : bit mask| enumeration
     */
    var $pluginMask=-1;


    /**
     * options dictionary for this class containing all data type values
     *
     * @var array
     */
    public static $optionsDict = array
    (
        self::CACHE_CONF       => XAPP_TYPE_ARRAY,
        self::LOGGING_CONF     => XAPP_TYPE_ARRAY,
        self::SERVICE_CONF     => XAPP_TYPE_ARRAY
    );

    /**
     * options mandatory map for this class contains all mandatory values
     *
     * @var array
     */
    public static $optionsRule = array
    (
        self::CACHE_CONF               => 0,
        self::LOGGING_CONF       => 0,
        self::SERVICE_CONF       => 0
    );

    /**
     * options default value array containing all class option default values
     *
     * @var array
     */
    public $options = array
    (
        self::CACHE_CONF            => null,
        self::LOGGING_CONF          => null,
        self::SERVICE_CONF          => null
    );


    /**
     * contains the singleton instance for this class
     *
     * @var null|Xapp_Connect_Plugin
     */
    protected static $_instance = null;

    /**
     * Xapp_Singleton interface impl.
     *
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return Xapp_Connect_Plugin
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
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    public function __construct($options = null)
    {
        xapp_set_options($options, $this);
    }

    /***
     * @param $loggingConf
     * @param $serviceConf
     */
    private function setupLogging($loggingConf,$serviceConf){

        $writer =  new Xapp_Log_Writer_File(XAPP_BASEDIR .'/cache/');
        $logging_options = array(
            Xapp_Log::PATH  => XAPP_BASEDIR .'/cache/',
            Xapp_Log::EXTENSION  => 'log',
            Xapp_Log::NAME  => 'error',
            Xapp_Log::WRITER  => array($writer),
            Xapp_Log_Error::STACK_TRACE => true
        );
        $log = new Xapp_Log_Error($logging_options);
    }

    /***
     * @param $message
     * @param string $ns
     * @param bool $stdError
     */
    public function log($message,$ns="",$stdError=true){
        if($this->logger){
            $this->logger->log($ns."::".$message);
        }else{
        }
        if($stdError){
            error_log('Error : '.$message);
        }
    }

    private function init(){}

    /**
     * IPugin interface impl.
     *
     * setup() must be called before load
     *
     * @error 15404
     * @return integer Returns error code due to the initialization.
     */
    function setup(){

        //extract service configuration

        $this->serviceConfig = xapp_get_option(self::SERVICE_CONF,$this);

        //logging
        if(xapp_is_option(self::LOGGING_CONF, $this) && $this->serviceConfig){
            $logConfig = xapp_get_option(self::SERVICE_CONF);
            if($logConfig && $logConfig[XC_CONF_LOGGER]!=null){
                $this->logger=$logConfig[XC_CONF_LOGGER];
            }else{
                //setup logger
            }
        }


        //cache
        if(xapp_is_option(self::CACHE_CONF, $this) && $this->serviceConfig){
            $cacheConfig = xapp_get_option(self::CACHE_CONF);
            if($cacheConfig){
                $this->cache = Xapp_Cache::instance($this->CACHE_NS,"file",array(
                    Xapp_Cache_Driver_File::PATH=>xapp_get_option(XC_CONF_CACHE_PATH,$this->serviceConfig),
                    Xapp_Cache_Driver_File::CACHE_EXTENSION=>$this->CACHE_NS,
                    Xapp_Cache_Driver_File::DEFAULT_EXPIRATION=>200
                ));
            }
        }
    }
    private function setupFilter($filterClass,$inData){
        $filter = Xapp_Connect_Filter::factory($inData,$filterClass,$this->logger,$this->cache,$this->serviceConfig,$this->xcType,$this->xcOptions,$this->xcSchemas,$this);
        return $filter;
    }

    public function applyFilter($filterClass,$inData,$encode=true){
        $arg_list = func_get_args();

        //weird
        if(count($arg_list)==2){
            $arg_list[2]=true;
        }
        $filter = $this->setupFilter($filterClass,$inData);
        if($filter){
            return $filter->apply($inData,$arg_list[2]);
        }else{
            $this->log('couldnt create filter');
        }
        return $inData;
    }

    /**
     * IPugin interface impl.
     *
     * load() does plugin specific 3th party imports
     *
     * @error 15404
     * @return integer Returns error code due to the initialization.
     */
    function load(){}

}
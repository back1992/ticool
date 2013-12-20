<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html

*/

/**
 * Temporary solution to retrieve options which have been setup due to a RPC entry point from within a registered RPC class
 *
 * @package XApp-Connect
 * @class Configurator
 * @error @TODO
 * @author  mc007
 */
class Xapp_Connect_Configurator implements Xapp_Singleton_Interface
{
    /**
     * contains the singleton instance for this class
     *
     * @var null|Xapp_Connect_Configurator
     */
    protected static $_instance = null;

    /**
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return Xapp_Connect_Configurator
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
     *
     * @error 15401
     * @param null|mixed $options expects optional xapp option array or object
     */
    /*
    function __construct($options = null)
    {
        xapp_init_options($options, $this);
    }
    */
}
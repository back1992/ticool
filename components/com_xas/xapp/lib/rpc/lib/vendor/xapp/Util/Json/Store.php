<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Util.Json.Exception');
xapp_import('xapp.Util.Json');

/**
 * Util json store class
 *
 * @package Util
 * @subpackage Util_Json
 * @class Xapp_Util_Json_Store
 * @error 169
 * @author Frank Mueller <support@xapp-studio.com>
 */
class Xapp_Util_Json_Store extends Xapp_Util_Std_Store
{
    /**
     * class constructor for json store implementation. see Xapp_Util_Std_Store::__constructor for more details
     *
     * @error 16901
     * @see Xapp_Util_Std_Store::__constructor
     * @param null|mixed $mixed expects one of the above value options
     * @param null|mixed $options expects optional class instance options
     * @throws Xapp_Util_Json_Exception
     */
    public function __construct($mixed = null, $options = null)
    {
        xapp_init_options($options, $this);
        if($mixed !== null)
        {
            if(is_object($mixed))
            {
                $this->_object =& $mixed;
            }else if(is_array($mixed)){
                $this->_object = Xapp_Util_Json::convert($mixed);
            }else{
                if(is_file($mixed))
                {
                    $this->_file = $mixed;
                    if(($mixed = file_get_contents($mixed)) !== false)
                    {
                        $this->_object = Xapp_Util_Json::decode($mixed);
                    }else{
                        throw new Xapp_Util_Json_Exception("unable to read from file: $mixed", 1690101);
                    }
                }else{
                    if(is_string($mixed) && in_array(substr($mixed, 0, 1), array('{', '[')))
                    {
                        $this->_object = Xapp_Util_Json::decode($mixed);
                    }else if(is_string($mixed) && strpos($mixed, '.') !== false && is_writeable(dirname($mixed))){
                        $this->_file = $mixed;
                    }else{
                        throw new Xapp_Util_Json_Exception("passed first argument in constructor is not a valid object or file path", 1690102);
                    }
                }
            }
        }
    }


    /**
     * json implementation for decoding, see Xapp_Util_Std_Store::decode for more details
     *
     * @error 16902
     * @see Xapp_Util_Std_Store::decode
     * @param mixed $value expects the value to try to decode
     * @return mixed|string
     */
    public static function decode($value)
    {
        if(is_string($value))
        {
            if(substr(trim($value), 0, 1) === '{' || substr(trim($value), 0, 2) === '[{')
            {
                return Xapp_Util_Json::decode($value);
            }else if(preg_match('/^([adObis]\:|N\;)/', trim($value))){
                return unserialize($value);
            }
        }
        return $value;
    }


    /**
     * json implementation for encoding, see Xapp_Util_Std_Store::encode for more details
     *
     * @error 16903
     * @param mixed $value expects any value to encode
     * @return mixed|string
     */
    public static function encode($value)
    {
        if(is_object($value))
        {
            return Xapp_Util_Json::encode($value);
        }else if(is_array($value)){
            return serialize($value);
        }else{
            return $value;
        }
    }


    /**
     * dump/print stores json object to screen
     *
     * @error 16904
     * @return void
     */
    public function dump()
    {
        Xapp_Util_Json::dump($this->_object);
    }


    /**
     * json implementation of save method, see Xapp_Util_Std_Store::save for more details
     *
     * @error 16905
     * @param bool $pretty expects boolean value whether to store json string pretty or non pretty
     * @return bool|mixed|string
     * @throws Xapp_Util_Json_Exception
     */
    public function save($pretty = true)
    {
        $result = null;

        if($this->_file !== null)
        {
            if((bool)$pretty)
            {
                $result = file_put_contents($this->_file, Xapp_Util_Json::prettify(Xapp_Util_Json::encode($this->_object)), LOCK_EX);
            }else{
                $result = file_put_contents($this->_file, Xapp_Util_Json::encode($this->_object), LOCK_EX);
            }
            if($result !== false)
            {
                $result = null;
                return true;
            }else{
                throw new Xapp_Util_Json_Exception("unable to save to file: " . $this->_file, 1690501);
            }
        }else{
            return self::encode($this->_object);
        }
    }


    /**
     * json implementation of saveTo method, see Xapp_Util_Std_Store::saveTo for more details
     *
     * @error 16905
     * @param string $file expects absolute file path to store object at
     * @param bool $pretty expects boolean value whether to store json string pretty or non pretty
     * @return bool
     * @throws Xapp_Util_Json_Exception
     */
    public function saveTo($file, $pretty = true)
    {
        $result = null;

        if((bool)$pretty)
        {
            $result = file_put_contents($file, Xapp_Util_Json::prettify(Xapp_Util_Json::encode($this->_object)), LOCK_EX);
        }else{
            $result = file_put_contents($file, Xapp_Util_Json::encode($this->_object), LOCK_EX);
        }
        if($result !== false)
        {
            $result = null;
            return true;
        }else{
            throw new Xapp_Util_Json_Exception("unable to save to file: $file", 1690501);
        }
    }
}
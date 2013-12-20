<?php


// *****************************************************************
// global xapp helpers contain common functions that are used
// throughout the different xapp modules so the helpers must always
// be included but will be loaded automatically if core.php is
// included - no need to include this manually
// *****************************************************************


/**
 * checks if passed value is a class, that is instance of an object or
 * a string of class name that has been declared before or must be autoloaded
 * to be known. will return false if none of the latter is true
 *
 * @param null|object|string $class expects instance of object or class name as string
 * @return bool
 */
function xapp_is_class($class = null)
{
    if($class !== null)
    {
        if(is_object($class))
        {
            return true;
        }else{
            return (class_exists((string)$class)) ? true : false;
        }
    }
    return false;
}


/**
 * check if passed date is a valid date string that can be parsed by php functions
 * strtotime or in strict modus strptime. the latter will check against date format
 * defined in xapp config
 *
 * @param null|string $date expects date string to be checked for validity
 * @param bool $strict expects boolean value to check against xapp config date format
 * @return bool
 */
function xapp_is_date($date = null, $strict = false)
{
    if($date !== null)
    {
        if((bool)$strict)
        {
            return (bool)@strptime((string)$date, xapp_conf(XAPP_CONF_DATE_FORMAT));
        }else{
            return (bool)@strtotime((string)$date);
        }
    }
    return false;
}


/**
 * check datetime string if valid. see xapp_is_date for more explanations since
 * the this function does the same only with datetime values
 *
 * @param null|string $datetime expects datetime string to be checked for validity
 * @param bool $strict expects boolean value to check against xapp config datetime format
 * @return bool
 */
function xapp_is_datetime($datetime = null, $strict = false)
{
    if($datetime !== null)
    {
        if((bool)$strict)
        {
            return (bool)@strptime($datetime, xapp_conf(XAPP_CONF_DATETIME_FORMAT));
        }else{
            return (bool)@strtotime((string)$datetime);
        }
    }
    return false;
}


/**
 * check a timestamp value if is valid unix timestamp int value. if second parameter
 * exists will check also if timestamp is > time value passed in second parameter
 * which must be a valid php time() value = unix timestamp or true meaning now
 *
 * @param null|mixed $timestamp expects the timestamp to check as int or string
 * @param null|int|bool $time expects optional timestamp - see explanation above
 * @return bool
 */
function xapp_is_timestamp($timestamp = null, $time = null)
{
    $return = false;

    if($timestamp !== null)
    {
        if(is_int($timestamp))
        {
            $return = ((int)(string)$timestamp === $timestamp);
        }else{
            $return = ((string)(int)$timestamp === $timestamp);
        }
        $return = (($return) && ((int)$timestamp <= PHP_INT_MAX) && ((int)$timestamp >= ~PHP_INT_MAX) && (!strtotime($timestamp)));;
        if($return && $time !== null)
        {
            if($time === true) $time = time();
            return ((int)$timestamp >= (int)$time) ? true : false;
        }
    }
    return $return;
}


/**
 * check if a passed value is anything else but a null value, boolean false, or
 * empty array|string = all values which are not values or empty values returning false
 *
 * @param null|mixed $mixed expects value to check
 * @return bool
 */
function xapp_is_value($mixed = null)
{
    if(is_null($mixed))
    {
        return false;
    }
    if(is_bool($mixed) && $mixed === false)
    {
        return false;
    }
    if(is_array($mixed) && empty($mixed))
    {
        return false;
    }
    if(is_string($mixed) && $mixed === '')
    {
        return false;
    }
    return true;
}


/**
 * returns data type value as string of variable passed in first parameter.
 * if the second parameter is to true will convert string values that are
 * actually wrong casted into its proper data type - usually used on
 * database results or $_GET parameters e.g.
 *
 * @param null|mixed $value expects the variable to test
 * @param boolean $convert expects boolean value for converting string
 * @return null|string
 */
function xapp_type($value = null, $convert = false)
{
    if(is_string($value) && (bool)$convert)
    {
        if(is_numeric($value))
        {
            if((float)$value != (int)$value){
                $value = (float)$value;
            }else{
                $value = (int)$value;
            }
        }else{
            if($value === 'true' || $value === 'false')
            {
                $value = (bool)$value;
            }
        }
    }

    if(is_object($value)){
        return 'object';
    }
    if(is_array($value)){
        return 'array';
    }
    if(is_resource($value)){
        return 'resource';
    }
    if(is_callable($value)){
        return 'callable';
    }
    if(is_file($value)){
        return 'file';
    }
    if(is_int($value)){
        return 'integer';
    }
    if(is_float($value)){
        return 'float';
    }
    if(is_bool($value)){
        return 'boolean';
    }
    if(is_null($value)){
        return 'null';
    }
    if(is_string($value)){
        return 'string';
    }
    return null;
}


/**
 * short cut function to channel all php % placeholder formatable strings for
 * sprintf through one function that can be extended at any time
 *
 * @param string $string expects string to format vsprintf
 * @param null|mixed|array $params expects placeholder values
 * @return string
 */
function xapp_sprintf($string, $params = null)
{
    return vsprintf((string)$string, (array)$params);
}


/**
 * get array value from array or array if key is not set or return
 * default value if key is not found. this function can deal with dot notation
 * to get get value from multidimensional associative array like config.database.pass
 * if no key is passed in second parameter will return first parameter direct
 *
 * @param array $array expects array to get key for
 * @param null|mixed $key expects key for value to get
 * @param null|mixed $default expects optional default value if value could not be retrieved by key
 * @return array|mixed|null
 */
function xapp_array_get(Array $array, $key = null, $default = null)
{
    if($key !== null)
    {
        if(array_key_exists($key, $array))
        {
            return $array[$key];
        }
        foreach(explode('.', trim($key, '.')) as $k => $v)
        {
            if(!is_array($array) || !array_key_exists($v, $array))
            {
                return $default;
            }
            $array = $array[$v];
        }
        return $array;
    }else{
        return $array;
    }
}


/**
 * set value to array by reference. if second parameter key is not set
 * overwrites first parameter with value in third parameter. if key is set
 * will be added to array. key can be in dot notation e.g. config.database.user
 * creating the required dimensions to store the value in. most likly will
 * return changed array or value passed in third parameter
 *
 * @param array $array expects array to set value to
 * @param null|mixed $key expects key to set value for in array
 * @param null|mixed $value expects value for key to store in array
 * @return mixed|array|null
 */
function xapp_array_set(Array &$array, $key = null, $value = null)
{
    if($key === null)
    {
        return $array = $value;
    }
    if(strpos($key, '.') === false)
    {
        return $array[$key] = $value;
    }
    $keys = explode('.', trim($key, '.'));
    while(count($keys) > 1)
    {
        $key = array_shift($keys);
        if(!isset($array[$key]) || !is_array($array[$key]))
        {
            $array[$key] = array();
        }
        $array =& $array[$key];
    }
    $array[array_shift($keys)] = $value;
    return null;
}


/**
 * unset key from array passed by reference. if the second parameter key is not
 * set will unset the complete array in first parameter. key can be in dot notation
 * e.g. config.database.user. function will iterate through array to look
 * for the right dimension to unset key at
 *
 * @param array $array expects the array to unset $key from
 * @param null|mixed $key expects optional key to unset
 * @return void
 */
function xapp_array_unset(Array &$array, $key = null)
{
    if($key === null)
    {
        $array = array();
    }else{
        if(array_key_exists($key, $array))
        {
            unset($array[$key]);
        }else{
            $keys = explode('.', trim($key, '.'));
            while(count($keys) > 1)
            {
                $key = array_shift($keys);
                if(!isset($array[$key]) or ! is_array($array[$key]))
                {
                    return;
                }
                $array =& $array[$key];
            }
            unset($array[array_shift($keys)]);
        }
    }
}


/**
 * check if key is set in array in strict mode or not. when in strict mode will
 * also check if the array keys value is not null, false, empty array or empty value.
 * if second parameter key is null will just check if first parameter array is an empty array or not.
 * key can be in dot notation e.g. config.database.user. in the case the first parameter
 * array will be iterate to find right dimension to check if key exits or not
 *
 * @param array $array expects array to check for key
 * @param null|mixed $key expects key
 * @param bool $strict
 * @return bool
 */
function xapp_array_isset(Array $array, $key = null, $strict = false)
{
    if($key === null)
    {
        return (!empty($array)) ? true : false;
    }
    if(array_key_exists($key, $array))
    {
        if((bool)$strict)
        {
            return (xapp_is_value($array[$key])) ? true : false;
        }else{
            return true;
        }
    }
    foreach(explode('.', trim($key, '.')) as $k => $v)
    {
        if(!is_array($array) || !array_key_exists($v, $array))
        {
            return false;
        }
        $array = $array[$v];
    }
    if((bool)$strict)
    {
        return (xapp_is_value($array)) ? true : false;
    }else{
        return true;
    }
}


/**
 * xapp implementation of array_merge with preserves numeric keys and will not reorder these keys starting with
 * index 0. call this function with as much arrays needed
 *
 * @return array
 */
function xapp_array_merge()
{
    $tmp = array();

    foreach(func_get_args() as $a)
    {
        if(is_array($a))
        {
            foreach($a as $k => $v)
            {
                $tmp[$k] = $v;
            }
        }
    }
    return $tmp;
}


/**
 * xapp php < 5.3.0 compatible function of property exist
 *
 * @param string|object $class expects class to test
 * @param string $property expects property to test
 * @return bool
 */
function xapp_property_exists($class, $property)
{
    if(version_compare(PHP_VERSION, '5.3.0', '>='))
    {
        return property_exists($class, $property);
    }else{
        $class = new ReflectionClass($class);
        return $class->hasProperty($property);
    }
}


/**
 * xapp php < 5.3.0 compatible function to get property from class
 *
 * @param string|object $class expects the class name or object
 * @param string $property expects the property to get
 * @param null $default expects default return value
 * @return mixed|null
 */
function xapp_property_get($class, $property, $default = null)
{
    try
    {
        $property = new ReflectionProperty($class, $property);
        if($property->isPublic())
        {
            if($property->isStatic())
            {
                return $property->getValue();
            }else{
                if(is_object($class))
                {
                    return $property->getValue($class);
                }
            }
        }
    }
    catch(Exception $e){}
    return $default;
}
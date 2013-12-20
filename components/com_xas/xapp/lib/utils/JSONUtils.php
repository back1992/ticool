<?php
/**
 * @version 0.1.0
 * @package JSON
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Reads a JSON file.
 * Expects an absolute file/dir pointer and an optional input
 * array or object to initialize function. if a directory is passed will auto create
 * global storage file with the name ".storage"
 *
 * @error 16401
 * @param string $storage expects the absolute path to storage file or directory
 * @param string $type expects storage data type which defaults to php
 * @param array|object|null $input expects optional storage data
 * @throws Xapp_Util_Exception_Storage
 */

class XApp_Utils_JSONUtils{

    /**
     * contains the absolute path to storage file or dir
     *
     * @var null|string
     */
    private  static $_storage = null;

    /**
     * contains the storage type which can by json string or serialized array/object
     *
     * @var string
     */
    protected static $_storageType = '';

    /**
     * contains all allowed storage types
     *
     * @var array
     */
    protected static $_storageTypes = array('json', 'php');

    public static  function  read_json($storage, $type = 'php',$writable=false,$decode=true,$input = array()){

            self::$_storage = DIRECTORY_SEPARATOR . ltrim($storage, DIRECTORY_SEPARATOR);
            self::$_storageType = strtolower(trim((string)$type));

            if(in_array(self::$_storageType, self::$_storageTypes))
            {
                if(is_dir(self::$_storage))
                {
                    if(is_writeable(self::$_storage) && $writable===true)
                    {
                        self::$_storage = rtrim(self::$_storage, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.storage';
                    }else{
                        throw new Xapp_Util_Exception_Storage(vsprintf('storage directory: %s is not writable', array(self::$_storage)), 1640101);
                    }
                }else{
                    if(file_exists(self::$_storage))
                    {
                        if(($container = file_get_contents(self::$_storage)) !== false)
                        {
                            if(!empty($container))
                            {
                                switch(self::$_storageType)
                                {
                                    case 'json':{
                                        if($decode==false){
                                            $container = str_replace(array("\n", "\r","\t"), array('', '',''), $container);
                                            $container = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $container);
                                            $container = preg_replace('/\r\n?/', "", $container);
                                            $container = str_replace('\/','/',$container);
                                            /*$container = str_replace('[[','[',$container);
                                            $container = str_replace(']]',']',$container);*/
                                            $container= preg_replace('/[\x00-\x1F\x7F]/', '', $container);
                                            return $container;
                                        }
                                        $container = Xapp_Util_Json::decode($container, true);
                                        return $container;
                                    }
                                    case 'php':
                                        $container = @unserialize($container);
                                        break;
                                    default:
                                        //nothing
                                }
                                if($container === null || $container === false)
                                {
                                    throw new Xapp_Util_Exception_Storage('unable to decode stored data', 1640103);
                                }
                            }
                            if(!empty($container))
                            {
                                //$this->data = $container;
                            }else{

                            }
                        }else{
                            throw new Xapp_Util_Exception_Storage('unable to read storage from file', 1640104);
                        }
                    }else{
                        if($writable===true && !is_writeable(dirname(self::$_storage)))
                        {
                            throw new Xapp_Util_Exception_Storage(vsprintf('storage directory: %s is not writable', array(self::$_storage)), 1640101);
                        }
                    }
                }
            }else{
                throw new Xapp_Util_Exception_Storage("storage type: $type is not supported", 1640105);
            }
            return null;
    }
}
/***
 * Static wrapper for Xapp_Util_Json_Query to query an object|array
 * @param $object
 * @param $path
 * @param $query
 *
 * @param bool $toArray
 * @return $this|array|bool
 */
function xapp_object_find($object,$path,$query,$toArray=true){


    ini_set('display_errors', '0');     # don't show any errors...
    error_reporting(E_ERROR);

    $queryObject = null;
    if(is_string($object)){
        $queryObject = $object;
    }else{
        $queryObject = json_encode($object);
    }
    $q = new Xapp_Util_Json_Query($queryObject);
    $res = $q->query($path, $query);
    ini_set('display_errors', '1');     # don't show any errors...
    error_reporting(E_ALL);
    return $res->get();

}
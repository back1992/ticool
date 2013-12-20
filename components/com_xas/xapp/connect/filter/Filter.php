<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * XApp-Connect-Filter base class.
 *
 *
 * @package XApp-Connect\Filter
 * @class Xapp_Connect_Filter
 * @error @TODO
 * @author  mc007
 */
abstract class Xapp_Connect_Filter
{


    const FILTER_XC_SCHEMA            = 'Xapp_Connect_Schema';


    var $logger;
    var $cache;
    var $serviceConfiguration;
    var $customType;

    var $user;
    var $data;
    var $schemas;

    public static function factory($inData,$filterClass,$_logger,$_cache,$_conf,$_ctype,$_user,$_schemas,$delegate=null){
        if(class_exists($filterClass, true))
        {
            $result = new $filterClass();
            if($delegate){
                $result->delegate=$delegate;
            }
            $result->setup($inData,$_logger,$_cache,$_conf,$_ctype,$_user,$_schemas);
            return $result;
        }else{
            error_log('filter class doesnt exists : ' .$filterClass);
        }
        return null;
    }

    public function apply($inData,$encode=true){
        return $inData;
    }

    public function log($message,$stdError=true){
        if($this->logger){
            $this->logger->log($message);
        }

        if($stdError){
            error_log('Error : '.$message);
        }
    }

    public  function getLastJSONError(){

        $result = null;
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $result = ' - No errors';
                break;
            case JSON_ERROR_DEPTH:
                $result = ' - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $result =  ' - Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $result = ' - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $result = ' - Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $result = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $result = ' - Unknown error';
                break;
        }


        return $result;
    }
    public function setup($_data,$_logger,$_cache,$_conf,$_ctype,$_user,$_schemas){
        $this->data=$_data;
        $this->logger=$_logger;
        $this->cache=$_cache;
        $this->serviceConfiguration=$_conf;
        $this->customType = $_ctype;
        $this->user = $_user;
        $this->schemas = $_schemas;
    }

    public function dumpObject($obj,$prefix=''){
        $d = print_r($obj,true);
        error_log('' .$prefix . ' : ' . $d);
        return $d;
    }

}
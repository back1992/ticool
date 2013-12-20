<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

xapp_import('xapp.Rpc.Request.Exception');
xapp_import('xapp.Rpc.Fault');
xapp_import('xapp.Rpc.Request');

/**
 * Rpc request json class
 *
 * @package Rpc
 * @subpackage Rpc_Request
 * @class Xapp_Rpc_Request_Json
 * @error 150
 * @author Frank Mueller <support@xapp-studio.com>
 */
class Xapp_Rpc_Request_Json extends Xapp_Rpc_Request
{
    /**
     * class constructor calls parent constructor to initialize class.
     *
     * @error 15001
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * decodes json raw data throwing error if phps native json_decode function is not supported by system.
     * returns input data in first parameter as is if not a string. throws fault if json string could not
     * be decoded
     *
     * @error 15002
     * @param string $data expects the raw json string to decode
     * @return mixed
     * @throws Xapp_Rpc_Request_Exception
     */
    public function decode($data)
    {
        if(!is_string($data))
        {
            return $data;
        }
        if(function_exists('json_decode'))
        {
            $data = json_decode($this->prepare($data), true);
            //$data = json_decode($this->prepare($data));
            if($data !== null)
            {
                return $data;
            }else{
                if(version_compare(PHP_VERSION, '5.3.0', '>='))
                {
                    Xapp_Rpc_Fault::t('unable to decode json string', array(1500202, -32700), XAPP_ERROR_IGNORE, array('jsonError' => $this->error(json_last_error())));
                }else{
                    Xapp_Rpc_Fault::t('unable to decode json string', array(1500202, -32700));
                }
            }
        }else{
            throw new Xapp_Rpc_Request_Exception("php function json_decode is not supported by system", 1500201);
        }
    }


    /**
     * gets the rpc version from request be looking for jsonrpc or version parameter in request
     *
     * @error 15003
     * @return null|string
     */
    public function getVersion()
    {
        if($this->hasParam('jsonrpc'))
        {
            return $this->getParam('jsonrpc');
        }
        if($this->hasParam('version'))
        {
            return $this->getParam('version');
        }
        return null;
    }


    /**
     * prepares raw json string before decoding
     *
     * @error 15004
     * @param string $json expects the raw json string
     * @return string
     */
    protected function prepare($json)
    {
        $json = trim($json);
        $json = mb_convert_encoding($json, 'UTF-8', 'ASCII,UTF-8,ISO-8859-1');
        if(substr($json, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF))
        {
            $json = substr($json, 3);
        }
        return $json;
    }


    /**
     * returns json decode error message for json error code in first parameter.
     * if first parameter is not set tries to get last error automatically
     *
     * @error 15005
     * @param null|int $error expects optional json error code
     * @return string
     */
    protected function error($error = null)
    {
        if($error !== null)
        {
            $error = (int)$error;
        }else{
            $error = (int)json_last_error();
        }
        switch($error)
        {
            case JSON_ERROR_NONE:
                return 'no errors';
            case JSON_ERROR_DEPTH:
                return 'maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'syntax error, malformed json';
            case JSON_ERROR_UTF8:
                return 'malformed utf-8 characters, possibly incorrectly encoded';
            default:
                return 'unknown error';
        }
    }
}
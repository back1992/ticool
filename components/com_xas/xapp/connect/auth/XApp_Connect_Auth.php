<?php
/**
 * @version 0.1.0
 * @package XApp-Connect\Auth
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
class XApp_Connect_Auth {


    var $logger=null;

    function loginUserEx($username, $password)
    {

    }

    function loginUser($username, $password)
    {

    }
    public function log($message,$stdError=true){
        if($this->logger){
            $this->logger->log($message);
        }
        if($stdError){
            error_log('XApp-Connect-Auth-Error : '.$message);
        }
    }


}
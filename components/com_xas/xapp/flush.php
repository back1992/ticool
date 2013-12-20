<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('XAPPED', true);
define("XAPP_BASEDIR", realpath(dirname(__FILE__)) . "/");
define("XAPP_LIB", realpath(dirname(__FILE__)) . "/lib/");
define("XAPP_CTYPES", XAPP_BASEDIR . "ctypes/");
define("XAPP_DEFAULT_LOG_PATH", XAPP_BASEDIR .'/cache/');
require_once(XAPP_BASEDIR . "includes.php");
require_once(XAPP_BASEDIR . "defines.php");
include(XAPP_BASEDIR . "conf.php");//conf wrapper
include(XAPP_BASEDIR . "conf.inc.php");//conf data

try{
    $files = glob(XAPP_BASEDIR .'/cache/*'); // get all file names
    foreach($files as $file){ // iterate files
        if(is_file($file))
            unlink($file); // delete file
    }

}catch(Exception $e)
{

}

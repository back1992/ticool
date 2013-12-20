<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @package XApp-Connect\Wordpress
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
/***
 * Includes all the Wordpress stuff
 */
if(!defined('DS')){
    define( 'DS', DIRECTORY_SEPARATOR );
}

$xapp_wp_root_prefix = "..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR ."..".DIRECTORY_SEPARATOR ."../";
require_once($xapp_wp_root_prefix . "wp-load.php");

?>

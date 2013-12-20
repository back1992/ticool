<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 * Includes all the Joomla stuff and also sets DirectorySeperator=DS.
 */
if(!defined('_JEXEC')){
    define( '_JEXEC', 1 );
}
if(!defined('JPATH_BASE')){
    define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../../' ));
}
if(!defined('DS')){
    define( 'DS', DIRECTORY_SEPARATOR );
}
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

$mainframe =JFactory::getApplication('site');
?>

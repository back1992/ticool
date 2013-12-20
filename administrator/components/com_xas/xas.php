<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***************************************************************************************
 * Pre-cautions
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!defined('DS')){
    define('DS',DIRECTORY_SEPARATOR);
}
error_reporting(E_ALL);
ini_set('display_errors', 0);


/**
 * Important configurations
 */
$xapp4j_has_xml=false;
$xapp4j_has_xas_gui=false;
$xapp4j_xas_gui_debug=false;

$xapp4j_classic_template="/templates/joomla/xas_normal.php";
$xapp4j_xas_template="/templates/joomla/xas.debug.php";

/***
 * Important variables
 */
$xapp4j_admin_root  = JPATH_SITE. DS. "administrator" . DS . "components" . DS.  "com_xas" . DS;
$xapp4j_xapp_root  = JPATH_SITE. DS.  "components" . DS.  "com_xas" . DS . "xapp" . DS;

/***************************************************************************************
 * Imports
 */

//XML - Stuff : @TODO : remove this
if($xapp4j_has_xml){
    jimport("joomla.html.parameter.element");
    jimport('joomla.utilities.simplexml');

    if(!file_exists(JPATH_SITE.DS."libraries".DS."phpxmlrpc".DS."xmlrpc.php"))
    {
        require(JPATH_SITE.DS."components".DS."com_xas".DS."phpxmlrpc".DS."xmlrpc.php");
        require(JPATH_SITE.DS."components".DS."com_xas".DS."phpxmlrpc".DS."xmlrpcs.php");
    }
}

//XAS includes
if($xapp4j_has_xas_gui){
    /*include_once($xapp4j_admin_root.$xapp4j_has_xas_gui);*/
}

/***************************************************************************************
 * Joomla - Admin stuff :
 */
$document = JFactory::getDocument();
$document->addStyleSheet("components/com_xas/xas.css");


JToolBarHelper::title(JText::_('Quick-XApp' ), 'xas');
JToolBarHelper::preferences('com_xas', '500');

/***************************************************************************************
 * Rendering
 */

if($xapp4j_has_xas_gui){

    //1. setup paths first
    global $XAPP_ADMIN_ROOT;
    $XAPP_ADMIN_ROOT = $xapp4j_admin_root;

    global $XAPP_XAPP_ROOT;
    $XAPP_ROOT = $xapp4j_xapp_root;



    ob_start();
    include($xapp4j_admin_root . $xapp4j_xas_template);
    $output = ob_get_contents();
    ob_end_clean();
    echo $output;

}else{
    //load the simple version
    include_once require(JPATH_SITE. DS. "administrator" . DS . "components" . DS.  "com_xas" . DS. $xapp4j_classic_template);
}

?>

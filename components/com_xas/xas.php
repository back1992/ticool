<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!defined('E_RECOVERABLE_ERROR')) {
    define('E_RECOVERABLE_ERROR', 4096);
}
if(!defined('DS')){
    define('DS',DIRECTORY_SEPARATOR);
}
$hasXML = false;

if($hasXML){

}else{

    ob_start();
    $controller = "com_xas";
    $rpcPath = JPATH_COMPONENT.DS.'xapp'.DS.'index.php';
    include($rpcPath);
    $output = ob_get_contents();
    ob_end_clean();
    echo $output;
    exit;
}
?>

<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

/**
 * Rpc callable interface
 *
 * @package Rpc
 * @author Frank Mueller <support@xapp-studio.com>
 */
interface Xapp_Rpc_Interface_Callable
{
    /**
     * method that will be called before the actual requested method is called
     *
     * @return void
     */
    public function onBeforeCall();


    /**
     * method that will be called after the requested method has been invoked
     *
     * @return void
     */
    public function onAfterCall();
}
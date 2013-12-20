<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * RPC-Base interface
 *
 * @package XApp-Connect
 * @class Xapp_Connect_IPlugin
 * @error @TODO
 * @author  mc007
 */
interface Xapp_Connect_IPlugin
{
    function load();

    function setup();
}
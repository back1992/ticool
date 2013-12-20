<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * XApp-Connect-Plugin base class.
 *
 * The class provides common Wordpress related functions like bootstrapping
 * and holding references to Wordpress objects (ie:mainframe,dbo,..)
 *
 * @package XApp-Connect\Wordpress
 * @class Xapp_Wordpress_Plugin
 * @error @TODO
 * @author  mc007
 */
class Xapp_Wordpress_Plugin extends  Xapp_Connect_RPCPlugin implements Xapp_Connect_IPlugin
{
    protected $db;
    protected $version;

    private function init(){}

    public function cleanUrl($url){
        return str_replace('components/com_xas/xapp','',$url);
    }

    public function rootUrl(){
        //$prefix = JURI::base( true );
        //$prefix = $this->cleanUrl($prefix);
        //$res = str_replace($prefix,'',$this->siteUrl());
        return '';
    }

    function siteUrl(){
        //$res = JURI::base();
        //$res = str_replace('components/com_xas/xapp/','',$res);
        return '';
    }

    //Xapp_Connect_IPlugin Impl.
    function load(){
        parent::load();
        //jimport('joomla.version');
        //$this->version = new JVersion();
    }
    function setup(){
        parent::setup();
       // $this->db =& JFactory::getDBO();
    }
}
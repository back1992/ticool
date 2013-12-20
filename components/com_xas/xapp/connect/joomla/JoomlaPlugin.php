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
 * The class provides common Joomla related functions like bootstrapping
 * and holding references to Joomla objects (ie:mainframe,dbo,..)
 *
 * @package XApp-Connect\Joomla
 * @class Xapp_Joomla_Plugin
 * @error @TODO
 * @author  mc007
 */
class Xapp_Joomla_Plugin extends Xapp_Connect_RPCPlugin implements Xapp_Connect_IPlugin
{
    protected $db;
    protected $version;

    private function init(){}


    public function getGatewayUrl(){
        $result = $this->siteUrl();
        $result.='/index.php?option=com_xas&view=rpc';
        return $result;

    }
    public function getCurrentUser(){
        $session = JFactory::getSession();
        $user   = JFactory::getUser();
        if(!$user){
            error_log('Jplugin:: have no user');
        }
        return $user;
    }
    public function getUserId(){
        $session = JFactory::getSession();
        $user   = JFactory::getUser();
        $userId = -1;
        if($user){
            //error_log('Jplugin:: have user : '. $user->get('id'));
            $userId = (int) $user->get('id');
        }
        return $userId;
    }

    public function isLoggedIn(){
        $session = JFactory::getSession();
        $user   = JFactory::getUser();
        $userId = 0;
        if($user){
            $userId = (int) $user->get('id');
            error_log('isLoggedIn  : ' . $userId);
        }
        return $userId !=0;
    }

    public function cleanUrl($url){
        return str_replace('components/com_xas/xapp','',$url);
    }

    public function completeUrl($url){
        if(!strpos($url,'http')||!strpos($url,'https')){
            return $this->siteUrl().'/'. $url;
        }
        return $url;
    }
    public function jPath(){
        $res = JURI::root( true );
        $res = str_replace('components/com_xas/xapp','',$res);
        return $res;
    }


    public function completeUrlSafe($url){
        if(!strpos($url,'http')||!strpos($url,'https')){
            $siteUrl = $this->jPath();
            $siteUrl = str_replace('/','',$siteUrl);
            $url = str_replace('components/com_xas/xapp','/',$url);
            $url = str_replace( $siteUrl ,'/',$url);
            $url = xapp_remove_DoubleSlash($url);
            $basePath = JURI::base( false );
            $basePath = str_replace('components/com_xas/xapp','',$basePath);
            return  $basePath .'/'. $url;
        }
        return $url;
    }
    public function rootUrl(){
        $prefix = JURI::base( true );
        $prefix = $this->cleanUrl($prefix);
        $res = str_replace($prefix,'',$this->siteUrl());
        return $res;
    }



    function siteUrl(){
        $res = JURI::base();
        $res = str_replace('components/com_xas/xapp/','',$res);
        return $res;
    }

    //Xapp_Connect_IPlugin Impl.
    function load(){
        parent::load();
        $this->loaded=true;
        jimport('joomla.version');
        $this->version = new JVersion();
    }
    function setup(){
        parent::setup();
        $this->db =JFactory::getDBO();
    }

    public function onAfterCall($result){
        parent::onAfterCall($result);
    }
}
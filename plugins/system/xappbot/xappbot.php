<?php
/*
*	MobileESP
* 	The MobileESP Project is Copyright 2010-2012, Anthony Hand
*
*	Plugin Author:		Robert Gerald Porter <rob@weeverapps.com>
*	Library Author:		Anthony Hand <http://code.google.com/p/mobileesp/>
*	Version: 			1.1.2
*	License: 			GPL v3.0
*
*	This extension is free software: you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation, either version 3 of the License, or
*	(at your option) any later version.
*
*	This extension is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details <http://www.gnu.org/licenses/>.
*
*/

defined('_JEXEC') or die();

jimport('joomla.plugin.plugin');

if(!defined('JPATH_BASE')){
    define('JPATH_BASE', dirname(__FILE__) . '/../../../' );
}
if(!defined('DS')){
    define( 'DS', DIRECTORY_SEPARATOR );
}

if((substr(JVERSION,0,3) == '1.5'))
{
    require_once JPATH_PLUGINS . DS . 'system' . DS . 'xappbot' . DS . 'xappbotdetect.php';
}else{
    require_once JPATH_PLUGINS . DS . 'system' . DS . 'xappbot' . DS . 'xappbot' . DS . 'xappbotdetect.php';
}


class plgSystemXappBot extends JPlugin
{

    public function plgSystemXappBot(&$subject, $config)
    {

        $app =JFactory::getApplication();

        // disable on the admin backend
        if ($app->isAdmin())
            return;

        parent::__construct($subject, $config);

    }

    function isJoomla15()
    {
        static $is_joomla15;
        if(!isset($is_joomla15))
            $is_joomla15 = (substr(JVERSION,0,3) == '1.5');
        return $is_joomla15;
    }

    /***
     * Set template
     */
    public function onRenderMobileDisplay($template)
    {

        $app =JFactory::getApplication();
        if($this->isJoomla15())
        {
            $app->setUserState('setTemplate', $template);
            $app->setTemplate($template);
        }
        else
        {
            $db = JFactory::getDBO();
            $query = "SELECT params FROM #__template_styles WHERE client_id = 0 AND template = ".$db->Quote($template)." ORDER BY id LIMIT 1";
            $db->setQuery($query);
            $params_data = $db->loadResult();
            if(empty($params_data))
                $params_data = '{}';
            if(version_compare(JVERSION, '1.7', '>='))
            {
                $app->setTemplate($template, $params_data);
            }
            elseif(version_compare(JVERSION, '1.6', '>='))
            {
                $app->setTemplate($template);
                $template_obj = $app->getTemplate(true);
                $template_obj->params->loadJSON($params_data);
            }
        }
        //jexit();

    }

    public function getDSUrl()
    {
        //$session =JFactory::getSession();
        $router = JFactory::getApplication()->getRouter('site');
        $uri = JURI::getInstance();
        $router = JRouter::getInstance('site');
        // Encode (build route)
        //$route = $router->build($url = 'index.php?
        //option=com_content&view=article&id=1);

        // Decode (parse route)
        $variables = $router->parse($uri);
        //$dump = print_r($variables,true);
        //error_log('uri components ' . $dump,0);
        //$query = http_build_query(array('aParam' => $variables));
        $query = http_build_query($variables) . "\n";
        return $query;
        //error_log('url ' . $query);
    }
    public function onAfterInitialise()
    {
        $session =JFactory::getSession();
        // none of this if it's an RSS request, specific template, or
        // component-only setting to play nice with Joomla devs & Weever
        if (JRequest::getVar('template') || JRequest::getVar('format') || JRequest::getVar('tmpl') || JRequest::getVar('wxfeed') || JRequest::getVar('wxCorsRequest') || JRequest::getVar('option') == 'com_user' || JRequest::getVar('option') == 'com_users' || JRequest::getVar('wxConfirmLogin') || JRequest::getVar('wxConfirmLogout'))
            return;

        /* Compatibility with login extension */

        if (JRequest::getVar('option') == 'com_xas')
            return;

        // kill the ignore_mobile session var if full=0 added to query
        if (JRequest::getVar('full') == '0') {
            $session->set('ignore_mobile', '');
        }

        // if requesting the full site, ignore all this
        if (JRequest::getVar('full') > 0 || $session->get('ignore_mobile', '') == '1') {

            $session->set('ignore_mobile', '1');
            return;

        }

        $mobileDisplay = $this->params->get('mobileDisplay', 0);

        if (!$this->params->get('forwardingEnabled', 0) && !$mobileDisplay)
            return;

        $uagent_obj = new uagent_info();

        if (!$this->params->get('webkitOnly', 0) && (!$uagent_obj->DetectWebkit())) {
            $session->set('ignore_mobile', '1');
            return;
        }

        $devices = $this->params->get('devicesForwarded', '');
        if (!$devices)
            return;


        $deviceList = explode(",", $devices);

        $forwardApp = false;

        foreach ((array)$deviceList as $v)
        {
            if ($uagent_obj->$v())
                $forwardApp = true;
        }

        if ($forwardApp == false)
            return;


        $request_uri = $_SERVER['REQUEST_URI'];

        $request_uri = str_replace("?full=0", "", $request_uri);
        $request_uri = str_replace("&full=0", "", $request_uri);

        if ($request_uri && $request_uri != 'index.php' && $request_uri != '/')
            $exturl = 'exturl=' . urlencode($request_uri);
        else
            $exturl = "";

        $dsUrl = $this->getDSUrl();
        if($dsUrl){
            $dsUrl = '&dsUrl=' . urlencode($dsUrl);
        }

        if ($this->params->get('forwardingEnabled', 0))
        {
            $forwardUrl = '';
            if ($uagent_obj->DetectIpad() || $uagent_obj->DetectAndroidTablet()) {
                $forwardUrl = $this->params->get('forwardingUrl') . '?noSim=true' . '&uuid=' . $this->params->get('uuid') . '&appId=' . $this->params->get('appId') . $dsUrl;
            } else {
                $forwardUrl = $this->params->get('forwardingUrl') . '?noSim=true&uuid=' . $this->params->get('uuid') . '&noSim=true' . '&appId=' . $this->params->get('appId') . $dsUrl;
            }
            header('Location: ' . $forwardUrl , '');
            jexit();
        }
        return $this->onRenderMobileDisplay('qxapp');
    }

}


class XASConfigUtil
{

    static function getXASConfigTable()
    {

        $db = JFactory::getDBO();

        $query = "	SELECT	* " .
            "	FROM	#__xas_config ";

        $db->setQuery($query);
        $result = $db->loadObjectList();

        return $result;

    }


    static function getPrimaryDomain($result)
    {

        foreach ((array)$result as $k => $v) {
            if ($v->option == "primary_domain")
                return $v->setting;
        }

        return null;

    }


    static function getDevices($result)
    {

        foreach ((array)$result as $k => $v) {
            if ($v->option == "devices")
                return $v->setting;
        }

        return null;

    }


    static function getAppEnabled($result)
    {

        foreach ((array)$result as $k => $v) {
            if ($v->option == "app_enabled")
                return $v->setting;
        }

        return null;
    }


    static function getCustomAppDomain($result)
    {

        foreach ((array)$result as $k => $v) {
            if ($v->option == "domain")
                return $v->setting;
        }

        return null;
    }


    static function currentPageURL()
    {

        $pageURL = 'http';

        if ($_SERVER["HTTPS"] == "on")
            $pageURL .= "s";

        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80")
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        else
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

        return $pageURL;

    }

}
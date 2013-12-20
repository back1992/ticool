<?php
/*
 *
 * @package		ARI Quiz Lite
 * @author		ARI Soft
 * @copyright	Copyright (c) 2011 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

defined('_JEXEC') or die('Restricted access');

if (!defined('J3_0'))
{
	$version = new JVersion();
	define('J3_0', version_compare($version->getShortVersion(), '3.0.0', '>='));
}

class com_ariquizliteInstallerScript
{
	function preflight($type, $parent)
	{	
		$db = JFactory::getDBO();		
		
		$db->setQuery('DELETE FROM `#__menu` WHERE `link` LIKE "%option=com_ariquizlite%" AND client_id = 1 AND menutype = "main"');
		$db->query();

		return true;
	}
	
	function postflight($type, $parent)
	{		
		if (J3_0 && ($type == 'install' || $type == 'update'))
			$this->_executeInstall();
			
		return true;
	}

	function uninstall()
	{
		if (J3_0)
		{
			require_once dirname(__FILE__) . '/uninstall.php';
	
			if (function_exists('com_uninstall'))
				com_uninstall();
		}
	}

	function _executeInstall()
	{
		require_once dirname(__FILE__) . '/backend/install.php';

		if (function_exists('com_install'))
			com_install();
	}
}
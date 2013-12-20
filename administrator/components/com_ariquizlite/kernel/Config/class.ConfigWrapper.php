<?php
/*
 * ARI Framework Lite
 *
 * @package		ARI Framework Lite
 * @version		1.0.0
 * @author		ARI Soft
 * @copyright	Copyright (c) 2009 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');

AriKernel::import('Config._Templates.ConfigTemplates');
AriKernel::import('Controllers.ConfigController');
AriKernel::import('Cache.FileCache');

define ('ARI_QUIZ_CONFIG_KEY', '_ariQuizConfig');

class AriConfigWrapper extends AriObject
{
	function setConfigValue($key, $value)
	{
		$configController = new AriConfigController();
		$configController->call('setConfigValue', $key, $value);
		if (!$this->_isError(true, false))
		{
			AriConfigWrapper::_createConfigCache();
		}
	}
	
	function _createConfigCache()
	{
		$configFile = AriConfigWrapper::_getConfigFilePath();
		$configController = new AriConfigController();
		$config = $configController->call('getConfig');
		
		$GLOBALS[ARI_QUIZ_CONFIG_KEY] = $config;
		$configContent = sprintf(ARI_CONFIG_CACHE_TEMPLATE,
						ARI_QUIZ_CONFIG_KEY,
						var_export($config, true));
		AriFileCache::saveTextFile($configContent, $configFile);
	}
	
	function _getConfigFilePath()
	{
		return ARI_QUIZ_CACHE_DIR . 'config.php';
	}
	
	function getConfig()
	{
		static $isLoaded = false;
		
		if (!$isLoaded)
		{
			$configFile = AriConfigWrapper::_getConfigFilePath();
			if (!file_exists($configFile))
			{
				AriConfigWrapper::_createConfigCache();
			}
			
			require_once $configFile;
			$isLoaded = true;
		}

		return isset($GLOBALS[ARI_QUIZ_CONFIG_KEY]) ? $GLOBALS[ARI_QUIZ_CONFIG_KEY] : array(); 
	}
	
	function getConfigKey($key, $defaultValue = null)
	{
		$config = AriConfigWrapper::getConfig();
		
		return isset($config[$key]) ? $config[$key] : $defaultValue;
	}
	
	function removeConfigKey($key)
	{
		$configController = new AriConfigController();
		$configController->call('removeConfigKey', $key);
		if (!$this->_isError(true, false))
		{
			AriConfigWrapper::_createConfigCache();
		}
	}
}
?>
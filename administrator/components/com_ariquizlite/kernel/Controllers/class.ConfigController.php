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

class AriConfigController extends AriControllerBase
{
	function getConfig()
	{
		$config = array();
		$query = 'SELECT ParamName,ParamValue FROM #__ariquizconfig';
		$this->_db->setQuery($query);
		$list = $this->_db->loadAssocList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt load config.', E_USER_ERROR);
			return $config;
		}
		
		if (!empty($list))
		{
			foreach ($list as $row)
			{
				$config[$row['ParamName']] = $row['ParamValue'];
			}
		}
		
		return $config;
	}
	
	function getConfigValue($key)
	{
		$query = sprintf('SELECT ParamValue FROM #__ariquizconfig WHERE ParamName = %s LIMIT 0,1',
			$this->_db->Quote($key));
		$this->_db->setQuery($query);
		$value = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get config value.', E_USER_ERROR);
			return null;
		}
		
		return $value;
	}
	
	function setConfigValue($key, $value)
	{		
		$query = sprintf('INSERT INTO #__ariquizconfig (ParamName,ParamValue) VALUES(%s,%s) ON DUPLICATE KEY UPDATE ParamValue = %2$s',
			$this->_db->Quote($key),
			$this->_db->Quote($value));
		$this->_db->setQuery($query);
		$this->_db->query();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt store config value.', E_USER_ERROR);
			return false;
		}
		
		return true;
	}
	
	function removeConfigKey($key)
	{		
		$query = sprintf('DELETE FROM #__ariquizconfig WHERE ParamName = %s',
			$this->_db->Quote($key));
		$this->_db->setQuery($query);
		$this->_db->query();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt remove config key.', E_USER_ERROR);
			return false;
		}
		
		return true;
	}
}
?>
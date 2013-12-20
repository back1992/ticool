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

AriKernel::import('Web.PageBase');

define('ARI_TM_KEY_PAGEDIR', 'pageDir');
define('ARI_TM_KEY_TEMPLATEDIR', 'templateDir');
define('ARI_TM_KEY_TEMPLATEEXT', 'templateExt');
define('ARI_TM_SPLITTER_GROUP', '.');
define('ARI_TM_SPLITTER_EVENT', '$');
define('ARI_TM_SPLITTER_EVENTARGS', ':');
define('ARI_TM_REQUEST_EVENT_NAME', 'ariEvent');
define('ARI_TM_REDIRECT_KEY', 'ariPKey');
define('ARI_TM_SES_KEY', 'ariTM');
define('ARI_TM_SES_REDIRECT_KEY', 'ariRedirect');

class AriTaskManager 
{
	var $_taskList;
	var $_taskGroupMapping = array();
	var $_defaultTask;
	var $_defaultGroup;
	
	function &_getInstance()
	{
		static $instance;
		
		if (!isset($instance))
		{
			$c = __CLASS__;
            $instance = new $c;
		}
		
		return $instance;
	}
	
	function setDefaultTask($task)
	{
		$_this =& AriTaskManager::_getInstance();
		
		$_this->_defaultTask = $task;
	}
	
	function setDefaultGroup($group)
	{
		$_this =& AriTaskManager::_getInstance();
		
		$_this->_defaultGroup = $group;
	}
	
	function registerTaskGroup($group, $pageDir, $options = null)
	{
		$_this =& AriTaskManager::_getInstance();

		if (empty($options)) $options = array();
		$options[ARI_TM_KEY_PAGEDIR] = $pageDir;
		$_this->_taskGroupMapping[$group] = $options;
	}
	
	function doTask($task, $loadParams = true)
	{
		$_this =& AriTaskManager::_getInstance();
	
		if (empty($task)) $task = $_this->_defaultTask;
		if (empty($task)) return false; 
		
		$taskParts = explode(ARI_TM_SPLITTER_GROUP, $task);
		$group = $_this->_defaultGroup;
		if (count($taskParts) > 1)
		{
			$group = $taskParts[0];
			array_shift($taskParts);
			$task = implode('.', $taskParts);
		}
		
		if (empty($group) || !isset($_this->_taskGroupMapping[$group])) $group = $_this->_defaultGroup;
		if (!isset($_this->_taskGroupMapping[$group])) return false;

		$groupOptions = $_this->_taskGroupMapping[$group];
		$dir = $groupOptions[ARI_TM_KEY_PAGEDIR];
		$event = null;
		$eventArgs = null;
		list($task, $event, $eventArgs) = $_this->_getPageParams($task);
		if (!preg_match('/^[A-z]+$/', $task) || !file_exists($dir . $task . '.php'))
		{
			$group = $_this->_defaultGroup;
			$groupOptions = $_this->_taskGroupMapping[$group];
			$dir = $groupOptions[ARI_TM_KEY_PAGEDIR];
			$task = $_this->_defaultTask;
			list($task, $event, $eventArgs) = $_this->_getPageParams($task);
		}

		$file = $dir . $task . '.php';
		if (file_exists($file))
		{
			require_once $file;
			$className = $task . 'AriPage';
			if (class_exists($className))
			{
				$templateDir = isset($groupOptions[ARI_TM_KEY_TEMPLATEDIR]) 
					? $groupOptions[ARI_TM_KEY_TEMPLATEDIR] 
					: null;
				$template = null;
				if ($templateDir !== null)
				{ 
					$ext = isset($groupOptions[ARI_TM_KEY_TEMPLATEEXT]) ? $groupOptions[ARI_TM_KEY_TEMPLATEEXT] : 'php'; 
					$template = $templateDir . $task . '.' . $ext;
				}

				//if ($loadParams) $_this->_loadParams();
				$page = new $className($template, $event, $eventArgs);
				$page->execute();
				restore_error_handler();
				return true;
			}
		}
		
		return false;
	}
	
	function getTask($task, $withoutGroup = false)
	{
		if ($task != null && strlen($task) > 0)
		{
			if ($withoutGroup)
			{
				$pos = strpos($task, ARI_TM_SPLITTER_GROUP);
				if ($pos !== FALSE)
				{
					$task = substr($task, $pos + 1);
				}
			}
			
			$pos = strpos($task, ARI_TM_SPLITTER_EVENT);
			if ($pos !== FALSE)
			{
				$task = substr($task, 0, $pos);
			}
		}
		
		return $task;
	}

	function _getPageParams($task)
	{
		$event = null;
		$eventArgs = null;
		$pos = strpos($task, ARI_TM_SPLITTER_EVENT);
		if ($pos !== false)
		{
			if ($pos < strlen($task) - 1)
			{
				$event = substr($task, $pos + 1);
				$posEvent = strpos($event, ARI_TM_SPLITTER_EVENTARGS);
				if ($posEvent !== false)
				{
					if ($posEvent < strlen($event) - 1)
					{
						$eventArgs = explode(ARI_TM_SPLITTER_EVENTARGS, substr($event, $posEvent + 1));
					}
					
					$event = substr($event, 0, $posEvent);
				}
			}
			
			$task = substr($task, 0, $pos);
		}
		
		if ($event === null)
		{
			if (isset($_REQUEST[ARI_TM_REQUEST_EVENT_NAME]))
			{
				$e = $_REQUEST[ARI_TM_REQUEST_EVENT_NAME];
				if (is_array($e))
				{
					if (count($e) > 0)
					{
						$e = array_keys($e);
						$event = $e[0];
					}
				}
				else
				{
					$event = $e;
				}
			}
		}

		return array($task, $event, $eventArgs);
	}
}
?>

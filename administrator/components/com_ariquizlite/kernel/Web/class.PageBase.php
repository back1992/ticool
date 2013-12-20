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

class AriPageBase extends AriObject
{
	var $_template;
	var $_variables = array();
	var $_eventsMapping = array();
	
	function __construct($template = null, $event = null, $eventArgs = null)
	{
		$this->_template = $template;
		$this->_registerEventHandlers();
		$this->_registerErrorHandler();
		
		$this->_init();
		$this->_checkAriMsg();
		$this->_raiseEvent($event, $eventArgs);
	}
	
	function _checkAriMsg()
	{
		$msgId = JRequest::getString('arimsg', '');
		if (!empty($msgId))
		{
			echo '<div class="message">' . AriQuizWebHelper::getResValue($msgId) . '</div>';
		}
	}
	
	function execute()
	{
		$processPage =& $this;

		if (!empty($this->_template) && file_exists($this->_template))require_once $this->_template;
	}
	
	function addVar($name, &$value)
	{
		$this->_variables[$name] =& $value;
	}
	
	function getVar($name)
	{
		$var = null;
		if (isset($this->_variables[$name]))
		{
			$var = $this->_variables[$name];
		}
		else if (isset($GLOBALS[$name]))
		{
			$var = $GLOBALS[$name];
		}
		
		return $var;
	}

	function _registerEventHandler($event, $handler)
	{
		$this->_eventsMapping[$event] = $handler;
	}
	
	function _registerEventHandlers()
	{
	}
	
	function _raiseEvent($event, $eventArgs)
	{
		if ($event && isset($this->_eventsMapping[$event]))
		{
			$handler = $this->_eventsMapping[$event];
			$this->$handler($eventArgs);
		}
	}
	
	function _registerErrorHandler()
	{
		set_error_handler(array(&$this, 'errorHandler'));
	}
	
	function _isError($clear = TRUE, $raised = TRUE)
	{
		$error = $this->_lastError;
		$isError = $error !== null; 		
		
		if ($isError)
		{
			if ($clear)
			{
				$this->_lastError = null;
			}
	
			if ($raised)
			{
				$this->_raiseError($error);
			}
		}
		
		return $isError;
	}
	
	function _raiseError($error)
	{
		echo '<script type="text/javascript">alert("' . str_replace("'", "\\'", $error->error) . '")</script>';
	}
	
	function _init()
	{
		
	}
}
?>

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

AriKernel::import('Controllers.FileController');
AriKernel::import('Xml.XmlHelper');

class LangAddPageBase extends AriPageBase 
{
	var $_untitledGroup = 'Untitled';
	var $_savedIndex = array('ShortDescription');
	var $_fileController;
	var $_fileGroup;
	var $_task;
	var $_listTask;

	function _init()
	{
		if (J1_6)
			JHtml::_('behavior.framework');

		$this->_fileController = new AriFileController(ARI_QUIZ_FILE_TABLE);
		
		parent::_init();
	}
	
	function execute()
	{
		$fileId = JRequest::getInt('fileId');
		$file = $this->_getFile($fileId);
		$res = $this->_getResource($fileId);
		$groups = array_keys($res);
		sort($groups);
 
		$this->addVar('res', $res);
		$this->addVar('groups', $groups);
		$this->addVar('fileId', $fileId);
		$this->addVar('file', $file);
		$this->addVar('currentTask', $this->_task);
		
		parent::execute();
	}
	
	function _getResource($fileId)
	{
		$res = array();
		$nullValue = null;
		$baseRes = ArisI18NHelper::parseXmlFromFile(ARI_QUIZ_CACHE_DIR . $this->_fileGroup . '/en.xml', $nullValue);
		if ($fileId != 0)
		{
			$file = $this->_fileController->call('getFile', $fileId, $this->_fileGroup);
			$res = ArisI18NHelper::parseXmlFromString($file->Content, $nullValue);
			$res = ArisI18NHelper::mergeResources($baseRes, $res);
		}
		else
		{ 
			$res = $baseRes;
		}

		$groupRes = array($this->_untitledGroup => array());
		foreach ($res as $id => $value)
		{
			$group = $this->_untitledGroup;
			$type = null;
			if (isset($value[ARI_I18N_ATTRS]))
			{
				if (isset($value[ARI_I18N_ATTRS][ARI_I18N_ATTR_GROUP]))
				{
					$group = $value[ARI_I18N_ATTRS][ARI_I18N_ATTR_GROUP];
					if (!isset($groupRes[$group])) $groupRes[$group] = array();
				}
				
				if (isset($value[ARI_I18N_ATTRS]['type']))
				{
					$type = $value[ARI_I18N_ATTRS]['type'];
				}
			}
			
			$groupRes[$group][] = array(
				'id' => $id,
				'message' => $value['message'], 
				'description' => isset($value['description']) ? $value['description'] : '',
				'type' => $type);
		}
		
		if (count($groupRes[$this->_untitledGroup]) == 0)
		{
			unset($groupRes[$this->_untitledGroup]);
		}

		return $groupRes;
	}
	
	function _getFile($fileId)
	{
		$file = null;
		if ($fileId != 0)
		{
			$file = $this->_fileController->call('getFile', $fileId, $this->_fileGroup);
		}
		else
		{
			$file = EntityFactory::createInstance('FileEntity', ARI_ENTITY_GROUP);
		}
		
		return $file;
	}
	
	function _getBaseRes()
	{
		$nullValue = null;
		return ArisI18NHelper::parseXmlFromFile(ARI_QUIZ_CACHE_DIR . $this->_fileGroup . '/en.xml', $nullValue);
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('save', 'clickSave');
		$this->_registerEventHandler('apply', 'clickApply');
		$this->_registerEventHandler('cancel', 'clickCancel');
	}
	
	function clickCancel($eventArgs)
	{
		AriQuizWebHelper::cancelAction($this->_listTask);
	}
	
	function clickSave($eventArgs)
	{
		$template = $this->_saveFile();
		if (!$this->_isError())
		{
			 AriQuizWebHelper::preCompleteAction('Complete.LangSave', array('task' => $this->_listTask));
		}
	}
	
	function clickApply($eventArgs)
	{
		$file = $this->_saveFile();
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.LangSave', 
				array('task' => $this->_task, 'fileId' => $file->FileId, 'hidemainmenu' => 1));
		}
	}

	function _saveFile()
	{
		$baseRes = $this->_getBaseRes();
		$tbxResMessage = JRequest::getVar('tbxResMessage', array());
		$tbxResDescr = JRequest::getVar('tbxResDescr', array());
		
		$merge = ArisI18NHelper::mergeDataResource(
			$baseRes, 
			array('message' => $tbxResMessage, 'description' => $tbxResDescr), 
			array('message', 'description'));
		$xml = ArisI18NHelper::createXmlFromData($merge);
		$xmlStr = AriXmlHelper::toString($xml->document);
		if (strpos($xmlStr, '<?xml') === false)
			$xmlStr = ARI_I18N_TEMPLATE_XML . $xmlStr;

		$user =& JFactory::getUser();
		$ownerId = $user->get('id');
		$reqFields = JRequest::getVar('zTemplate', null);
		$fields = array();
		foreach ($this->_savedIndex as $index)
		{
			$fields[$index] = isset($reqFields[$index])
				? $reqFields[$index]
				: '';
		}

		$fields['Group'] = $this->_fileGroup;
		$fields['Content'] = $xmlStr;
		$fields['Extension'] = 'xml';
		$file = $this->_fileController->call('saveFile',
			JRequest::getInt('fileId'),
			$fields,
			$ownerId);
			
		if ($file) AriFileCache::recacheFile(ARI_QUIZ_CACHE_DIR, $this->_fileGroup, $file->FileId, $file->Extension);

		return $file;
	}
}
?>
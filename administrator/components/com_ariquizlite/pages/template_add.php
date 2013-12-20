<?php
/*
 * ARI Quiz Lite
 *
 * @package		ARI Quiz Lite
 * @version		1.0.0
 * @author		ARI Soft
 * @copyright	Copyright (c) 2009 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');

AriKernel::import('Controllers.FileController');
AriKernel::import('Cache.FileCache');

class template_addAriPage extends AriAdminQuizPageBase 
{
	var $_savedIndex = array('ShortDescription', 'Content');
	var $_fileController;
	
	function _init()
	{
		$this->_fileController = new AriFileController(ARI_QUIZ_FILE_TABLE);
		
		parent::_init();
	}
	
	function execute()
	{
		$fileId = JRequest::getInt('fileId');
		$file = $this->_getFile($fileId);
		
		$this->addVar('fileId', $fileId);
		$this->addVar('file', $file);
		
		parent::execute();
	}
	
	function _getFile($fileId)
	{
		$file = null;
		if ($fileId != 0)
		{
			$file = $this->_fileController->call('getFile', $fileId, ARI_QUIZ_FILE_TEMPLATEGROUP);
		}
		else
		{
			$file = EntityFactory::createInstance('FileEntity', ARI_ENTITY_GROUP);
		}
		
		return $file;
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('save', 'clickSave');
		$this->_registerEventHandler('apply', 'clickApply');
		$this->_registerEventHandler('cancel', 'clickCancel');
	}
	
	function clickCancel($eventArgs)
	{
		AriQuizWebHelper::cancelAction('templates');
	}
	
	function clickSave($eventArgs)
	{
		$template = $this->_saveFile();
		if (!$this->_isError())
		{
			 AriQuizWebHelper::preCompleteAction('Complete.TemplateSave', array('task' => 'templates'));
		}
	}
	
	function clickApply($eventArgs)
	{
		$file = $this->_saveFile();
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.TemplateSave', 
				array('task' => 'template_add', 'fileId' => $file->FileId, 'hidemainmenu' => 1));
		}
	}
	
	function _saveFile()
	{
		global $option;

		$user =& JFactory::getUser(); 
		$ownerId = $user->get('id');
		$reqFields = JRequest::getVar('zTemplate', array());
		$fields = array();
		foreach ($this->_savedIndex as $index)
		{
			$fields[$index] = isset($reqFields[$index])
				? $reqFields[$index]
				: '';
		}

		$fields['Group'] = ARI_QUIZ_FILE_TEMPLATEGROUP;
		$file = $this->_fileController->call('saveFile',
			JRequest::getInt('fileId', 0),
			$fields,
			$ownerId);
		if (!empty($file))
		{
			AriFileCache::saveTextFile($file->Content, 
				JPATH_SITE . '/administrator/components/' . $option . '/cache/files/' . ARI_QUIZ_FILE_TEMPLATEGROUP . '/' . $file->FileId . '.css');
		}
		
		return $file;
	}
}
?>
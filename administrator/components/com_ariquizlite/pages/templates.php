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

class templatesAriPage extends AriAdminQuizPageBase 
{
	var $_fileController;
	
	function _init()
	{
		$this->_fileController = new AriFileController(ARI_QUIZ_FILE_TABLE);
		
		parent::_init();
	}
	
	function execute()
	{
		$templateList = $this->_fileController->call('getFileList', ARI_QUIZ_FILE_TEMPLATEGROUP);
		
		$this->addVar('templateList', $templateList);
		
		parent::execute();
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('delete', 'clickDelete');
	}

	function clickDelete($eventArgs)
	{
		$fileIdList = JRequest::getVar('fileId', array());
		$this->_fileController->call('deleteFile', $fileIdList, ARI_QUIZ_FILE_TEMPLATEGROUP);
		if (!$this->_isError())
		{
			$this->_clearCacheFiles($fileIdList);
			AriQuizWebHelper::preCompleteAction('Complete.TemplateDelete', array('task' => 'templates'));
		}
	}
	
	function _clearCacheFiles($fileIdList)
	{
		global $option; 
		
		if (!empty($fileIdList) && is_array($fileIdList))
		{
			foreach ($fileIdList as $id)
			{
				$id = intval($id, 10);
				@unlink(JPATH_SITE . '/administrator/components/' . $option . '/cache/files/' . ARI_QUIZ_FILE_TEMPLATEGROUP . '/' . $id . '.css');
			}
		}
	}
}
?>
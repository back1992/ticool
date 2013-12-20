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

class texttemplate_listAriPage extends AriAdminQuizPageBase
{
	var $_templateController;
	
	function execute()
	{
		$this->addVar('templateList', $this->_getTemplateList());

		parent::execute();
	}
	
	function _getTemplateList()
	{
		$templateList = $this->_templateController->call('getTemplateList', ARI_QUIZ_RESTEMPLATE_KEY);
		
		return $templateList;
	}
	
	function _init()
	{
		$this->_templateController = new AriTextTemplateController();
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('delete', 'clickDelete');
	}
	
	function clickDelete($eventArgs)
	{
		$this->_templateController->call('deleteTemplate', JRequest::getVar('templateId', 0), ARI_QUIZ_RESTEMPLATE_KEY);
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.CategoryDelete', array('task' => 'texttemplate_list'));
		}
	}
}
?>
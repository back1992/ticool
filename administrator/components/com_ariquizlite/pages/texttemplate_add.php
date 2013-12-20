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

class texttemplate_addAriPage extends AriAdminQuizPageBase
{
	var $_templateController;
	
	function _init()
	{
		$this->_templateController = new AriTextTemplateController();
	}
	
	function execute()
	{
		$templateId = JRequest::getInt('templateId');

		$this->addVar('templateId', $templateId);
		$this->addVar('params', $this->_getParams());
		$this->addVar('template', $this->_getTemplate($templateId));
		
		parent::execute();
	}
	
	function _getParams()
	{
		$params = $this->_templateController->call('getParamsByGroup', ARI_QUIZ_RESTEMPLATE_KEY);
		
		return $params;
	}
	
	function _getTemplate($templateId)
	{
		$template = null;
		if ($templateId != 0)
		{
			$template = $this->_templateController->call('getTemplate', $templateId);
		}
		else
		{
			$template = EntityFactory::createInstance('TextTemplateEntity', ARI_ENTITY_GROUP);
		}
		
		return $template;
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('save', 'clickSave');
		$this->_registerEventHandler('apply', 'clickApply');
		$this->_registerEventHandler('cancel', 'clickCancel');
	}
	
	function clickCancel($eventArgs)
	{
		AriQuizWebHelper::cancelAction('texttemplate_list');
	}
	
	function clickSave($eventArgs)
	{
		$template = $this->_saveTemplate();
		if (!$this->_isError())
		{
			 AriQuizWebHelper::preCompleteAction('Complete.TemplateSave', array('task' => 'texttemplate_list'));
		}
	}
	
	function clickApply($eventArgs)
	{
		$template = $this->_saveTemplate();
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.TemplateSave', 
				array('task' => 'texttemplate_add', 'templateId' => $template->TemplateId, 'hidemainmenu' => 1));
		}
	}
	
	function _saveTemplate()
	{
		$user =& JFactory::getUser();
		$ownerId = $user->get('id');
		$fields = JRequest::getVar('zTemplate', array(), 'default', 'none', JREQUEST_ALLOWRAW);

		return $this->_templateController->call('saveTemplate',
			JRequest::getInt('templateId'),
			$fields,
			ARI_QUIZ_RESTEMPLATE_KEY,
			$ownerId);
	}	
}
?>
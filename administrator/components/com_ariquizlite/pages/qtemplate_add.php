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

class qtemplate_addAriPage extends AriAdminQuizPageBase 
{
	function execute()
	{
		$templateId = JRequest::getInt('templateId');
		$question = null;
		$className = null;
		$questionTypeList = $this->_quizController->call('getQuestionTypeList', true);
		$questionTypeId = JRequest::getInt('questionTypeId');
		if ($templateId == 0)
		{
			$template = EntityFactory::createInstance('QuestionTemplateEntity', ARI_ENTITY_GROUP);
		}
		else 
		{
			$template = $this->_quizController->call('getQuestionTemplate', $templateId);
			if (empty($questionTypeId))
			{ 
				$questionTypeId = $template->QuestionTypeId;
			}
		}

		if (empty($questionTypeId))
		{ 
			$questionTypeId = $questionTypeList[0]->QuestionTypeId;  
		}
		
		foreach ($questionTypeList as $qt)
		{
			if ($qt->QuestionTypeId == $questionTypeId)
			{
				$className = $qt->ClassName;
				break;
			}
		}

		$specificQuestion = EntityFactory::createInstance($className, ARI_QUESTIONENTITY_GROUP);
		$questionData = $template->Data;
		
		$this->addVar('templateId', $templateId);
		$this->addVar('questionTypeId', $questionTypeId);
		$this->addVar('template', $template);
		$this->addVar('questionTypeList', $questionTypeList);
		$this->addVar('className', $className);
		$this->addVar('specificQuestion', $specificQuestion);
		$this->addVar('questionData', $questionData);
		
		parent::execute();
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('save', 'clickSave');
		$this->_registerEventHandler('apply', 'clickApply');
		$this->_registerEventHandler('cancel', 'clickCancel');
	}
	
	function clickCancel($eventArgs)
	{
		AriQuizWebHelper::cancelAction('qtemplate_list');
	}
	
	function clickSave($eventArgs)
	{
		$template = $this->_saveTemplate();
		if (!$this->_isError())
		{
			 AriQuizWebHelper::preCompleteAction('Complete.QTemplateSave', array('task' => 'qtemplate_list'));
		}				
	}
	
	function clickApply($eventArgs)
	{
		$template = $this->_saveTemplate();
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.QTemplateSave', 
				array('task' => 'qtemplate_add', 'templateId' => $template->TemplateId, 'hidemainmenu' => 1));
		}
	}
	
	function _saveTemplate()
	{
		$user =& JFactory::getUser();
		$ownerId = $user->get('id');
		$fields = JRequest::getVar('zQuiz');
		
		$questionTypeId = JRequest::getString('questionTypeId', '');
		$questionType = $this->_quizController->call('getQuestionType', $questionTypeId);
		$questionObj = EntityFactory::createInstance($questionType->ClassName, ARI_QUESTIONENTITY_GROUP);
		$data = $questionObj->getXml();

		return $this->_quizController->call('saveQuestionTemplate',
			JRequest::getInt('templateId'), 
			$questionTypeId,
			$ownerId, 
			$fields,
			$data);
	}
}
?>
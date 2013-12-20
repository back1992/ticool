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

class quiz_addAriPage extends AriAdminQuizPageBase
{
	var $_fileController;
	
	function _init()
	{
		$this->_fileController = new AriFileController(ARI_QUIZ_FILE_TABLE);
		
		parent::_init();
	}
	
	function execute()
	{
		$quizId = JRequest::getInt('quizId');

		$this->addVar('groupsTree', $this->_getAccessTree());
		$this->addVar('quizId', $quizId);
		$this->addVar('categoryList', $this->_getCategoryList());
		$this->addVar('quiz', $this->_getQuiz($quizId));
		$this->addVar('textTemplateList', $this->_getTextTemplateList());
		$this->addVar('quizTextTemplates', $this->_getQuizTextTemplateList($quizId));
		$this->addVar('cssTemplates', $this->_getCssTemplateList());
		
		parent::execute();
	}
	
	function _getCssTemplateList()
	{		
		$cssTemplates = $this->_fileController->call('getFileList', ARI_QUIZ_FILE_TEMPLATEGROUP);
		if (!empty($cssTemplates))
		{
			foreach ($cssTemplates as $key => $template)
			{
				$cssTemplates[$key]->ShortDescription = $template->ShortDescription;
			}
		}
		else
		{
			$cssTemplates = array();
		}
		
		$emptyTemplate = new stdClass();
		$emptyTemplate->FileId = 0;
		$emptyTemplate->ShortDescription = AriQuizWebHelper::getResValue('Label.NotSelectedItem');
		array_unshift($cssTemplates, $emptyTemplate);
		
		return $cssTemplates;
	}
	
	function _getAccessTree()
	{
		$accessTree = null;
		
		$version = new JVersion();
		$j15 = version_compare($version->getShortVersion(), '1.6.0', '<');
		
		if ($j15)
		{
			$acl =& JFactory::getACL();
			$uah = new UserAccessHelper($acl);
			$accessTree = $uah->getGroupsFlatTree(UAH_REGISTERED_GROUP);
		}
		else 
		{
			$accessItem = new stdClass();
			$accessItem->value = 2;
			$accessItem->text = 'Registered';
			
			$accessTree = array($accessItem);
		}
		
		$emptyGroup = new stdClass();
		$emptyGroup->value = 0;
		$emptyGroup->text = AriQuizWebHelper::getResValue('Label.Guest');
		if (empty($accessTree)) $accessTree = array();
		
		array_unshift($accessTree, $emptyGroup);
		
		return $accessTree;
	}
	
	function _getCategoryList()
	{		
		$categoryList = $this->_quizController->call('getCategoryList');
		if (!empty($categoryList))
		{
			foreach ($categoryList as $key => $category)
			{
				$categoryList[$key]->CategoryName = $category->CategoryName;
			}
		}
		else 
		{
			$categoryList = array();
		}
		
		$emptyCategory = new stdClass();
		$emptyCategory->CategoryId = 0;
		$emptyCategory->CategoryName = AriQuizWebHelper::getResValue('Label.NotSelectedItem');
		array_unshift($categoryList, $emptyCategory);
		
		return $categoryList;
	}

	function _getQuizTextTemplateList($quizId)
	{
		$templateList = array();
		if ($quizId > 0)
		{
			$templateController = new AriTextTemplateController();
			$templateList = $templateController->call('getEntitySingleTemplate', ARI_QUIZ_ENTITY_KEY, $quizId);
		}
		
		return $templateList;
	}
	
	function _getTextTemplateList()
	{
		$templateController = new AriTextTemplateController();
		$templateList = $templateController->call('getTemplateList', ARI_QUIZ_RESTEMPLATE_KEY);
		if (!empty($templateList))
		{
			foreach ($templateList as $key => $template)
			{
				$templateList[$key]->TemplateName = $template->TemplateName;
			}
		}
		else 
		{
			$templateList = array();
		}
		
		$emptyTemplate = new stdClass();
		$emptyTemplate->TemplateId = 0;
		$emptyTemplate->TemplateName = AriQuizWebHelper::getResValue('Label.NotSelectedItem');
		array_unshift($templateList, $emptyTemplate);
		
		return $templateList;
	}
	
	function _getQuiz($quizId)
	{
		$quiz = null;
		if ($quizId != 0)
		{
			$quiz = $this->_quizController->call('getQuiz', $quizId);
		}
		else
		{
			$quiz = EntityFactory::createInstance('QuizEntity', ARI_ENTITY_GROUP);
		}
		
		return $quiz;
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('save', 'clickSave');
		$this->_registerEventHandler('apply', 'clickApply');
		$this->_registerEventHandler('cancel', 'clickCancel');
	}
	
	function clickCancel($eventArgs)
	{
		AriQuizWebHelper::cancelAction('quiz_list');
	}
	
	function clickSave($eventArgs)
	{
		$quiz = $this->_saveQuiz();
		if (!$this->_isError())
		{
			 AriQuizWebHelper::preCompleteAction('Complete.QuizSave', array('task' => 'quiz_list'));
		}				
	}
	
	function clickApply($eventArgs)
	{
		$quiz = $this->_saveQuiz();
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.QuizSave', 
					array('task' => 'quiz_add', 'quizId' => $quiz->QuizId, 'hidemainmenu' => 1));
		}
	}
	
	function _saveQuiz()
	{
		$user =& JFactory::getUser();
		$ownerId = $user->get('id');
		$fields = JRequest::getVar('zQuiz', array(), 'default', 'none', JREQUEST_ALLOWRAW);

		return $this->_quizController->call('saveQuiz',
			JRequest::getInt('quizId'),
			$fields, 
			$ownerId,
			JRequest::getVar('Category', array()),
			JRequest::getVar('AccessGroup', array()),
			JRequest::getVar('zTextTemplate', array()));
	}
}
?>
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

class question_addAriPage extends AriAdminQuizPageBase
{
	var $_questionTypeId;
	var $_fileController;
	
	function _init()
	{
		$this->_fileController = new AriFileController(ARI_QUIZ_FILE_TABLE);
		
		parent::_init();
	}
	
	function execute()
	{
		global $option;
		
		$mainframe =& JFactory::getApplication();
		
		$questionTypeId = $this->_questionTypeId;
		$templateId = JRequest::getInt('templateId');
		$questionId = JRequest::getInt('questionId');
		$quizId = null;
		$question = null;
		$className = null;
		$questionData = null;
		$questionTypeList = $this->_quizController->call('getQuestionTypeList');
		if ($questionId == 0)
		{
			$quizId = JRequest::getInt('quizId');
			$question = EntityFactory::createInstance('QuestionEntity', ARI_ENTITY_GROUP);
		}
		else 
		{
			$question = $this->_quizController->call('getQuestion', $questionId);
			$quizId = $question->QuizId;
			if (empty($questionTypeId))
			{ 
				$questionTypeId = $question->QuestionVersion->QuestionTypeId;
			}
			
			if ($question->Status == ARI_QUIZ_QUE_STATUS_DELETE)
			{
				$mainframe->redirect('index.php?option=' . $option . '&task=question_list&quizId=' . $quizId);
			}
		}
		
		$question = $this->_bindQuestionFromRequest($question);
		if (empty($questionTypeId))
		{
			$qt = JRequest::getVar('questionTypeId', null);
			if (!is_null($qt)) $questionTypeId = $qt;
		}
		
		if ($templateId > 0)
		{
			$template = $this->_quizController->call('getQuestionTemplate', $templateId);
			if (!empty($template) && $template->QuestionTypeId > 0)
			{
				$questionTypeId = $template->QuestionTypeId;
				$questionData = $template->Data;
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
		
		$quiz = $this->_quizController->call('getQuiz', $quizId);
		$specificQuestion = EntityFactory::createInstance($className, ARI_QUESTIONENTITY_GROUP);
		if (empty($questionData))
		{
			$questionData = $question->QuestionVersion->Data;
		}

		$this->addVar('quiz', $quiz);
		$this->addVar('quizId', $quizId);
		$this->addVar('question', $question);
		$this->addVar('questionId', $questionId);
		$this->addVar('className', $className);
		$this->addVar('questionData', $questionData);
		$this->addVar('specificQuestion', $specificQuestion);
		$this->addVar('questionTypeId', $questionTypeId);
		$this->addVar('questionTypeList', $questionTypeList);
		$this->addVar('categoryList', $this->_getCategoryList($quizId));
		$this->addVar('templateList', $this->_getTemplateList());
				
		parent::execute();
	}
	
	function _bindQuestionFromRequest($question)
	{
		if (empty($question)) return $question;
		
		$zQuiz = JRequest::getVar('zQuiz', array());
		if (empty($zQuiz)) return $question;

		$val = JArrayHelper::getValue($zQuiz, 'QuestionCategoryId', null, '');
		if (!is_null($val)) $question->QuestionVersion->QuestionCategoryId = $val;

		$val = JArrayHelper::getValue($zQuiz, 'Score', null, '');
		if (!is_null($val)) $question->QuestionVersion->Score = $val;
		
		$val = JArrayHelper::getValue($zQuiz, 'Question', null, '');
		if (!is_null($val)) $question->QuestionVersion->Question = $val;
		
		return $question;
	}
	
	function _getCategoryList($quizId)
	{
		$categoryList = $this->_quizController->call('getQuestionCategoryList', null, $quizId);
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
		$emptyCategory->QuestionCategoryId = 0;
		$emptyCategory->CategoryName = AriQuizWebHelper::getResValue('Label.NotSelectedItem');
		array_unshift($categoryList, $emptyCategory);

		return $categoryList;
	}
	
	function _getTemplateList()
	{
		$templateList = $this->_quizController->call('getQuestionTemplateList', null);
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
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('save', 'clickSave');
		$this->_registerEventHandler('apply', 'clickApply');
		$this->_registerEventHandler('cancel', 'clickCancel');
		$this->_registerEventHandler('apply_qtype', 'clickApplyType');
	}
	
	function clickApplyType()
	{
		$this->_questionTypeId = JRequest::getString('questionTypeId', '');
	}
	
	function clickCancel($eventArgs)
	{
		$questionId = JRequest::getInt('questionId');
		$quizId = 0;
		if ($questionId == 0)
		{
			$quizId = JRequest::getInt('quizId');
		}
		else 
		{
			$question = $this->_quizController->call('getQuestion', $questionId);
			if ($question) $quizId = $question->QuizId;
		}
		
		AriQuizWebHelper::cancelAction('question_list', array('quizId' => $quizId));
	}
	
	function clickSave($eventArgs)
	{
		$question = $this->_saveQuestion();
		if (!$this->_isError())
		{
			 AriQuizWebHelper::preCompleteAction('Complete.QuestionSave', array('task' => 'question_list', 'quizId' => $question->QuizId));
		}				
	}
	
	function clickApply($eventArgs)
	{
		$question = $this->_saveQuestion();
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.QuestionSave', 
				array('task' => 'question_add', 'questionId' => $question->QuestionId, 'quizId' => $quizId, 'hidemainmenu' => 1));
		}
	}
	
	function _saveQuestion()
	{
		$user =& JFactory::getUser();
		$ownerId = $user->get('id');
		$quizId = JRequest::getInt('quizId');
		$questionTypeId = JRequest::getString('questionTypeId', '');
		
		$fields = JRequest::getVar('zQuiz', array(), 'default', 'none', JREQUEST_ALLOWRAW);
		$questionType = $this->_quizController->call('getQuestionType', $questionTypeId);
		$questionObj = EntityFactory::createInstance($questionType->ClassName, ARI_QUESTIONENTITY_GROUP);
		$data = $questionObj->getXml();
		
		return $this->_quizController->call('saveQuestion',
			JRequest::getInt('questionId'),
			$quizId, 
			$questionTypeId, 
			$ownerId, 
			$fields,
			$data);
	}
}
?>
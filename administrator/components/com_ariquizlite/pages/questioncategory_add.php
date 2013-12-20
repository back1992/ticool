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

class questioncategory_addAriPage extends AriAdminQuizPageBase 
{
	function execute()
	{
		$qCategoryId = JRequest::getInt('qCategoryId');
		$category = EntityFactory::createInstance('QuestionCategoryEntity', ARI_ENTITY_GROUP);
		$isUpdate = false;
		$quizList = null;
		$quizId = 0;
		$isNotQuiz = false;
		if ($qCategoryId > 0)
		{
			$category = $this->_quizController->call('getQuestionCategory', $qCategoryId, true);
			$quizId = $category->Quiz->QuizId;
			$isUpdate = true;
		}
		else 
		{
			$quizId = JRequest::getInt('quizId');
			$category->Quiz = $this->_quizController->call('getQuiz', $quizId);
			$quizList = $this->_getQuizList();
			$isNotQuiz = empty($quizList) || count($quizList) == 0;			
		}
		
		if ($isNotQuiz)
		{
			AriQuizWebHelper::preCompleteAction('Warning.QCategoryCreateQuiz', 
				array('task' => 'questioncategory_list', 'quizId' => $quizId));
		}
		
		$this->addVar('category', $category);
		$this->addVar('isUpdate', $isUpdate);
		$this->addVar('quizId', $quizId);
		$this->addVar('qCategoryId', $qCategoryId);
		$this->addVar('quizList', $quizList);
		
		parent::execute();
	}
	
	function _getQuizList()
	{
		$quizList = $this->_quizController->call('getQuizList');
		if (!empty($quizList))
		{
			foreach ($quizList as $key => $quiz)
			{
				$quizList[$key]->QuizName = $quiz->QuizName;
			}
		}
		
		return $quizList;
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('save', 'clickSave');
		$this->_registerEventHandler('apply', 'clickApply');
		$this->_registerEventHandler('cancel', 'clickCancel');
	}
	
	function clickCancel($eventArgs)
	{
		AriQuizWebHelper::cancelAction('questioncategory_list', array('quizId' => JRequest::getInt('quizId')));
	}
	
	function clickSave($eventArgs)
	{
		$category = $this->_saveCategory();
		if (!$this->_isError())
		{
			$quizId = JRequest::getInt('quizId');
			AriQuizWebHelper::preCompleteAction('Complete.QCategorySave', array('task' => 'questioncategory_list', 'quizId' => $quizId));
		}				
	}
	
	function clickApply($eventArgs)
	{
		$category = $this->_saveCategory();
		if (!$this->_isError())
		{
			$quizId = JRequest::getInt('quizId');
			AriQuizWebHelper::preCompleteAction('Complete.QCategorySave', 
				array('task' => 'questioncategory_add', 'qCategoryId' => $category->QuestionCategoryId, 'quizId' => $quizId, 'hidemainmenu' => 1));
		}
	}
	
	function _saveCategory()
	{
		$user =& JFactory::getUser();
		$ownerId = $user->get('id');
		$fields = JRequest::getVar('zCategory');
		$quizId = JRequest::getInt('quizId');

		return $this->_quizController->call('saveQuestionCategory',
			JRequest::getInt('qCategoryId'),
			$fields,
			$quizId, 
			$ownerId);
	}	
}
?>
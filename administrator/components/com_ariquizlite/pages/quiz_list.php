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

jimport('joomla.html.pagination');

class quiz_listAriPage extends AriAdminQuizPageBase 
{	
	function execute()
	{
		$limit = AriQuizHelper::_getLimit('quizlist'); 
		$limitStart = AriQuizHelper::_getLimitStart('quizlist');
		$sortInfo = ArisSortingHelper::getCurrentSorting('QuizName');		
		$categoryList = $this->_getCategoryList();

		// create filters
		$quizFilters = $this->_createFilters($categoryList);
		$filterStatus = $quizFilters->getFilterValue('Status', null);
		$filterCategoryId = $quizFilters->getFilterValue('CategoryId', null);
		
		$quizCount = $this->_quizController->call('getQuizCount', $filterCategoryId, $filterStatus);
		if ($quizCount <= $limitStart)
		{
			$limitStart = 0;
			AriQuizHelper::setLimitStart($limitStart, 'quizlist');
		}
		//print_r($sortInfo);exit();
		$quizList = $this->_quizController->call('getQuizList', $filterCategoryId, $filterStatus, $sortInfo, $limitStart, $limit);
		if ($this->_isError())
		{
			return ; 
		}

		$pageNav = new JPagination($quizCount, $limitStart, $limit);
		
		$this->addVar('quizList', $quizList);
		$this->addVar('categoryList', $categoryList); 
		$this->addVar('quizFilters', $quizFilters);
		$this->addVar('pageNav', $pageNav);

		parent::execute();
	}
	
	function _getCategoryList()
	{
		$categoryList = $this->_quizController->call('getCategoryList');
		if (!empty($categoryList))
		{
			foreach ($categoryList as $category)
			{
				$category->CategoryName = $category->CategoryName;
			}
		}
		
		return $categoryList;
	}
	
	function _createFilters($categoryList)
	{
		$filters = new ArisFilterContainer();
		$filters->addFilter('CategoryId', $categoryList, AriQuizWebHelper::getResValue('Label.AllCategory', true), 'CategoryId', 'CategoryName');
		$filters->addFilter('Status', array(ARI_QUIZ_STATUS_ACTIVE => 'Active', ARI_QUIZ_STATUS_INACTIVE => 'Inactive'), AriQuizWebHelper::getResValue('Label.AllStatus'));

		return $filters;
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('activate', 'clickActivate');
		$this->_registerEventHandler('deactivate', 'clickDeactivate');
		$this->_registerEventHandler('delete', 'clickDelete');
		$this->_registerEventHandler('questions', 'clickQuestions');
	}
	
	function clickQuestions()
	{
		$quizId = JRequest::getVar('quizId');
		if (is_array($quizId) && count($quizId) > 0)
			$quizId = $quizId[0];
			
		$quizId = intval($quizId, 10);
		
		if ($quizId > 0)
		{
			$mainframe =& JFactory::getApplication();
			$mainframe->redirect('index.php?option=com_ariquizlite&task=question_list&quizId=' . $quizId);
		}
	}
	
	function clickActivate($eventArgs)
	{
		$this->_quizController->call('activateQuiz', JRequest::getVar('quizId', 0));
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.QuizActivate');
		}		
	}
	
	function clickDeactivate($eventArgs)
	{
		$this->_quizController->call('deactivateQuiz', JRequest::getVar('quizId', 0));
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.QuizDeactivate');
		}
	}
	
	function clickDelete($eventArgs)
	{
		$this->_quizController->call('deleteQuiz', JRequest::getVar('quizId', 0));
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.QuizDelete', array('task' => 'quiz_list'));
		}
	}
}
?>
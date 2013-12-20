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

class question_listAriPage extends AriAdminQuizPageBase
{
	function execute()
	{
		$quizId = JRequest::getInt('quizId');
		$limit = AriQuizHelper::_getLimit('quelist'); 
		$limitStart = AriQuizHelper::_getLimitStart('quelist');
		$sortInfo = ArisSortingHelper::getCurrentSorting('Question');
		$questionCount = $this->_quizController->call('getQuestionCount', $quizId);
		if ($questionCount <= $limitStart)
		{
			$limitStart = 0;
			AriQuizHelper::setLimitStart($limitStart, 'quelist');
		}
		
		$questionList = $this->_quizController->call('getQuestionList', $sortInfo, $quizId, $limitStart, $limit);
		if ($this->_isError())
		{
			return ; 
		}

		$this->addVar('questionList', $questionList);
		$this->addVar('quizId', $quizId);
		$this->addVar('pageNav', new JPagination($questionCount, $limitStart, $limit));
		
		parent::execute();
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('delete', 'clickDelete');
		$this->_registerEventHandler('orderup', 'clickOrderUp');
		$this->_registerEventHandler('orderdown', 'clickOrderDown');
	}
	
	function clickOrderUp($eventArgs)
	{
		$this->_changeQuestionOrder(-1);
	}
	
	function clickOrderDown($eventArgs)
	{
		$this->_changeQuestionOrder(1);
	}
	
	function _changeQuestionOrder($dir)
	{		
		$quizId = JRequest::getInt('quizId');
		$questionId = JRequest::getVar('questionId', array());
		if (is_array($questionId)) $questionId = $questionId[0];
		$questionId = intval($questionId);
		
		$this->_quizController->call('changeQuestionOrder', $questionId, $dir);

		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.ChangeQuestionOrder', 
				array('task' => 'question_list', 'quizId' => $quizId));
		}
	}
	
	function clickDelete($eventArgs)
	{
		$this->_quizController->call('deleteQuestion', JRequest::getVar('questionId', 0));
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.QuestionDelete', 
				array('task' => 'question_list', 'quizId' => JRequest::getInt('quizId')));
		}
	}
}
?>
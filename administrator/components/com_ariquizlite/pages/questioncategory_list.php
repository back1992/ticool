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

class questioncategory_listAriPage extends AriAdminQuizPageBase 
{
	function execute()
	{
		$quizId = JRequest::getInt('quizId');
		$limit = AriQuizHelper::_getLimit('qcatlist'); 
		$limitStart = AriQuizHelper::_getLimitStart('qcatlist');
		$sortInfo = ArisSortingHelper::getCurrentSorting('CategoryName');
		$categoryCount = $this->_quizController->call('getQuestionCategoryCount', $quizId);
		if ($categoryCount <= $limitStart)
		{
			$limitStart = 0;
			AriQuizHelper::setLimitStart($limitStart, 'qcatlist');
		}
		
		$categoryList = $this->_quizController->call('getQuestionCategoryList', $sortInfo, $quizId, $limitStart, $limit);
		if ($this->_isError())
		{
			return ; 
		}

		$this->addVar('pageNav', new JPagination($categoryCount, $limitStart, $limit));
		$this->addVar('categoryList', $categoryList);
		$this->addVar('quizId', $quizId);
				
		parent::execute();
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('delete', 'clickDelete');
	}
	
	function clickDelete($eventArgs)
	{
		$this->_quizController->call('deleteQuestionCategory',
			JRequest::getVar('questionCategoryId', null),
			JRequest::getBool('zq_deleteQuestions', false));
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.QuestionCategoryDelete', array('task' => 'questioncategory_list', 'quizId' => JRequest::getString('quizId', '')));
		}
	}
}
?>
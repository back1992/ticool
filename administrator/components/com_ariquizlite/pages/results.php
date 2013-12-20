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

class resultsAriPage extends AriAdminQuizPageBase 
{
	var $_resultController;
	
	function _init()
	{
		$this->_resultController = new AriQuizResultController();
		
		parent::_init();
	}
	
	function execute()
	{
		$quizId = JRequest::getInt('quizId');
		$limit = AriQuizHelper::_getLimit('results'); 
		$limitStart = AriQuizHelper::_getLimitStart('results');
		$sortInfo = ArisSortingHelper::getCurrentSorting('Name');
		
		// create filters
		$resultFilters = $this->_createFilters($quizId);
		$filterUserId = $resultFilters->getFilterValue('UserId', null);
		$filterQuizId = $resultFilters->getFilterValue('QuizId', $quizId);
		$resultCount = $this->_resultController->call('getResultCount', $filterQuizId, $filterUserId);
		if ($resultCount <= $limitStart)
		{
			$limitStart = 0;
			AriQuizHelper::setLimitStart($limitStart, 'results');
		}

		$results = $this->_resultController->call('getResults', $sortInfo, $limitStart, $limit, $filterQuizId, $filterUserId);
		if ($this->_isError())
		{
			return ; 
		}
		
		$this->addVar('quizId', $quizId);
		$this->addVar('results', $results);
		$this->addVar('resultFilters', $resultFilters);
		$this->addVar('pageNav', new JPagination($resultCount, $limitStart, $limit));
		
		parent::execute();
	}
	
	function _createFilters($quizId)
	{
		$resultFilters = new ArisFilterContainer();
		if (empty($quizId))
		{
			$quizList = $this->_resultController->call('getFinishedQuizList');
			$resultFilters->addFilter('QuizId', $quizList, AriQuizWebHelper::getResValue('Label.AllQuiz'), 'QuizId', 'QuizName');
		}
		$userList = $this->_resultController->call('getFinishedUserList', $quizId, true, array('Anonymous' => AriQuizWebHelper::getResValue('Label.Guest')));
		$resultFilters->addFilter('UserId', $userList, AriQuizWebHelper::getResValue('Label.AllUser'), 'Id', 'Name', ARISFILTER_TYPE_SELECT, false, -1);
		
		return $resultFilters;
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('tohtml', 'clickToHtml');
		$this->_registerEventHandler('toword', 'clickToWord');
		$this->_registerEventHandler('toexcel', 'clickToExcel');
		$this->_registerEventHandler('tocsv', 'clickToCSV');
	}
	
	function clickToCSV($eventArgs)
	{
		$statisticsId = JRequest::getVar('statisticsInfoId', array());

		$result = $this->_resultController->call('getCSVView', $statisticsId,
			array('Anonymous' => AriQuizWebHelper::getResValue('Label.Guest'),
			'Passed' => AriQuizWebHelper::getResValue('Label.Passed'),
			'NoPassed' => AriQuizWebHelper::getResValue('Label.NoPassed')),
			AriQuizWebHelper::getShortPeriods());
		ArisResponse::sendContentAsAttach($result,
			sprintf('result.csv'));
		exit();
	}	
}
?>
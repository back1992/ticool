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

class questionAriPage extends AriAdminQuizPageBase 
{
	var $_resultController;
	
	function _init()
	{
		$this->_resultController = new AriQuizResultController();
		
		parent::_init();
	}

	function execute()
	{
		$statisticsId = JRequest::getInt('sid');
		$statistics = $this->_quizController->call('getStatistics', $statisticsId, true);
		$className = '';
		$specificQuestion = null;
		if (!empty($statistics))
		{
			$questionType = $this->_quizController->call('getQuestionType', $statistics->Question->QuestionVersion->QuestionTypeId);
			$className = $questionType->ClassName;
			$specificQuestion = EntityFactory::createInstance($className, ARI_QUESTIONENTITY_GROUP); 
		}
		
		$this->addVar('statistics', $statistics);
		$this->addVar('className', $className);
		$this->addVar('specificQuestion', $specificQuestion);
		$this->addVar('statisticsId', $statisticsId);
		
		parent::execute();
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('next', 'clickNext');
		$this->_registerEventHandler('prev', 'clickPrev');
	}
	
	function clickNext($eventArgs)
	{
		global $option;
		
		$mainframe =& JFactory::getApplication();
		
		$statisticsId = JRequest::getInt('sid');
		$nextStatisticsId = $this->_resultController->call('getNextFinishedQuestion', $statisticsId);
		$mainframe->redirect('index.php?option=' . $option . '&task=question&sid=' . $nextStatisticsId);
	}
	
	function clickPrev($eventArgs)
	{
		global $option;
		
		$mainframe =& JFactory::getApplication();
		
		$statisticsId = JRequest::getInt('sid');
		$nextStatisticsId = $this->_resultController->call('getPrevFinishedQuestion', $statisticsId);
		$mainframe->redirect('index.php?option=' . $option . '&task=question&sid=' . $nextStatisticsId);
	}
}
?>
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

AriKernel::import('Web.Utils.Util');
AriKernel::import('Controllers.FileController');
AriKernel::import('Cache.FileCache');

class questionAriPage extends AriUserQuizPageBase
{
	function execute()
	{
		$this->_loadQuestion();
		
		parent::execute();
	}
	
	function _loadQuestion($ticketId = null)
	{
		global $option, $Itemid;
		
		$mainframe =& JFactory::getApplication();
		
		$user =& JFactory::getUser();
		if (empty($ticketId)) $ticketId = JRequest::getString('ticketId', '');
		$userId = $user->get('id');
		
		if (!AriQuizFrontHelper::_checkQuizAvailability($userId, $ticketId)) return ;

		$statistics = $this->_quizController->call('getNextQuestion', $ticketId);
		if (empty($statistics) || empty($statistics->StatisticsId))
		{
			$isQuizFinished = $this->_quizController->call('isQuizFinished', $ticketId);
			if ($isQuizFinished)
			{
				$mainframe->redirect(
					AriQuizFrontHelper::addTmplToLink('index.php?option=' . $option . '&task=quiz_finished&ticketId=' . $ticketId . '&Itemid=' . $Itemid));
				exit();
			}
			else 
			{
				$mainframe->redirect(
					AriQuizFrontHelper::addTmplToLink('index.php?option=' . $option . '&task=quiz_list&ItemId=' . $Itemid));
				exit();
			}
		}

		if (empty($statistics->StartDate))
		{
			$this->_quizController->call('setStatisticsStart', $statistics->StatisticsId, ArisUtils::getIP());
		}

		$quizInfo = $this->_quizController->call('getUserQuizInfo', $ticketId);
		$completedCount = $this->_quizController->call('getUserCompletedQuestion', $ticketId);
		$questionVersionId = $statistics->Question->QuestionVersionId;
		$questionVersion = $statistics->Question->QuestionVersion;
		$questionEntity = EntityFactory::createInstance($questionVersion->QuestionType->ClassName, ARI_QUESTIONENTITY_GROUP);
		$questionData = $questionEntity->getDataFromXml($questionVersion->Data);
		$questionTime = null; 
		if (!empty($statistics->QuestionTime))
		{
			$questionTime = !empty($statistics->StartDate)
				? strtotime($statistics->StartDate) + $statistics->QuestionTime - strtotime(ArisDate::getDbUTC()) - $statistics->UsedTime 
				: $statistics->QuestionTime - $statistics->UsedTime;
				
			if ($questionTime < 0)
			{
				$this->_loadQuestion($ticketId);
			}
		}
		
		$questionCount = $quizInfo->QuestionCount;
		$progressPercent = $questionCount != 0 ? floor((100 * $completedCount) / $questionCount) : 0;
		$totalTime = null;
		if ($quizInfo->TotalTime)
		{
			$totalTime = $quizInfo->TotalTime - $quizInfo->Now + $quizInfo->StartDate;
		}

		$this->_includeCssFile($quizInfo);
		
		$this->addVar('totalTime', $totalTime);
		$this->addVar('completedCount', $completedCount);
		$this->addVar('progressPercent', $progressPercent);
		$this->addVar('ticketId', $ticketId);
		$this->addVar('questionVersion', $questionVersion);
		$this->addVar('questionVersionId', $questionVersionId);
		$this->addVar('questionTime', $questionTime);
		$this->addVar('quizInfo', $quizInfo);
		$this->addVar('statistics', $statistics);
		$this->addVar('questionData', $questionData);
	}
	
	function _includeCssFile($quizInfo)
	{
		global $option;
		
		$siteUrl = JURI::root(true);
		$cssFilePath = 'components/' . $option . '/css/';
		$cssFile = 'default.css';
		$cacheDir = JPATH_SITE . '/administrator/components/' . $option . '/cache/files/' . ARI_QUIZ_FILE_TEMPLATEGROUP . '/';
		$webCacheDir = $siteUrl . '/administrator/components/' . $option . '/cache/files/' . ARI_QUIZ_FILE_TEMPLATEGROUP . '/';
		if (!empty($quizInfo) && $quizInfo->CssTemplateId)
		{
			$fileName = $cacheDir . $quizInfo->CssTemplateId . '.css';
			$isExists = file_exists($fileName);
			if (!$isExists)
			{
				$fileController = new AriFileController(ARI_QUIZ_FILE_TABLE);
				$file = $fileController->getFile($quizInfo->CssTemplateId, ARI_QUIZ_FILE_TEMPLATEGROUP);
				if (!empty($file))
				{
					AriFileCache::saveTextFile($file->Content, $fileName);
					$isExists = file_exists($fileName);
				}
			}
			
			if ($isExists)
			{
				$cssFilePath = 'administrator/components/' . $option . '/cache/files/' . ARI_QUIZ_FILE_TEMPLATEGROUP . '/';
				$cssFile = $quizInfo->CssTemplateId . '.css';
			}
		}
		
		$doc = JFactory::getDocument();
		$doc->addStyleSheet($cssFilePath . '/' . $cssFile);
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('skip', 'clickSkip');
		$this->_registerEventHandler('save', 'clickSave');
	}
	
	function clickSkip($eventArgs)
	{
		global $option, $Itemid;
		
		$mainframe =& JFactory::getApplication();

		$user =& JFactory::getUser();
		$userId = $user->get('id');
		$skipDate = ArisDate::getDbUTC();
		$ticketId = JRequest::getString('ticketId', '');

		if (!AriQuizFrontHelper::_checkQuizAvailability($userId, $ticketId)) return ;
		
		$qid = JRequest::getInt('qid');
		$statistics = $this->_quizController->call('getCurrentQuestion', $ticketId);
		if (!empty($statistics) && $statistics->Question->QuestionId == $qid)
		{
			$quiz = $this->_quizController->call('getQuizByTicketId', $ticketId);
			if (!empty($quiz) && !empty($quiz->QuizId) && $quiz->CanSkip)
			{
				$this->_quizController->call('skipQuestion', $statistics->StatisticsId, $skipDate);				
			}
		}

		$mainframe->redirect(
			AriQuizFrontHelper::addTmplToLink('index.php?option=' . $option . '&task=question&ticketId=' . $ticketId . '&t=' . uniqid('uv_') . '&Itemid=' . $Itemid));
	}
	
	function clickSave($eventArgs)
	{
		global $option, $Itemid;
		
		$mainframe =& JFactory::getApplication();

		$user =& JFactory::getUser();
		$userId = $user->get('id');
		$ticketId = JRequest::getString('ticketId', '');
		if (!AriQuizFrontHelper::_checkQuizAvailability($userId, $ticketId)) return ;
		
		$qid = JRequest::getInt('qid');
		$statistics = $this->_quizController->call('getCurrentQuestion', $ticketId);
		if (!empty($statistics) && $statistics->Question->QuestionId == $qid)
		{
			$questionVersion = $statistics->Question->QuestionVersion;
			$questionEntity = EntityFactory::createInstance($questionVersion->QuestionType->ClassName, ARI_QUESTIONENTITY_GROUP);
			$statistics->EndDate = ArisDate::getDbUTC();
			$statistics->Data = $questionEntity->getFrontXml();
			$statistics->Score = $questionEntity->isCorrect($statistics->Data, $questionVersion->Data) ? $questionVersion->Score : 0;
	
			$this->_quizController->call('updateStatisticsInfo', $statistics);
		}

		$mainframe->redirect(
			AriQuizFrontHelper::addTmplToLink('index.php?option=' . $option . '&task=question&ticketId=' . $ticketId . '&t=' . uniqid('uv_') . '&Itemid=' . $Itemid));
	}
}
?>

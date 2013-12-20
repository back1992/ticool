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

class get_ticketAriPage extends AriUserQuizPageBase
{	
	function execute()
	{
		global $option, $Itemid;
		
		$mainframe =& JFactory::getApplication();

		$user =& JFactory::getUser();
		$userId = $user->get('id');
		$quizId = JRequest::getInt('quizId');

		$generateTicketId = false;
		$isAnonymous = empty($userId);
		$ticketId = '';
		if (!$isAnonymous)
		{
			$ticketId = $this->_quizController->call('getNotFinishedTicketId', $quizId, $userId);
			if (empty($ticketId))
			{
				$generateTicketId = true;
			}
		}
		else 
		{
			if (isset($_COOKIE['ariQuizTicketId']))
			{
				$statisticsInfoId = $this->_quizController->call('getStatisticsInfoIdByTicketId', $_COOKIE['ariQuizTicketId'], 0, array('Process', 'Prepare'), $quizId);
				if (!empty($statisticsInfoId))
				{
					$ticketId = $_COOKIE['ariQuizTicketId'];
				}
				else 
				{
					setcookie('ariQuizTicketId', '', time() - 3600);
					$generateTicketId = true;
				}
			}
			else
			{
				$generateTicketId = true;
			}
		}

		if ($generateTicketId)
		{
			$canTakeQuiz = $this->_quizController->call('canTakeQuiz', $quizId, $userId, $user->get('usertype'));
			if ($canTakeQuiz)
			{
				$ticketId = $this->_quizController->call('createTicketId', $quizId, $userId);
				if ($isAnonymous)
				{
					setcookie('ariQuizTicketId', $ticketId, time()+ 3 * 24 * 3600, '/');
				}
			}
			else
			{
				$mainframe->redirect(
					AriQuizFrontHelper::addTmplToLink('index.php?option=' . $option . '&task=quiz&quizId=' . $quizId . '&Itemid=' . $Itemid));
			}
		}

		$mainframe->redirect(
			AriQuizFrontHelper::addTmplToLink('index.php?option=' . $option . '&task=take_quiz&quizId=' . $quizId . '&ticketId=' . $ticketId . '&Itemid=' . $Itemid));
	}
}
?>
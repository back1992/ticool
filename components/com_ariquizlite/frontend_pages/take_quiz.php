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

class take_quizAriPage extends AriUserQuizPageBase
{
	function execute()
	{
		global $option, $Itemid;
		
		$mainframe =& JFactory::getApplication();

		$user =& JFactory::getUser();
		$userId = $user->get('id');
		$quizId = JRequest::getInt('quizId');
		$ticketId = JRequest::getString('ticketId', '');
		
		if (!AriQuizFrontHelper::_checkQuizAvailability($userId, $ticketId)) return ;

		$errorMessage = '';
		$isQuizComposed = $this->_quizController->call('composeUserQuiz', $quizId, $ticketId, $userId);
		if (!$isQuizComposed)
		{
			AriQuizFrontHelper::_redirectToInfo('FrontEnd.QuizNotAvailable');			
		}
		else 
		{
			$mainframe->redirect(
				AriQuizFrontHelper::addTmplToLink('index.php?option=' . $option . '&task=question&ticketId=' . $ticketId . '&t=' . uniqid('uv_') . '&Itemid=' . $Itemid));
		}
	}
}
?>
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

class quizAriPage extends AriUserQuizPageBase
{
	function execute()
	{
		$user =& JFactory::getUser();		
		$quizId = JRequest::getInt('quizId');
		$canTakeQuiz = !empty($quizId)
			? $this->_quizController->call('canTakeQuiz', $quizId, $user->get('id'), $user->get('usertype'))
			: false;

		$quiz = $this->_quizController->call('getQuiz', $quizId);
		
		$this->addVar('ticketId', JRequest::getString('ticketId', ''));
		$this->addVar('quiz', $quiz);
		$this->addVar('canTakeQuiz', $canTakeQuiz);
		
		parent::execute();
	}
}
?>
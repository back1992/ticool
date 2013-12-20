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

class checkQuizNameAriPage extends AriPageBase 
{
	function execute()
	{
		@ob_end_clean();
		
		$name = JRequest::getString('name', '');
		$quizId = JRequest::getInt('quizId');
		$quizController = new AriQuizController();
		$isUnique = $quizController->call('isUniqueQuizName', $name, $quizId);
		echo $isUnique ? 'true' : 'false';
		exit();
	}
}
?>
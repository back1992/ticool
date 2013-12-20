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

class checkCategoryNameAriPage extends AriPageBase
{
	function execute()
	{
		@ob_end_clean();
		
		$name = JRequest::getString('name'); 
		$categoryId = JRequest::getInt('categoryId');
		$quizController = new AriQuizController();
		$isUnique = $quizController->call('isUniqueCategoryName', $name, $categoryId);
		echo $isUnique ? 'true' : 'false';
		exit();
	}
}
?>
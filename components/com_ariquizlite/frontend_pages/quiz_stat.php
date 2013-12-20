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

class quiz_statAriPage extends AriUserQuizPageBase
{
	function execute()
	{
		$user =& JFactory::getUser();
		$userId = $user->get('id');

		if (empty($userId))
		{
			AriQuizFrontHelper::_redirectToInfo('');
			exit();
		}
		
		$results = $this->_resultController->call('getResults', null, null, null, 0, $userId);
	
		$this->addVar('results', $results);
		
		parent::execute();
	}
}
?>
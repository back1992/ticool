<?php
/*
 * ARI Framework Lite
 *
 * @package		ARI Framework Lite
 * @version		1.0.0
 * @author		ARI Soft
 * @copyright	Copyright (c) 2009 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');

AriKernel::import('Web.PageBase');

class AriUserQuizPageBase extends AriPageBase
{
	var $_resultController;
	var $_quizController;
	
	function _init()
	{
		global $Itemid;
		
		$this->_resultController = new AriQuizResultController();
		$this->_quizController = new AriQuizController();
		
		$this->addVar('Itemid', $Itemid);
		
		parent::_init();
	}
}
?>

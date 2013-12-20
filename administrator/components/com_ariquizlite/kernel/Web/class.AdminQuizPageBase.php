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
AriKernel::import('Web.Request');

class AriAdminQuizPageBase extends AriPageBase 
{
	var $_quizController;
	
	function _init()
	{
		if (J1_6)
			JHtml::_('behavior.framework');

		$this->_quizController = new AriQuizController();
		
		parent::_init();
	}

	function execute()
	{
		parent::execute();

		$tplPath = dirname(__FILE__) . '/Page/_Templates/';
		require_once $tplPath . 'AdminPageFooter.html.php';
	}
}
?>
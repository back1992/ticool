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

AriKernel::import('Web.LangListPageBase');

class lang_backendAriPage extends AriLangListPageBase 
{
	function _init()
	{
		$this->_fileGroup = ARI_QUIZ_FILE_LANGBACKEND;
		$this->_defaultFileKey = ARI_QUIZ_CONFIG_BLANG;
		$this->_task = 'lang_backend';
		$this->_addTask = 'blang_add';
		
		parent::_init();
	}
}
?>
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

class QuestionTypeEntity extends JTable 
{
	var $QuestionTypeId;
	var $QuestionType;
	var $ClassName;
	var $CanHaveTemplate;
	var $TypeOrder;
	var $Default;
	
	function __construct(&$_db)
	{
		parent::__construct('#__ariquizquestiontype', 'QuestionTypeId', $_db);
	}
}
?>
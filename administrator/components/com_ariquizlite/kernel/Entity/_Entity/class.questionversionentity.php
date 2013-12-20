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

class QuestionVersionEntity extends JTable
{
	var $QuestionVersionId;
	var $QuestionId;
	var $QuestionCategoryId;
	var $QuestionType;
	var $QuestionTypeId;
	var $Question;
	var $QuestionTime;
	var $HashCode;
	var $Created;
	var $CreatedBy;
	var $TotalPoint;
	var $ShowAsImage;
	var $Data;
	var $Score;
	var $Status;
	
	function __construct(&$_db)
	{
		parent::__construct('#__ariquizquestionversion', 'QuestionVersionId', $_db);
		$this->QuestionType = EntityFactory::createInstance('QuestionTypeEntity', ARI_ENTITY_GROUP);
	}
}

?>

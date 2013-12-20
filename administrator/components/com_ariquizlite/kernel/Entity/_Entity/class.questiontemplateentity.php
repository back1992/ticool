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

class QuestionTemplateEntity extends JTable
{
	var $TemplateId;
	var $TemplateName;
	var $QuestionTypeId;
	var $Data;
	var $QuestionType;
	var $Created;
	var $CreatedBy;
	var $Modified;
	var $ModifiedBy;
	var $DisableValidation;
	
	function __construct(&$_db) 
	{
		parent::__construct('#__ariquizquestiontemplate', 'TemplateId', $_db);
		$this->QuestionType = EntityFactory::createInstance('QuestionTypeEntity', ARI_ENTITY_GROUP);
	}
}
?>
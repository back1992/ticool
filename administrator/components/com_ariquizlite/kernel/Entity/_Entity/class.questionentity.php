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

class QuestionEntity extends JTable 
{
	var $QuestionId;
	var $QuizId;
	var $QuestionVersionId;
	var $CreatedBy;
	var $Created;
	var $ModifiedBy;
	var $Modified;
	var $QuestionVersion;
	var $Status;
	var $QuestionIndex;

	function __construct(&$_db) 
	{
		parent::__construct('#__ariquizquestion', 'QuestionId', $_db);
		$this->QuestionVersion = EntityFactory::createInstance('QuestionVersionEntity', ARI_ENTITY_GROUP);
	}
}
?>
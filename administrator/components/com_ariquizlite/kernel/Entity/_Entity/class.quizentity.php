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

class QuizEntity extends JTable
{
	var $QuizId = null;
	var $QuizName = '';
	var $CreatedBy = 0;
	var $Created;
	var $ModifiedBy = null;
	var $Modified = null;
	var $AccessType = null;
	var $Status;
	var $TotalTime = null;
	var $PassedScore;
	var $QuestionCount = null;
	var $QuestionTime = null;
	var $CategoryList;
	var $AccessList;
	var $Description = null;
	var $CanSkip;
	var $RandomQuestion;
	var $AttemptCount = 0;
	var $LagTime = 0;
	var $CssTemplateId;
	var $AdminEmail;

	function __construct(&$_db) 
	{
		parent::__construct('#__ariquiz', 'QuizId', $_db);
	}
}
?>
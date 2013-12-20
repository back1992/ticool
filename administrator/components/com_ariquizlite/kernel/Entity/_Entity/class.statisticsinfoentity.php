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

class StatisticsInfoEntity extends JTable 
{
	var $StatisticsInfoId;
	var $QuizId;
	var $UserId;
	var $Status;
	var $TicketId;
	var $StartDate;
	var $EndDate;
	var $PassedScore;
	var $UserScore;
	var $MaxScore;
	var $Passed;
	var $CreatedDate;
	var $ResultEmailed;
	
	function __construct(&$_db) 
	{
		parent::__construct('#__ariquizstatisticsinfo', 'StatisticsInfoId', $_db);
	}
}
?>
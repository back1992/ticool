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

class CategoryEntity extends JTable
{
	var $CategoryId;
	var $CategoryName;
	var $Description;
	var $CreatedBy;
	var $Created;
	var $ModifiedBy;
	var $Modified;

	function __construct(&$_db)
	{
		parent::__construct('#__ariquizcategory', 'CategoryId', $_db);
	}
}
?>
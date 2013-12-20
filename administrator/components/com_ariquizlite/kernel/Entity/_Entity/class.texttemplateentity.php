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

class TextTemplateEntity extends JTable 
{
	var $TemplateId;
	var $BaseTemplateId;
	var $TemplateName;
	var $Value;
	var $Created;
	var $CreatedBy;
	var $Modified;
	var $ModifiedBy;
	var $Params = null;
	
	function __construct(&$_db) 
	{
		parent::__construct('#__arigenerictemplate', 'TemplateId', $_db);
	}
	
	function parse($params = array())
	{
		if (!empty($params))
		{
			foreach ($params as $key => $val)
			{
				${$key} = $val;
			}
		}

		$value = $this->Value;
		$value = str_replace(array_map(array('TextTemplateEntity', '_mapParamsKey'), array_keys($params)), array_values($params), $value);
		
		return $value;
	}
	
	function _mapParamsKey($key)
	{
		return sprintf('{$%s}', $key);
	}
}
?>
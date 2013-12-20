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

class ArisUtils 
{
	function getIP()
	{
		$ip = getenv('HTTP_X_FORWARDED_FOR')
    		? getenv('HTTP_X_FORWARDED_FOR')
    		: getenv('REMOTE_ADDR');

    	return $ip;
	}
}

define ('ARIS_RESPONSE_CTYPE_OCTET', 'application/octet-stream');

class ArisResponse
{
	function sendContentAsAttach($fileContent, $fileName, $type = ARIS_RESPONSE_CTYPE_OCTET)
	{
		$fileName = rawurldecode($fileName);
		
		@ob_end_clean();
		header('Content-Type: ' . $type);
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header('Accept-Ranges: bytes');
		header("Cache-control: private");
		header('Pragma: private');
		header('Content-Length: ' . (string)strlen($fileContent));

		echo $fileContent;
		exit();
	}
}

define('ARIS_SORTING_DIR_ASC', 'asc');
define('ARIS_SORTING_DIR_DESC', 'desc');

class ArisSortingInfo
{
	var $sortField;
	var $sortDirection = ARIS_SORTING_DIR_ASC;
	
	function ArisSortingInfo($sortField, $sortDirection)
	{
		$this->sortField = $sortField;
		$this->sortDirection = $sortDirection;
	}
}

class ArisSortingHelper
{
	function getCurrentSorting($defaultField, $defaultDir = ARIS_SORTING_DIR_ASC)
	{
		$sortInfo = null;
		if (isset($_REQUEST['zsort']))
		{
			$sortField = JRequest::getCmd('zfield', $defaultField);

			$dir = JRequest::getString('zdir', $defaultField);
			if ($dir != ARIS_SORTING_DIR_DESC && $dir != ARIS_SORTING_DIR_ASC)
			{
				$dir = $defaultDir;
			}
			
			$sortInfo = new ArisSortingInfo($sortField, $dir);
		}
		
		return $sortInfo;
	}
}

define ('ARISFILTER_TYPE_SELECT', 1);

class ArisFilter
{
	var $_type;
	var $_field;
	var $_label;
	var $_data;
	var $_multipleValue;
	var $_dataKey;
	var $_dataValue;
	var $_emptyValue;
	
	function ArisFilter($field, $data, $dataKey, $dataValue, $label, $type, $multipleValue, $emptyValue = 0)
	{
		$this->_multipleValue = $multipleValue;
		$this->_label = $label;
		$this->_type = $type;
		$this->_field = $field;
		$this->_emptyValue = $emptyValue;
		$this->_dataKey = $dataKey;
		$this->_dataValue = $dataValue;
		$this->setData($data);
	}
	
	function getType()
	{
		return $this->_type;
	}
	
	function getField()
	{
		return $this->_field;
	}
	
	function getLabel()
	{
		return $this->_label;
	}
	
	function getMultipleValue()
	{
		return $this->_multipleValue;
	}
	
	function getEmptyValue()
	{
		return $this->_emptyValue;
	}
	
	function setData($data)
	{
		$normalizeData = array();
		if (!empty($data) && count($data) > 0)
		{
			list(,$first) = each($data);  
			if (!is_object($first))
			{
				$dataKey = $this->getDataKey();
				$dataValue = $this->getDataValue();
				foreach ($data as $key => $value)
				{
					$obj = new stdClass();
					$obj->$dataKey = $key;
					$obj->$dataValue = $value;
					$normalizeData[] = $obj;
				}
			}
			else
			{
				$normalizeData = $data;
			}
		}
		
		$this->_data = $normalizeData;
	}
	
	function getData()
	{
		$data = $this->_data;

		$label = $this->getLabel();
		if ($label !== null)
		{
			$dataKey = $this->getDataKey();
			$dataValue = $this->getDataValue();
			$emptyItem = new stdClass();
			$emptyItem->$dataKey = $this->getEmptyValue();
			$emptyItem->$dataValue = $label;
			if (empty($data)) $data = array();
			array_unshift($data, $emptyItem);
		}
		
		return $data;
	}
	
	function getDataKey()
	{
		return $this->_dataKey !== null ? $this->_dataKey : 'value';
	}
	
	function getDataValue()
	{
		return $this->_dataValue !== null ? $this->_dataValue : 'text';
	}
	
	function getControlName($field = null, $multipleValue = null)
	{
		if ($field === null) $field = $this->getField();
		if ($multipleValue === null) $multipleValue = $this->getMultipleValue();

		$name = 'arisFilter_' . str_replace('.', '_', $field);
		if ($multipleValue) $name .= '[]';
		
		return $name;
	}
	
	function draw()
	{
		$name = $this->getControlName(null, false);
		$selected = JRequest::getVar($name, null);

		return JHTML::_(
			'select.genericlist', 
			$this->getData(), 
			$this->getControlName(), 
			'class="text_area" ' . ($this->getMultipleValue() ? ' multiple' : ''), 
			$this->getDataKey(), 
			$this->getDataValue(), 
			$selected);
	}
	
	function inData($val)
	{
		$isContains = false;
		$data = $this->_data;
		if (!empty($data))
		{
			$dataKey = $this->getDataKey();
			foreach ($data as $dataItem)
			{
				if ($dataItem->$dataKey == $val)
				{
					$isContains = !$isContains;
					break;
				}
			}
		}
		
		return $isContains;
	}

	function getFilterInfo()
	{
		$val = JRequest::getVar($this->getControlName(), null);
		$data = $this->_data;
		if ($val === $this->getEmptyValue() ||
			!$this->inData($val))
		{
			$val = null;
		}
		
		return $val;
	}
}

class ArisFilterContainer
{
	var $_filters = array();
	
	function ArisFilterContainer()
	{
		
	}
	
	function addFilter($field, $data, $label = null, $dataKey = null, $dataValue = null, $type = ARISFILTER_TYPE_SELECT, $multipleValue = false, $emptyValue = 0)
	{
		$filter = new ArisFilter($field, $data, $dataKey, $dataValue, $label, $type, $multipleValue, $emptyValue);
		$this->_filters[] = $filter;
	}
	
	function draw()
	{
		$view = '';
		if (!empty($this->_filters))
		{
			foreach ($this->_filters as $filter)
			{
				$view .= $filter->draw();
			}
		}
		
		return $view;
	}
	
	function getFiltersInfo()
	{
		$info = array();
		if (!empty($this->_filters))
		{
			foreach ($this->_filters as $filter)
			{
				$val = $filter->getFilterInfo();
				if ($val !== null) $info[$filter->getField()] = $val;
			}
		}
		
		return $info;
	}
	
	function getFilterValue($field, $defaultValue = null)
	{
		$retVal = $defaultValue;
		if (!empty($this->_filters))
		{
			foreach ($this->_filters as $filter)
			{
				if ($field == $filter->getField())
				{
					$val = $filter->getFilterInfo();
					if ($val !== null) $retVal = $val;
					break;					
				}
			}
		}
		
		return $retVal;
	}
}
?>
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

class AriControllerBase extends AriObject
{
	var $_db;
	
	function __construct()
	{
		$this->_db =& JFactory::getDBO();
	}
	
	function call($method)
	{
		$numArgs = func_num_args();
		$args = func_get_args();
		if ($numArgs > 0)
		{
			array_shift($args);
		}
		
		set_error_handler(array(&$this, 'errorHandler'));

		$retVal = call_user_func_array(array(&$this, $method), $args);

		restore_error_handler();
		
		$this->_isError();
		
		return $retVal;
	}
	
	function _raiseError($error)
	{
		trigger_error($error->error, E_USER_ERROR);
	}

	function _getFilter($filterInfo)
	{
		$filter = '';
		if (!empty($filterInfo) && is_array($filterInfo))
		{
			$filterParts = array();
			foreach ($filterInfo as $field => $value)
			{
				$filterParts[] = sprintf('%s = %s',
					$field, 
					$this->_db->Quote($value));
			}
			$filter = join(' AND ', $filterParts);
		}
		
		return $filter;
	}
	
	function _getOrder($sortInfo)
	{
		$query = '';
		if (!empty($sortInfo) && !empty($sortInfo->sortField))
		{
			$query = sprintf(' ORDER BY %s %s ', $sortInfo->sortField, $sortInfo->sortDirection);
		}
		
		return $query;
	}
	
	function _getLimit($limitStart, $limit)
	{
		$query = '';
		if ($limitStart !== null && $limit)
		{
			$query .= ' LIMIT ' . intval($limitStart);
			if ($limit !== null)
			{
				$query .= ',' . intval($limit);
			}
		}
		
		return $query;
	}
	
	function _fixIdList($idList)
	{
		if (empty($idList))
			return array();

		if (!is_array($idList))
		{
			$idList = array($idList);
		}
		
		return $idList;
	}
	
	function _quoteValues($arr)
	{
		if (!empty($arr))
		{
			foreach ($arr as $key => $value)
				$arr[$key] = $this->_db->Quote($value);
		}
		
		return $arr;
	}
	
	function _normalizeValue($val)
	{
		return $val === null ? 'NULL' : $this->_db->Quote($val);
	}
	
	function _getRecordCount($tableName, $predicateFields = array())
	{
		$query = 'SELECT COUNT(*) FROM ' . $tableName;
		if (!empty($predicateFields))
		{
			$query .= ' WHERE ';
			$predQuery = array();
			foreach ($predicateFields as $field => $value)
			{
				 $predQuery[] = sprintf('`%s` = %s',
				 	$field,
				 	$this->_db->Quote($value)); 
			}
			
			$query .= join($predQuery, ' AND ');
		}

		$this->_db->setQuery($query);
		$count = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get count.', E_USER_ERROR);
			return 0;
		}
		
		return $count;
	}

	function _isUniqueField($tableName, $field, $value, $keyField = null, $key = null)
	{
		$query = sprintf('SELECT COUNT(%s) FROM %s WHERE %s = %s', $field, $tableName, $field, $this->_db->Quote($value));
		if (!empty($keyField) && $key != null)
		{
			$query .= sprintf(' AND %s <> %s', $keyField, $this->_db->Quote($key));
		}
		$this->_db->setQuery($query);
		$count = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt check unique field.', E_USER_ERROR);
			return false;
		}
		
		return $count < 1;
	}
}
?>
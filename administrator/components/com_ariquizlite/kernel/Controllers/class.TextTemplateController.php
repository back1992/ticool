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

AriKernel::import('Controllers.ControllerBase');

class AriTextTemplateController extends AriControllerBase
{
	function getParamsByGroup($group)
	{
		$query = sprintf('SELECT ParamId, ParamName, ParamDescription, ParamType' . 
			' FROM #__arigenerictemplatebase GTB INNER JOIN #__arigenerictemplateparam GTP' .
			' 	ON GTB.BaseTemplateId = GTP.BaseTemplateId' .
			' WHERE GTB.Group = %s',
			$this->_db->Quote($group));
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get template params.', E_USER_ERROR);
			return null;
		}
		
		return $rows;
	}
	
	function getTemplate($templateId, $group = null)
	{
		$templateId = intval($templateId);
		$template = EntityFactory::createInstance('TextTemplateEntity', ARI_ENTITY_GROUP);
		if (!$template->load($templateId))
		{
			trigger_error('ARI: Couldnt get text template.', E_USER_ERROR);
			return null;
		}
		
		return $template;
	}
	
	function saveTemplate($templateId, $fields, $group, $ownerId)
	{
		$error = 'ARI: Couldnt save text template.'; 
		
		$templateId = intval($templateId);
		$isUpdate = ($templateId > 0);
		$row = $isUpdate ? $this->getTemplate($templateId) : EntityFactory::createInstance('TextTemplateEntity', ARI_ENTITY_GROUP);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if (!$row->bind($fields))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if ($isUpdate)
		{
			$row->Modified = ArisDate::getDbUTC();
			$row->ModifiedBy = $ownerId;
		} 
		else
		{
			$templateBase = $this->getTemplateBaseByGroup($group);
			$row->BaseTemplateId = $templateBase->BaseTemplateId;
			$row->Created = ArisDate::getDbUTC();
			$row->CreatedBy = $ownerId;
		}
		
		if (!$row->store())
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		return $row;
	}
	
	function getTemplateBaseByGroup($group)
	{
		$error = 'ARI: Couldnt get template base by group name.';
		
		$template = EntityFactory::createInstance('TextTemplateBaseEntity', ARI_ENTITY_GROUP);
		$query = sprintf('SELECT GTB.*' .
			' FROM #__arigenerictemplatebase GTB' .
			' WHERE GTB.Group = %s LIMIT 0,1',
			$this->_db->Quote($group));
		$this->_db->setQuery($query);
		$templateFields = $this->_db->loadAssocList();
		if ($this->_db->getErrorNum())
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if (!empty($templateFields) && count($templateFields) > 0)
		{
			if (!$template->bind($templateFields[0]))
			{
				trigger_error($error, E_USER_ERROR);
				return null;
			}
		}

		return $template; 
	}
	
	function getTemplateList($group)
	{
		$query = sprintf('SELECT TemplateId, TemplateName' . 
			' FROM #__arigenerictemplate GT INNER JOIN #__arigenerictemplatebase GTB' .
			' 	ON GT.BaseTemplateId = GTB.BaseTemplateId' .
			' WHERE GTB.Group = %s' .
			' ORDER BY GT.TemplateName ASC',
			$this->_db->Quote($group));
		
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get template list.', E_USER_ERROR);
			return null;
		}
		
		return $rows;
	}
	
	function setEntitySingleTemplate($entityName, $entityId, $map)
	{
		if (empty($map) || !is_array($map)) return true;

		$queryList = array();
		$quoteEntityName = $this->_db->Quote($entityName);
		$queryList[] = sprintf('DELETE FROM #__arigenerictemplateentitymap' .
			' WHERE EntityName = %s AND EntityId = %d',
			$quoteEntityName,
			$entityId);
		
		$query = 'INSERT INTO #__arigenerictemplateentitymap (TemplateId,EntityName,TemplateType,EntityId) VALUES ';
		$values = array();
		foreach ($map as $key => $value)
		{
			if ($value) $values[] = sprintf('(%d,%s,%s,%d)', $value, $quoteEntityName, $this->_db->Quote($key), $entityId);
		}
		if (count($values) > 0)
		{
			$query .= join(',', $values);
			$queryList[] = $query;
		}

		foreach ($queryList as $queryItem)
		{
			$this->_db->setQuery($queryItem);
			$this->_db->query();
			if ($this->_db->getErrorNum())
			{
				trigger_error('ARI: Couldnt set entity templates.', E_USER_ERROR);
				return false;
			}
		}

		return true;
	}
	
	function getEntitySingleTemplate($entityName, $entityId)
	{
		$query = sprintf('SELECT GTEM.TemplateType, GTEM.TemplateId' .
			' FROM #__arigenerictemplateentitymap GTEM' . 
			' WHERE GTEM.EntityName = %s AND GTEM.EntityId = %d' .
			' GROUP BY GTEM.TemplateType',
			$this->_db->Quote($entityName),
			$entityId);
		$this->_db->setQuery($query);
		$rows = $this->_db->loadAssocList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get entity templates.', E_USER_ERROR);
			return null;
		}
		
		$res = array();
		if (!empty($rows))
		{
			foreach ($rows as $row)
			{
				$res[$row['TemplateType']] = $row['TemplateId'];
			}
		}
		
		return $res;
	}
	
	function deleteTemplate($idList, $group)
	{
		$idList = $this->_fixIdList($idList);
		if (empty($idList)) return true;
		
		$queryList = array();
		$idStr = join(',', $this->_quoteValues($idList));
		$queryList[] = sprintf('DELETE FROM #__arigenerictemplate WHERE TemplateId IN (%s)', 
			$idStr);
		$queryList[] = sprintf('DELETE FROM #__arigenerictemplateentitymap WHERE TemplateId IN (%s)', 
			$idStr);
			
		foreach ($queryList as $queryItem)
		{
			$this->_db->setQuery($queryItem);
			$this->_db->query();
			if ($this->_db->getErrorNum())
			{
				trigger_error('ARI: Couldnt delete text templates.', E_USER_ERROR);
				return false;
			}
		}

		return true;		
	}
}
?>
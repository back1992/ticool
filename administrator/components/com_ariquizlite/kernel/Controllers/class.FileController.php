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

class AriFileController extends AriControllerBase 
{
	var $_table;
	
	function __construct($table)
	{
		$this->_table = $table;
		
		parent::__construct();
	}
	
	function getFileList($group, $idList = array(), $fullLoad = false)
	{
		$query = '';
		if (!empty($idList))
		{
			$idList = $this->_fixIdList($idList);
			if (empty($idList)) return null;

			$fileStr = join(',', $this->_quoteValues($idList));
			$query = sprintf('SELECT FileId,FileName,Extension,Flags,ShortDescription,Created%s FROM %s WHERE `Group` = %s AND FileId IN (%s) ORDER BY ShortDescription',
				$fullLoad ? ',Content' : '',
				$this->_table,
				$this->_db->Quote($group),
				$fileStr);
		}
		else
		{	
			$query = sprintf('SELECT FileId,FileName,Extension,Flags,ShortDescription,Created%s FROM %s WHERE `Group` = %s ORDER BY ShortDescription',
				$fullLoad ? ',Content' : '',
				$this->_table,
				$this->_db->Quote($group));
		}
		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();
		
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get file list.', E_USER_ERROR);
			return null;
		}

		return $results;
	}
	
	function deleteFile($idList, $group = null)
	{
		$idList = $this->_fixIdList($idList);
		if (empty($idList)) return true;
		
		$fileStr = join(',', $this->_quoteValues($idList));

		$query = sprintf('DELETE FROM %s WHERE FileId IN (%s) AND (%s IS NULL OR `Group` = %s)',
			$this->_table, 
			$fileStr,
			$this->_normalizeValue($group),
			$this->_db->Quote($group));
		$this->_db->setQuery($query);
		$this->_db->query();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt delete files.', E_USER_ERROR);
			return false;
		}
		
		return true;
	}
	
	function getFile($fileId, $group = null)
	{
		$fileId = intval($fileId);
		if (empty($fileId)) return null;
		
		$file = EntityFactory::createInstance('FileEntity', ARI_ENTITY_GROUP);
		
		if (!$file->load($fileId) || ($group !== null && $file->Group != $group))
		{
			return null;
		}
		
		return $file;
	}
	
	function saveFileFromFile($fileId, $fields, $fileName, $ownerId = 0)
	{
		if (file_exists($fileName))
		{			
			$handle = fopen($fileName, "rb");			 
			$fields['Content'] = fread($handle, filesize($fileName));
			fclose($handle);

			$this->saveFile($fileId, $fields, $ownerId);
		}
	}
	
	function saveFile($fileId, $fields, $ownerId = 0)
	{
		$error = 'ARI: Couldnt save file.'; 
		
		$fileId = intval($fileId);
		$isUpdate = ($fileId > 0);
		$row = $isUpdate 
			? $this->getFile($fileId) 
			: EntityFactory::createInstance('FileEntity', ARI_ENTITY_GROUP);
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
		
		$row->Content = addslashes($row->Content);
		$row->Size = strlen($row->Content);
		
		if ($isUpdate)
		{
			$row->Modified = ArisDate::getDbUTC();
			$row->ModifiedBy = $ownerId;
		} 
		else
		{
			$row->Created = ArisDate::getDbUTC();
			$row->CreatedBy = $ownerId;
		}

		$extension = $row->Extension;
		if ($row->FileName)
		{
			$info = pathinfo($row->FileName);
			$extension = $info['extension'];			
		}
		$row->Extension = $extension;

		if (!$row->store())
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}

		$hexData = bin2hex($fields['Content']);
		$query = sprintf('UPDATE %s SET Content = 0x%s WHERE FileId=%d',
			$this->_table,
			$hexData,
			$row->FileId);
		$this->_db->setQuery($query);
		$this->_db->query();
		
		return $row;
	}
}
?>
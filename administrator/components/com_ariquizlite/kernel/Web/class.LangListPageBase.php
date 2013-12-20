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

AriKernel::import('Controllers.FileController');
AriKernel::import('Config.ConfigWrapper');
AriKernel::import('Xml.XmlHelper');

class AriLangListPageBase extends AriPageBase
{
	var $_fileController;
	var $_fileGroup;
	var $_defaultFileKey;
	var $_task;
	var $_addTask;
	var $_savedIndex = array('ShortDescription');
	
	function _init()
	{
		if (J1_6)
			JHtml::_('behavior.framework');

		$this->_fileController = new AriFileController(ARI_QUIZ_FILE_TABLE);
		
		parent::_init();
	}
	
	function execute()
	{
		$config = AriConfigWrapper::getConfig();
		
		$templateList = $this->_fileController->call('getFileList', $this->_fileGroup);
		$defaultLang = isset($config[$this->_defaultFileKey]) ? $config[$this->_defaultFileKey] : -1;
		$this->addVar('templateList', $templateList);
		$this->addVar('defaultLang', $defaultLang);
		$this->addVar('currentTask', $this->_task);
		$this->addVar('addTask', $this->_addTask);
		
		parent::execute();
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('delete', 'clickDelete');
		$this->_registerEventHandler('default', 'clickDefault');
		$this->_registerEventHandler('export', 'clickExport');
		$this->_registerEventHandler('import', 'clickImport');
	}
	
	function clickImport($eventArgs)
	{
		$file = $this->_saveFile();
		if ($file)
		{
			AriQuizWebHelper::preCompleteAction('Complete.FileImport', array('task' => $this->_task));
		}
		else
		{
			AriQuizWebHelper::preCompleteAction('Validator.FileIncorrectFormat', array('task' => $this->_task));
		}
	}
	
	function clickExport($eventArgs)
	{
		$fileId = JRequest::getVar('fileId', array());
		if (is_array($fileId))
		{
			$fileId = count($fileId) > 0 ? $fileId[0] : 0;
		}
		
		$fileId = intval($fileId);
		if ($fileId > 0)
		{
			$file = $this->_fileController->call('getFile', $fileId, $this->_fileGroup);
			if ($file && $file->Content)
			{
				ArisResponse::sendContentAsAttach($file->Content, 'lang.xml');
			}
		}
	}
	
	function clickDefault($eventArgs)
	{
		$rbLangDefault = JRequest::getInt('rbLangDefault');
		if ($rbLangDefault > 0)
		{
			AriConfigWrapper::setConfigValue($this->_defaultFileKey, $rbLangDefault);
		}

		AriQuizWebHelper::preCompleteAction('Complete.SetLangDefault', array('task' => $this->_task));
	}
	
	function clickDelete($eventArgs)
	{
		$fileIdList = JRequest::getVar('fileId', array());
		$rbLangDefault = JRequest::getInt('rbLangDefault');
		if (in_array($rbLangDefault, $fileIdList))
		{
			AriConfigWrapper::removeConfigKey($this->_defaultFileKey);
		}
		
		$this->_fileController->call('deleteFile', $fileIdList, $this->_fileGroup);
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.LangDelete', array('task' => $this->_task));
		}
	}
	
	function _saveFile()
	{
		$file = JRequest::getVar('fileLang', '', 'files', 'array'); 
		$res = array(); 
		if (!empty($file) && $file['size'] > 0)
		{
			$fileName = $file['tmp_name'];
			if (file_exists($fileName))
			{
				$handle = fopen($fileName, "rb");
				$content = fread($handle, filesize($fileName));
				fclose($handle);
				
				$res = array();
				$nullValue = null;
				$baseRes = ArisI18NHelper::parseXmlFromFile(ARI_QUIZ_CACHE_DIR . $this->_fileGroup . '/en.xml', $nullValue);
				$res = ArisI18NHelper::parseXmlFromString($content, $nullValue);
				if (!empty($res))
				{
					$res = ArisI18NHelper::mergeResources($baseRes, $res);
				}
			}
		}

		if (empty($res)) return null;

		$xml = ArisI18NHelper::createXmlFromData($res);
		$xmlStr = AriXmlHelper::toString($xml->document);
		if (strpos($xmlStr, '<?xml') === false)
			$xmlStr = ARI_I18N_TEMPLATE_XML . $xmlStr;

		$user =& JFactory::getUser();
		$ownerId = $user->get('id');
		$reqFields = JRequest::getVar('zLang');
		$fields = array();
		foreach ($this->_savedIndex as $index)
		{
			$fields[$index] = isset($reqFields[$index])
				? $reqFields[$index]
				: '';
		}

		$fields['Group'] = $this->_fileGroup;
		$fields['Content'] = $xmlStr;
		$fields['Extension'] = 'xml';
		$file = $this->_fileController->call('saveFile',
			0,
			$fields,
			$ownerId);

		if ($file) AriFileCache::recacheFile(ARI_QUIZ_CACHE_DIR, $this->_fileGroup, $file->FileId, $file->Extension);

		return $file;
	}
}
?>
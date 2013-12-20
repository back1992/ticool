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

defined('_JEXEC') or die('Restricted access');

$version = new JVersion();
if (!defined('J16')) 
	define('J16', version_compare($version->getShortVersion(), '1.6.0', '>='));

define('_ARI_INSTALL_ERROR_EXECUTEQUERY', 'Couldn\'t execute query. Error: %s.');
define('_ARI_INSTALL_ERROR_CHMOD', 'Couldn\'t change permission for directory "%s" permission "%s".');
define('_ARI_INSTALL_ERROR_UTF8', 'Database not supported UTF-8 encoding.');
define('_ARI_INSTALL_SUCCESFULLY', 'ARI Quiz succesfully installed');
define('_ARI_INSTALL_FAILED', 'ARI Quiz installation failed');
define('_ARI_INSTALL_DEF_VERSION', '1.0.0');
define('_ARI_INSTALL_VERSION', '1.2.3');
define('_ARI_INSTALL_VERSION_KEY', 'Version');

$adminPath = dirname(__FILE__) . '/';
require_once ($adminPath . 'utils/constants.php');
require_once ($adminPath . 'kernel/class.AriKernel.php');

AriKernel::import('Date.Date');
AriKernel::import('Entity.EntityFactory2');
AriKernel::import('Controllers.ControllerBase');
AriKernel::import('Controllers.FileController');
AriKernel::import('I18N.I18N');
AriKernel::import('Xml.XmlHelper');

function com_install() 
{
	$mainframe =& JFactory::getApplication();
	$baseDir = dirname(__FILE__) . '/';
	$option = 'com_ariquizlite';
	$adminPath = JPATH_SITE . '/administrator/components/' . $option . '/';	
	$return = '';
	$installer = new AriQuizInstall();
	
	$installer->_registerErrorHandler();
	
	if (!$installer->isDbSupportUtf8())
	{
		if ($installer->_isError(false, false))
		{
			$return .= "\r\n" . $installer->getLastErrorMsg();
		}
		else
		{
			$return .= "\r\n" . _ARI_INSTALL_ERROR_UTF8;
		}
	}
	
	if (!J16)
	{
		$installer->updateMenuIcon();
		if ($installer->_isError(false, false))
		{
			$return .= "\r\n" . $installer->getLastErrorMsg();
		}
	}	
	
	$installer->createModule($adminPath);
	if ($installer->_isError(false, false))
	{
		$return .= "\r\n" . $installer->getLastErrorMsg();
	}
	
	$installer->setPermissions($adminPath);
	if ($installer->_isError(false, false))
	{
		$return .= "\r\n" . $installer->getLastErrorMsg();
	}
	
	$installer->doInstallFile($adminPath . 'install/description.xml');
	
	$installer->updateVersion();
	
	restore_error_handler();
	
	if (empty($return))
	{ 
		$return = sprintf('<div style="color: green; font-weight: bold; text-align: center;">%s</div>',
			_ARI_INSTALL_SUCCESFULLY);
	}
	else
	{
		$return = sprintf('<div style="color: red; font-weight: bold; text-align: center;">%s</div><div style="color: red;">%s</div>',
			_ARI_INSTALL_FAILED,
			$return);
	}
	
	return nl2br(trim($return));
}

class AriQuizInstall extends AriObject
{
	var $_db;
	
	function __construct()
	{
		$this->_db =& JFactory::getDBO();
	}
	
	function doInstallFile($file)
	{
		if (empty($file) || !file_exists($file)) return ;

		$xmlStr = file_get_contents($file);
		$xml = AriXmlHelper::getXML($xmlStr, false);
		
		$doc = $xml->document;
		$path = pathinfo($file);
		$path = $path['dirname'] . '/';
		
		$childs = $doc->children();
		foreach ($childs as $child)
		{
			$tag = AriXmlHelper::getTagName($child);
			switch ($tag)
			{
				case 'files':
					$this->_parseFilesTag($child, $path);
					break;
			}
		}
	}
	
	function _parseFilesTag($node, $path)
	{
		$folder = AriXmlHelper::getAttribute($node, 'folder');
		if ($folder) $path .= $folder . '/';

		$tagName = 'file';
		$fileNodeList = isset($node->$tagName) ? $node->$tagName : null;
		if (is_null($fileNodeList)) 
			return ; 

		$group = AriXmlHelper::getAttribute($node, 'group');
		$files = array();
		foreach ($fileNodeList as $fileNode)
		{
			$tagName = 'name';
			$name = AriXmlHelper::getData($fileNode, $tagName);
			if (!file_exists($path . $name)) continue;
			
			$tagName = 'shortDescription';
			$tagName1 = 'flags';
			$tagName2 = 'action';
			$files[$name] = array(
				'shortDescription' => isset($fileNode->$tagName) ? AriXmlHelper::getData($fileNode, $tagName) : '',
				'flags' => isset($fileNode->$tagName1) ? AriXmlHelper::getData($fileNode, $tagName1) : 0,
				'action' => isset($fileNode->$tagName2) ? AriXmlHelper::getData($fileNode, $tagName2) : '',
				'update' => false);
		}

		$fileController = new AriFileController(ARI_QUIZ_FILE_TABLE);
		$existsFiles = $fileController->getFileList($group, array(), true);
		if (!empty($existsFiles))
		{
			foreach ($existsFiles as $existFile)
			{
				$name = $existFile->FileName;
				if (isset($files[$name]))
				{
					$file = $files[$name];
					if ($file['flags'] == $existFile->Flags)
					{
						$files[$name]['update'] = true;
						$files[$name]['oldContent'] = $existFile->Content;
						$files[$name]['fileId'] = $existFile->FileId;
					}
				}
			}
		}
		
		$user = JFactory::getUser(); 
		$ownerId = $user->get('id');
		if (!empty($files))
		{
			$nullValue = null;
			foreach ($files as $name => $file)
			{
				if ($file['update'])
				{
					// add merge resource
					if ($file['action'] == 'Merge')
					{
						$baseRes = ArisI18NHelper::parseXmlFromFile($path . $name, $nullValue);
						$res = ArisI18NHelper::parseXmlFromString($file['oldContent'], $nullValue);						
						
						$res = ArisI18NHelper::mergeResources($baseRes, $res);
						$xml = ArisI18NHelper::createXmlFromData($res);
						$xmlStr = AriXmlHelper::toString($xml->document);

						$fields = array();
						$fields['Group'] = $group;
						$fields['FileName'] = $name;
						$fields['Flags'] = $file['flags'];
						$fields['Content'] = $xmlStr;					
						
						$fileController->saveFile($file['fileId'], $fields, $ownerId);
					}
				}
				else
				{
					$fields = array();
					$fields['ShortDescription'] = $file['shortDescription'];
					$fields['Group'] = $group;
					$fields['FileName'] = $name;
					$fields['Flags'] = $file['flags'];

					$fileController->saveFileFromFile(0, $fields, $path . $name, $ownerId);
				}
			}
		}
	}
	
	function setPermissions($adminPath)
	{
		$errors = array();
		$dirForChmod = array(
			$adminPath . 'cache/files' => 0777,
			$adminPath . 'cache/files/css' => 0777, 
			$adminPath . 'cache/files/lbackend' => 0777,
			$adminPath . 'cache/files/lfrontend' => 0777,
			$adminPath . 'i18n/cache/lbackend' => 0777,
			$adminPath . 'i18n/cache/lfrontend' => 0777);
		foreach ($dirForChmod as $dir => $perm)
		{
			if (!@chmod($dir, $perm))
			{
				$errors[] = sprintf(_ARI_INSTALL_ERROR_CHMOD, $dir, $perm); 
			}
		}
		
		if (count($errors) > 0)
		{
			trigger_error(join("\r\n", $errors), E_USER_ERROR);
			return false;
		}
		
		return true;
	}
	
	function createModule($adminPath)
	{
		jimport( 'joomla.installer.installer' );

		$modulePath = dirname(__FILE__) . '/modules/';
		$installer = new JInstaller();

		$installer->setOverwrite(true);
		$installer->install($modulePath . 'result');
		$installer->install($modulePath . 'topresult');
		$installer->install($modulePath . 'userresult');
		$installer->install($modulePath . 'usertopresult');

		return true;
	}
	
	function updateMenuIcon()
	{
		$queryList = array();
		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../administrator/components/com_ariquizlite/images/arisoft_icon.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite"';

		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../includes/js/ThemeOffice/categories.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite&task=quiz_list"';
		
		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../includes/js/ThemeOffice/categories.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite&task=category_list"';
		
		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../includes/js/ThemeOffice/categories.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite&task=questioncategory_list"';
		
		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../includes/js/ThemeOffice/template.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite&task=qtemplate_list"';
		
		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../includes/js/ThemeOffice/template.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite&task=texttemplate_list"';
		
		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../includes/js/ThemeOffice/search_text.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite&task=results"';
		
		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../includes/js/ThemeOffice/language.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite&task=lang_backend"';
		
		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../includes/js/ThemeOffice/language.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite&task=lang_frontend"';
		
		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../includes/js/ThemeOffice/template.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite&task=templates"';
		
		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../includes/js/ThemeOffice/help.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite&task=help"';
		
		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../includes/js/ThemeOffice/help.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite&task=faq"';
		
		$queryList[] = 'UPDATE #__components' .
		  ' SET admin_menu_img="../includes/js/ThemeOffice/help.png"' .
		  ' WHERE admin_menu_link="option=com_ariquizlite&task=about"';

		foreach ($queryList as $queryItem)
		{
			$this->_db->setQuery($queryItem);
			$this->_db->query();
			if ($this->_db->getErrorNum())
			{
				trigger_error(sprintf(_ARI_INSTALL_ERROR_EXECUTEQUERY, $this->_db->getErrorMsg()), E_USER_ERROR);
				return false;
			}
		}
		
		return true;
	}

	function updateVersion()
	{
		$query = sprintf('INSERT INTO #__ariquizconfig (ParamName,ParamValue) VALUES("%s","%s") ON DUPLICATE KEY UPDATE ParamValue="%s"',
			_ARI_INSTALL_VERSION_KEY,
			_ARI_INSTALL_VERSION,
			_ARI_INSTALL_VERSION);
		$this->_db->setQuery($query);
		$this->_db->query();
	}
	
	function isDbSupportUtf8()
	{		
		$query = 'SHOW CHARACTER SET LIKE "utf8"';
		$this->_db->setQuery($query);
		$result = $this->_db->loadAssocList();
		if ($this->_db->getErrorNum())
		{
			$error = sprintf(_ARI_INSTALL_ERROR_EXECUTEQUERY, 
				$this->_db->getErrorMsg());
			trigger_error($error, E_USER_ERROR);
			return false;			
		}
		
		return (!empty($result) && count($result) > 0);
	}	
}
?>
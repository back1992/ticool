<?php
/*
 *
 * @package		ARI Quiz
 * @author		ARI Soft
 * @copyright	Copyright (c) 2011 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

defined('_JEXEC') or die('Restricted access');

class mod_ariquizresultInstallerScript
{
	function preflight($type, $parent)
	{
		$type = strtolower($type);
		if ($type == 'install' || $type == 'update')
			$this->updateManifest($parent);
	}
	
	function postflight($type, $parent)
	{
		$type = strtolower($type);
		if ($type == 'install' || $type == 'update')
			$this->deleteHelpManifest($parent);
	}
	
	private function updateManifest($parent)
	{
		jimport('joomla.filesystem.file');
		
		$installer = $parent->getParent();
		$manifestFile = basename($installer->getPath('manifest'));
		$cleanManifestFile = preg_replace('/^\_+/i', '', $manifestFile);

		$dir = dirname(__FILE__) . '/install/';

		JFile::delete($dir . $cleanManifestFile);
		JFile::copy($dir . '../' . $cleanManifestFile, $dir . $cleanManifestFile);
	}
	
	private function deleteHelpManifest($parent)
	{
		jimport('joomla.filesystem.file');
		
		$installer = $parent->getParent();
		$manifestFile = basename($installer->getPath('manifest'));

		JFile::delete(JPATH_ROOT . '/modules/mod_ariquizresult/' . $manifestFile);
	}
}
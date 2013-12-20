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

class AriFileCache 
{
	function recacheFile($cacheDir, $fileGroup, $fileId, $binary = true)
	{
		AriFileCache::cacheFile($cacheDir, $fileGroup, $fileId, null, $binary, true);
	}
	
	function recacheFileWithName($cacheDir, $fileGroup, $fileId, $binary = true)
	{
		AriFileCache::cacheFileWithName($cacheDir, $fileGroup, $fileId, null, $binary, true);
	}
	
	function cacheFile($cacheDir, $fileGroup, $fileId, $ext, $binary = true, $recache = false)
	{
		AriFileCache::_cacheFile($cacheDir, $fileGroup, $fileId, $ext, null, false, $binary, $recache);
	}
	
	function cacheFileWithName($cacheDir, $fileGroup, $fileId, $fileName = null, $binary = true, $recache = false)
	{
		AriFileCache::_cacheFile($cacheDir, $fileGroup, $fileId, $fileName, true, $binary, $recache);
	}
	
	function _cacheFile($cacheDir, $fileGroup, $fileId, $ext = null, $fileName = null, $useFileName = false, $binary = true, $recache = false)
	{
		$cacheDir .= $fileGroup . '/';
		if (!$recache)
		{
			if (!is_null($fileName) && file_exists($cacheDir . $fileName)) return ;
			if (!is_null($ext) && file_exists($cacheDir . $fileId . '.' . $ext)) return ;
		}

		$fileController = new AriFileController(ARI_QUIZ_FILE_TABLE);
		$file = $fileController->call('getFile', $fileId, $fileGroup);
		if (!empty($file))
		{
			$content = $file->Content;
			$fileName = $cacheDir;
			$fileName .= ($useFileName && $file->FileName)
				? $file->FileName
				: $fileId . ($file->Extension ? '.' . $file->Extension : '');
			if ($binary)
			{
				AriFileCache::saveBinaryFile($content, $fileName);
			}
			else 
			{
				AriFileCache::saveTextFile($content, $fileName);
			}
		}
	}
	
	function saveBinaryFile($content, $fileName)
	{
		AriFileCache::_saveFile($content, $fileName, 'b');
	}
	
	function saveTextFile($content, $fileName)
	{
		AriFileCache::_saveFile($content, $fileName);
	}
	
	function _saveFile($content, $fileName, $mode = 't')
	{
		$handle = fopen($fileName, 'w+' . $mode);
		fwrite($handle, $content);
		fclose($handle);
	}
}
?>
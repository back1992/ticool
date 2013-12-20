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

class AriQuizWebHelper
{
	function getResValue($keyRes, $htmlSpecialChars = false)
	{
		global $arisI18N;
		
		$value = $arisI18N->getMessage($keyRes);
		if ($htmlSpecialChars) $value = AriQuizWebHelper::htmlSpecialChars($value);
		
		return $value;
	}
	
	function displayDbValue($value, $htmlSpecialChars = true)
	{
		echo $htmlSpecialChars
			? AriQuizWebHelper::htmlSpecialChars($value)
			: $value;
	}
	
	function displayRealResValue($keyRes)
	{
		global $arisI18N;
		
		echo $arisI18N->getMessage($keyRes);
	}
	
	function displayResValue($keyRes, $htmlSpecialChars = false)
	{
		echo AriQuizWebHelper::getResValue($keyRes, $htmlSpecialChars);
	}	
	
	function htmlSpecialChars($value)
	{
		if (!empty($value))
		{
			$transTable = get_html_translation_table(HTML_SPECIALCHARS);
			$transTable['&'] = '&';
			
			$value = strtr($value, $transTable);
		}
		
		return $value;
	}
	
	function cancelAction($task, $params = array())
	{
		global $option;
		
		$mainframe =& JFactory::getApplication();
		
		$url = 'index.php?option=' . $option . '&task=' . $task;
		if ($params && is_array($params))
		{
			foreach ($params as $key => $value)
			{
				$url .= sprintf('&%s=%s', $key, $value);  
			}
		}
		
		$mainframe->redirect($url);
	}
	
	function preCompleteAction($messageId, $params = array())
	{
		global $option;
		
		$mainframe =& JFactory::getApplication();
		
		$url = 'index.php?option=' . $option;
		if ($params && is_array($params))
		{
			foreach ($params as $key => $value)
			{
				$url .= sprintf('&%s=%s', $key, $value);  
			}
		}

		$mainframe->redirect($url . '&arimsg=' . urlencode($messageId));
	}		

	function showValue($value, $emptyValue)
	{
		echo AriQuizWebHelper::getValue($value, $emptyValue);
	}
	
	function getValue($value, $emptyValue)
	{
		return empty($value) ? $emptyValue : $value;
	}

	function createBackendI18N()
	{
		AriQuizWebHelper::_createI18NObj(ARI_QUIZ_CONFIG_BLANG, ARI_QUIZ_FILE_LANGBACKEND);
	}
	
	function createFrontendI18N()
	{
		AriQuizWebHelper::_createI18NObj(ARI_QUIZ_CONFIG_FLANG, ARI_QUIZ_FILE_LANGFRONTEND);
	}
	
	function _createI18NObj($configKey, $fileGroup)
	{
		global $option;

		if (isset($GLOBALS['arisI18N'])) return ;

		AriKernel::import('Cache.FileCache');
		AriKernel::import('Config.ConfigWrapper');
		
		$useLang = AriConfigWrapper::getConfigKey($configKey, 'en'); 
		AriFileCache::cacheFile(ARI_QUIZ_CACHE_DIR, $fileGroup, $useLang, 'xml');

		$basePath = JPATH_SITE . '/administrator/components/' . $option . '/';
		$GLOBALS['arisI18N'] = new ArisI18N($basePath . 'cache/files/' . $fileGroup, $useLang, $basePath . 'i18n/cache/' . $fileGroup, 'en');
	}

	function getShortPeriods()
	{
		return array(
			AriQuizWebHelper::getResValue('Date.DayShort')    => ARIS_DATE_DSC,
			AriQuizWebHelper::getResValue('Date.HourShort')    => ARIS_DATE_HSC,
			AriQuizWebHelper::getResValue('Date.MinuteShort')  => ARIS_DATE_MINSC,
			AriQuizWebHelper::getResValue('Date.SecondShort')  => ARIS_DATE_SSC);
	}
}
?>
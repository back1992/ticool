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

AriKernel::import('I18N._Templates.I18NTemplates');
AriKernel::import('Xml.XmlHelper');

class ArisI18NTagMapping
{
	var $MessageTagName = 'message';
	var $ItemTagName = 'item';
	var $ItemIdAttrName = 'id';
}

class ArisI18N
{
	var $_locale;
	var $_resourceDir;
	var $_cache = null;
	var $_res = null;
	var $_tagMapping = null;
	
	function ArisI18N($resourceDir, $locale, $cacheDir = null, $defaultLocale = 'en', $tagMapping = null)
	{
		$locale = ArisI18NHelper::getMostPrefLocale($resourceDir, $locale, $defaultLocale);
		if ($tagMapping == null)
		{
			$tagMapping = new ArisI18NTagMapping();
		}
		
		$this->_tagMapping = $tagMapping;
		$this->_resourceDir = $resourceDir;
		$this->_locale = $locale;
		$this->_cache = $cacheDir != null 
			? new ArisI18NCache($locale, $resourceDir, $cacheDir, $tagMapping) 
			: null; 
	}
	
	function _getTagMapping()
	{
		return $this->_tagMapping;
	}
	
	function _getLocale()
	{
		return $this->_locale;
	}
	
	function _getResourceDir()
	{
		return $this->_resourceDir;
	}
	
	function getItem($key)
	{
		$item = null;
		if (!empty($key))
		{
			$res = $this->_getResource();
			if (!empty($res) && isset($res[$key]))
			{
				$item = $res[$key];
			}
		}
		
		return $item;
	}
	
	function getMessage($key)
	{
		$tagMapping = $this->_getTagMapping();
		return $this->getItemElement($key, $tagMapping->MessageTagName);
	}
	
	function displayMessage($key)
	{
		echo $this->getMessage($key);
	}
	
	function displayQuoteMessage($key)
	{
		$message = $this->getMessage($key);
		echo sprintf('"%s"', str_replace('"', '\\"', $message)); 
	}
	
	function getItemElement($key, $elementName)
	{
		$message = null;
		if (!empty($elementName))
		{
			$item = $this->getItem($key);
			if (!empty($item) && isset($item[$elementName]))
			{
				$message = $item[$elementName];
			}
		}
		
		return $message;
	}
	
	function setItem($key, $item)
	{
		$this->setItems(array($key => $item));
	}
	
	function setItems($items)
	{
		if (!empty($items))
		{
			$xml = ArisI18NHelper::loadXmlResource($this->_getLocale(), $this->_getResourceDir());
			if (!empty($xml))
			{
				$tagMapping = $this->_getTagMapping();
				$itemTagName = $tagMapping->ItemTagName;
				$itemIdAttr = $tagMapping->ItemIdAttrName;
				$xmlItems = $xml->document->$itemTagName; 
				foreach ($xmlItems as $xmlItem)
				{
					$key = AriXmlHelper::getAttribute($xmlItem, $itemIdAttr);
					if (isset($items[$key]))
					{
						$item = $items[$key];
						if (!empty($item))
						{
							foreach ($item as $tagName => $value)
							{
								if (!isset($xmlItem->$tagName))
								{
									$xmlItem->addChild($tagName);
								}
								
								$el = $xmlItem->$tagName;
								AriXmlHelper::setData($el, $value);
							}
						}
						unset($items[$key]);
					}
				}
				
				if (!empty($items))
				{
					foreach ($items as $key => $item)
					{
						$newXmlItem =& $xml->document->addChild($itemTagName);
						$newXmlItem->addAttribute($itemIdAttr, $key);
						if (!empty($item))
						{
							foreach ($item as $tagName => $value)
							{
								$newNode =& $newXmlItem->addChild($tagName);
								AriXmlHelper::setData($newNode, $value);
							}
						}
					}
				}
			}
			
			ArisI18NHelper::saveXmlResource($xml, $this->_getLocale(), $this->_getResourceDir());
		}
	}
	
	function setMessage($key, $message)
	{
		$this->setMessages(array($key => $message));
	}
	
	function setMessages($messages)
	{
		if (!empty($messages))
		{
			$items = array();
			foreach ($messages as $key => $message)
			{
				$tagMapping = $this->_getTagMapping();
				$items[$key] = array($tagMapping->MessageTagName => $message);			
			}
			
			$this->setItems($items);
		}
	}

	function _getResource()
	{
		if ($this->_res == null)
		{
			$tagMapping = $this->_getTagMapping();
			$this->_res = $this->_isUseCache() 
				? $this->_cache->getResource() 
				: ArisI18NHelper::loadResource($this->_getLocale(), $this->_getResourceDir(), $tagMapping);
		}
		
		return $this->_res;
	}
	
	function _isUseCache()
	{
		return $this->_cache != null;
	}
}

define('ARI_I18N_SPLITTER', '-');
define('ARI_I18N_ATTRS', '_attrs');
define('ARI_I18N_ATTR_GROUP', 'group');

class ArisI18NHelper
{
	function loadResource($locale, $resourceDir, &$tagMapping)
	{
		$xml = ArisI18NHelper::loadXmlResource($locale, $resourceDir);
		return ArisI18NHelper::parseXml($xml, $tagMapping);
	}
	
	function parseXmlFromFile($fileName, &$tagMapping)
	{
		$res = array();
		if (!empty($fileName) && file_exists($fileName))
		{			
			$xmlStr = file_get_contents($fileName);
			
			$res = ArisI18NHelper::parseXmlFromString($xmlStr, $tagMapping);
		}
		
		return $res;
	}
	
	function parseXmlFromString($xmlStr, &$tagMapping)
	{
		$xml = AriXmlHelper::getXML($xmlStr, false);
		
		return ArisI18NHelper::parseXml($xml, $tagMapping);
	}
	
	function parseXml($xml, &$tagMapping)
	{
		$res = array();
		if (!empty($xml))
		{
			if ($tagMapping == null)
			{
				$tagMapping = new ArisI18NTagMapping();
			}
			
			$itemTagName = $tagMapping->ItemTagName;
			$itemIdAttr = $tagMapping->ItemIdAttrName; 
			$items = isset($xml->document->$itemTagName) ? $xml->document->$itemTagName : null;
			if (!empty($items))
			{
				foreach ($items as $item)
				{
					$attrs = $item->attributes();
					$resAttrs = array();
					foreach ($attrs as $key => $value)
					{
						if ($key != $itemIdAttr)
						{
							$resAttrs[$key] = (string)$value;
						}
					}
					
					$id = AriXmlHelper::getAttribute($item, $itemIdAttr);

					$res[$id] = array();
					if (!empty($resAttrs)) 
						$res[$id][ARI_I18N_ATTRS] = $resAttrs;

					foreach ($item->children() as $child)
					{
						$res[$id][AriXmlHelper::getTagName($child)] = AriXmlHelper::getData($child);
					}
				}
			}
		}

		return $res;
	}
	
	function loadXmlResource($locale, $resourceDir)
	{
		$locale = $locale;
		$resFile = ArisI18NHelper::getResourceFileName($resourceDir, $locale);
		$xml = null;
		if (file_exists($resFile))
		{
			$xml = AriXmlHelper::getXML($resFile, true);
		}
		
		return $xml;
	}
	
	function saveXmlResource($xml, $locale, $resourceDir)
	{
		if (!empty($xml))
		{
			$resFile = ArisI18NHelper::getResourceFileName($resourceDir, $locale);
			$handle = fopen($resFile, 'w');
			fwrite($handle, sprintf('%s%s%s',
				ARI_I18N_TEMPLATE_XML,
				"\r\n",
				AriXmlHelper::toString($xml->document)));
			fclose($handle);			
		}
	}
	
	function createXmlFromData($data, $tagMapping = null)
	{
		$xml = AriXmlHelper::getXML('<resource />', false);

		if (!empty($data))
		{
			if (is_null($tagMapping))
			{
				$tagMapping = new ArisI18NTagMapping();
			}
			
			$rootElement =& $xml->document;
			foreach ($data as $id => $resItem)
			{
				$item =& $rootElement->addChild($tagMapping->ItemTagName);
				if (isset($resItem[ARI_I18N_ATTRS]))
				{
					foreach ($resItem[ARI_I18N_ATTRS] as $name => $value)
					{
						$item->addAttribute($name, $value);
					}
				}
				
				$item->addAttribute($tagMapping->ItemIdAttrName, $id);
				foreach ($resItem as $name => $value)
				{
					if ($name != ARI_I18N_ATTRS && !empty($value))
					{
						$child =& $item->addChild($name);
						AriXmlHelper::setData($child, $value);
					}
				}
			}
		}
		
		return $xml;
	}
	
	function getMostPrefLocale($resourceDir, $locale, $defaultLocale = null)
	{
		$resFile = ArisI18NHelper::getResourceFileName($resourceDir, $locale);
		$resLocale = '';
		if (!file_exists($resFile))
		{
			$splitter = ARI_I18N_SPLITTER;
			$part = explode($splitter, $locale);
			$part = $part[0];
			$resFile = ArisI18NHelper::getResourceFileName($resourceDir, $part);
			if (file_exists($resFile))
			{
				$resLocale = $part;
			}
			else
			{
				$resFile = ArisI18NHelper::getResourceFileName($resourceDir, 
					$part . $splitter . $part);
				if (file_exists($resFile))
				{
					$resLocale = $part . $splitter . $part;
				}
			}
		}
		else
		{
			$resLocale = $locale;
		}
		
		if (empty($resLocale) && !empty($defaultLocale))
		{
			$resLocale = ArisI18NHelper::getMostPrefLocale($resourceDir, $defaultLocale);
		}
		
		return $resLocale;
	}
	
	function getResourceFileName($resourceDir, $locale)
	{
		return $resourceDir . '/' . $locale . '.xml';
	}
	
	function mergeDataResource($baseData, $newTagData, $mergedTag)
	{
		$mergeData = $baseData;
		if (!empty($baseData))
		{
			foreach ($mergeData as $id => $resItem)
			{
				foreach ($mergedTag as $tag)
				{
					if (isset($newTagData[$tag]) && isset($newTagData[$tag][$id]))
					{
						$mergeData[$id][$tag] = $newTagData[$tag][$id]; 
					}
				}
			}
		}
		
		return $mergeData;
	}
	
	function mergeResources($baseRes, $newRes)
	{
		$mergeRes = $baseRes;
		if (empty($baseRes) || empty($newRes)) return $mergeRes;

		foreach ($baseRes as $id => $resItem)
		{
			if (!isset($newRes[$id]) || empty($resItem)) continue ;

			foreach ($resItem as $key => $value)
			{
				if (isset($newRes[$id][$key]))
				{
					if ($key != ARI_I18N_ATTRS)
					{
						$mergeRes[$id][$key] = $newRes[$id][$key];
					}
					else
					{
						if (!empty($value))
						{
							foreach ($value as $attrKey => $attrValue)
							{
								if (isset($newRes[$id][$key][$attrKey]))
								{
									$mergeRes[$id][$key][$attrKey] = $newRes[$id][$key][$attrKey];
								}
							}
						}
					}
				}
			}
		}
		
		return $mergeRes;
	}
}

define ('ARI_I18N_RES_ID', '_ariI18NResources');
class ArisI18NCache
{
	var $_cacheDir;
	var $_resourceDir;
	var $_locale;
	var $_tagMapping;
	
	function ArisI18NCache($locale, $resourceDir, $cacheDir, &$tagMapping)
	{
		if ($tagMapping == null)
		{
			$tagMapping = new ArisI18NTagMapping();
		}
		
		$this->_tagMapping = $tagMapping;
		$this->_locale = $locale;
		$this->_cacheDir = $cacheDir;
		$this->_resourceDir = $resourceDir;
	}
	
	function _getCacheDir()
	{
		return $this->_cacheDir;
	}
	
	function _getResourceDir()
	{
		return $this->_resourceDir;
	}
	
	function _getLocale()
	{
		return $this->_locale;
	}
	
	function getResource()
	{
		static $cachedRes = array();
		
		$res = array();
		$locale = $this->_getLocale();
		if (isset($cachedRes[$locale]))
		{
			$res = $cachedRes[$locale];
		}
		else
		{
			$cachedFile = $this->_getCachedFileName($locale);
			$resFile = ArisI18NHelper::getResourceFileName($this->_getResourceDir(), $locale);
			$updateSource = true;
			if (file_exists($resFile))
			{
				if (file_exists($cachedFile))
				{
					$resMTime = filemtime($resFile);
					$cacheMTime = filemtime($cachedFile);
					if ($cacheMTime > $resMTime)
					{
						$updateSource = false;
						require_once $cachedFile;
						$res = $GLOBALS[ARI_I18N_RES_ID][$locale];
					}
				}
				
				if ($updateSource)
				{
					$tagMapping = $this->_getTagMapping();
					$res = ArisI18NHelper::loadResource($locale, $this->_getResourceDir(), $tagMapping);
					
					$handle = fopen($cachedFile, 'w');
					fwrite($handle, sprintf(ARI_I18N_CACHE_TEMPLATE,
						ARI_I18N_RES_ID,
						$locale,
						var_export($res, true)));
					fclose($handle);

				}
			}
		}
		
		return $res;
	}
	
	function _getTagMapping()
	{
		return $this->_tagMapping;
	}
	
	function _getCachedFileName()
	{
		return $this->_getCacheDir() . '/' . $this->_getLocale() . '.php';
	}
}
?>
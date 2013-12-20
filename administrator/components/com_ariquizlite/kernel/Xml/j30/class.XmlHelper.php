<?php 
/*
 *
 * @package		ARI Framework
 * @author		ARI Soft
 * @copyright	Copyright (c) 2011 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

(defined('_JEXEC') && defined('ARI_FRAMEWORK_LOADED')) or die;

class AriXmlHelper extends AriXmlHelperBase
{
	function getXML($data, $isFile = true)
	{
		$xmlHandler = null;
		$xmlDoc = null;
		if ($isFile)
			$xmlDoc = simplexml_load_file($data);
		else
		{
			$xmlDoc = simplexml_load_string($data);
		}
			
		if (!is_null($xmlDoc))
		{
			$xmlHandler = new stdClass();
			$xmlHandler->document = $xmlDoc;
		}

		return $xmlHandler;
	}
	
	function getData($rootNode, $tagName = null, $default = null)
	{	
		$node = $tagName
			? AriXmlHelperBase::getSingleNode($rootNode, $tagName)
			: $rootNode;

		if (is_null($node))
			return $default;

		return (string)$node;
	}

	function setData($node, $data)
	{
		if (!is_null($node))
			$node->{0} = (string)$data;
	}

	function getAttribute($node, $attrName, $default = null)
	{
		$val = isset($node[$attrName]) ? (string)$node[$attrName] : $default;

		return $val;
	}

	function getTagName($node)
	{
		return $node->getName();
	}
	
	function toString($doc)
	{
		if (is_null($doc))
			return '';
			
		return $doc->asXML();
	}
}
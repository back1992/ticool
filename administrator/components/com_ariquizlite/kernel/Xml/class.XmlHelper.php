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

class AriXmlHelperBase
{ 
	function getXML($data, $isFile = true)
	{
		return null;
	}
	
	function &getNode(&$rootNode, $tagName)
	{
		$node = null;
		if (isset($rootNode->$tagName)) 
			$node =& $rootNode->$tagName;

		return $node;
	}
	
	function &getSingleNode(&$rootNode, $tagName)
	{
		$node =& AriXmlHelperBase::getNode($rootNode, $tagName);
		if ($node != null && is_array($node))
			$node =& $node[0];
		
		return $node;
	}
	
	function getData(&$rootNode, $tagName = null, $default = null)
	{
		$node = $tagName
			? AriXmlHelperBase::getSingleNode($rootNode, $tagName)
			: $rootNode;

		if (is_null($node))
			return $default;

		return $node->data();
	}
	
	function setData($node, $data)
	{
		$node->setData($data);
	}
	
	function getAttribute($node, $attrName, $default = null)
	{
		$val = $node->attributes($attrName);

		if (is_null($val))
			$val = $default;
		else
			$val = $val;
			
		return $val;
	}
	
	function getTagName($node)
	{
		return $node->name();
	}

	function toString($doc)
	{
		if (is_null($doc))
			return '';
			
		return $doc->toString();
	}
}

if (J3_0)
	require_once dirname(__FILE__) . DS . 'j30' . DS . 'class.XmlHelper.php';
else if (J1_6)
	require_once dirname(__FILE__) . DS . 'j16' . DS . 'class.XmlHelper.php';
else
	require_once dirname(__FILE__) . DS . 'j15' . DS . 'class.XmlHelper.php';
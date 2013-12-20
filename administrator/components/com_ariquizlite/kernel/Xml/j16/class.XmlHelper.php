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
		$xmlHandler = JFactory::getXMLParser('Simple');
		if ($isFile)
			$xmlHandler->loadFile($data);
		else
			$xmlHandler->loadString($data);
		
		return $xmlHandler;
	}
}
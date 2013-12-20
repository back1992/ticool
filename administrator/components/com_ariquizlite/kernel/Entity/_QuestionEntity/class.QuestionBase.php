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

defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');

class QuestionBase 
{
	function getDataFromXml($xml, $decodeHtmlEntity = false)
	{
		return null;
	}
	
	function getFrontXml()
	{
		return null;
	}
	
	function getXml()
	{
		return null;
	}
	
	function isCorrect($xml, $baseXml)
	{
		return false;
	}
}
?>

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

class AriText
{
	function htmlStrLen($str) 
	{
  		$chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

  		return count($chars);
	}

	function htmlSubStr($str, $start = 0, $length = null) 
	{
  		if ($length === 0) return ""; //stop wasting our time ;)

  		if (strpos($str, '&') === false) 
  		{
  			return is_null($length)
  				? substr($str, $start)
  				: substr($str, $start, $length);
  		}

  		$chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
		$html_length = count($chars);

  		if ($html_length === 0 ||
       		$start >= $html_length ||
       		(isset($length) && ($length <= -$html_length)))
     	{
    		return '';
     	}

  		if ($start >= 0) 
  		{
    		$real_start = $chars[$start][1];
  		} 
  		else 
  		{
    		$start = max($start,-$html_length);
    		$real_start = $chars[$html_length+$start][1];
  		}

		if (!isset($length))
    		return substr($str, $real_start);
  		else if ($length > 0) 
  		{
    		if ($start + $length >= $html_length) 
    		{
      			return substr($str, $real_start);
    		} 
    		else 
    		{
      			return substr($str, $real_start, $chars[max($start,0) + $length][1] - $real_start);
    		}
  		} 
  		else 
  		{
      		return substr($str, $real_start, $chars[$html_length + $length][1] - $real_start);
  		}
	}
}
?>
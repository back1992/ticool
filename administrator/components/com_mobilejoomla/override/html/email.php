<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version		1.2.8
 * @license		GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright	(C) 2008-2013 Kuneri Ltd.
 * @date		November 2013
 */
defined('_JEXEC') or die('Restricted access');

abstract class JHtmlEmail
{
	public static function cloak($mail, $mailto = 1, $text = '', $email = 1)
	{
		if(strpos($mail, "'") !== false)
			return $mail;

		if(empty($text))
			$text = $mail;

		if($email)
			$text = str_replace(array('@', '.'),
			                    array('&#8203;<bdo dir=\'ltr\'>&#64;<bdo>&#8203;', '&#46;'),
			                    $text);

		if($mailto)
			$html = '<a href=\'javascript:void(location.href=&quot;mai&quot;+&quot;lto:'
			        . str_replace(array('@', '.'),
			                      array('&quot;+&quot;&#92;100&quot;+&quot;', '.&quot;+&quot;'),
			                      $mail)
			        . '&quot;)\'>' . $text . '</a>';
		else
			$html = $text;

		return $html;
	}
}

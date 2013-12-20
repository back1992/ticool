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

/** @var $params JRegistry */
/** @var $links array */

echo $params->get('show_text', ' ');

$parts = array();
foreach($links as $link)
{
	if($link['url'])
		$parts[] = '<a href="'.$link['url'].'">'.$link['text'].'</a>';
	else
		$parts[] = $link['text'];
}

echo implode(' | ', $parts);

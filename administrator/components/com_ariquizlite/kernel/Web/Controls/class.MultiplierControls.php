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

class WebControls_MultiplierControls
{
	function getData($containerId, $keys, $idKey = null, $stripSlashes = false)
	{
		$i = 0;
		$data = array();

		while (WebControls_MultiplierControls::isSetTemplateItem($containerId, $i))
		{
			$dataItem = array();

			if (!empty($keys))
			{
				foreach ($keys as $key)
				{
					$itemKey = WebControls_MultiplierControls::getTemplateItemKey($key, $i);
					$dataItem[$key] = null;
					if (isset($_REQUEST[$itemKey]))
					{
						$dValue = $_REQUEST[$itemKey];
						if ($stripSlashes && get_magic_quotes_gpc())
						{
							$dValue = stripslashes($dValue);
						}
						
						$dataItem[$key] = $dValue;
					}
					else if (isset($_FILES[$itemKey]))
					{
						$dataItem[$key] = $_FILES[$itemKey];
					}
				}
			}

			$itemIdKey = !empty($idKey) ? WebControls_MultiplierControls::getTemplateItemKey($idKey, $i) : null;
			if (!empty($itemIdKey) && isset($dataItem[$itemIdKey]))
			{
				$data[$dataItem[$itemIdKey]] = $dataItem;
			}
			else
			{
				$data[] = $dataItem;
			}

			++$i;
		}

		return $data;
	}

	function isSetTemplateItem($containerId, $index)
	{
		return isset($_REQUEST[$containerId . '_hdnstatus_' . $index]);
	}

	function getTemplateItemKey($key, $index)
	{
		return $key . '_' . $index;
	}
}
?>
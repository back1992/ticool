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

class EntityFactory 
{
	function createInstance($className, $group = null)
	{
		$path = sprintf('%s%sclass.%s.php',
			ARI_ENTITYFACTORY_DIR2,
			$group != null ? $group . '/' : '',
			strtolower($className)); 
		if (preg_match('/^[A-z]+$/', $className) && file_exists($path))
		{
			require_once $path;
			if (class_exists($className))
			{
				$args = null;
				$numargs = func_num_args();
				$funcArgs = '';
				if ($numargs > 2)
				{
					$args = func_get_args();
					$args = array_slice($args, 2);
					for ($i = 0; $i < $numargs - 2; $i++)
					{
						$funcArgs .= ',$args[' . $i . ']';
					}
				}

				$inst = eval(sprintf('$db =& JFactory::getDBO();;return new %s($db%s);', 
					$className,
					$funcArgs));
				
				return $inst;
			}
		}

		return null;
	}
}

define ('ARI_ENTITYFACTORY_DIR2', dirname(__FILE__) . '/');
?>
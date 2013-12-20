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

define ('UAH_USERS_GROUP', 'USERS');
define ('UAH_REGISTERED_GROUP', 'Registered');
define ('UAH_REGISTERED_GROUP_ID', 18);
define ('UAH_FRONTEND_GROUP', 'Public Frontend');

class UserAccessHelper 
{
	var $_acl;
	
	function UserAccessHelper($acl)
	{
		$this->_acl = $acl;
	}
	
	function getGroupsFlatTree($group = UAH_USERS_GROUP, $addEmpty = true)
	{
		$gTree = $this->_acl->get_group_children_tree(null, $group, true);
		
		return $gTree;
	}
	
	function isChildOfGroupByName($childGroup, $parentGroup)
	{
		$acl = $this->_acl;

		return $acl->is_group_child_of($childGroup, $parentGroup);
	}
	
	function isChildOfGroup($childGroup, $parentGroupId)
	{
		$acl = $this->_acl;

		return $acl->is_group_child_of($childGroup, $acl->get_group_name($parentGroupId));
	}
	
	function isGroupOrChildOfGroup($childGroup, $parentGroupId)
	{
		$acl = $this->_acl;
		
		$parentGroup = $acl->get_group_name($parentGroupId);
		if ($parentGroup == $childGroup ||
			$this->isChildOfGroupByName($childGroup, $parentGroup))
		{
			return true;
		}
		
		return false;
	}
}
?>
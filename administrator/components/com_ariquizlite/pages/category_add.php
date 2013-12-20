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

class category_addAriPage extends AriAdminQuizPageBase
{
	function execute()
	{	
		$categoryId = JRequest::getInt('categoryId');
		$this->addVar('categoryId', $categoryId);
		$this->addVar('category', $this->_getCategory($categoryId));
		
		parent::execute();
	}
	
	function _getCategory($categoryId)
	{
		$category = null;
		if ($categoryId != 0)
		{
			$category = $this->_quizController->call('getCategory', $categoryId);
		}
		else
		{
			$category = EntityFactory::createInstance('CategoryEntity', ARI_ENTITY_GROUP);
		}
		
		return $category;
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('save', 'clickSave');
		$this->_registerEventHandler('apply', 'clickApply');
		$this->_registerEventHandler('cancel', 'clickCancel');
	}
	
	function clickCancel($eventArgs)
	{
		AriQuizWebHelper::cancelAction('category_list');
	}
	
	function clickSave($eventArgs)
	{
		$category = $this->_saveCategory();
		if (!$this->_isError())
		{
			 AriQuizWebHelper::preCompleteAction('Complete.CategorySave', array('task' => 'category_list'));
		}				
	}
	
	function clickApply($eventArgs)
	{
		$category = $this->_saveCategory();
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.CategorySave', 
				array('task' => 'category_add', 'categoryId' => $category->CategoryId, 'hidemainmenu' => 1));
		}
	}
	
	function _saveCategory()
	{
		$user =& JFactory::getUser();
		$ownerId = $user->get('id');
		$fields = JRequest::getVar('zCategory');

		return $this->_quizController->call('saveCategory',
			JRequest::getInt('categoryId'),
			$fields, 
			$ownerId);
	}	
}
?>
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

jimport('joomla.html.pagination');

class category_listAriPage extends AriAdminQuizPageBase
{
	function execute()
	{
		$limit = AriQuizHelper::_getLimit('catlist'); 
		$limitStart = AriQuizHelper::_getLimitStart('catlist');
		$sortInfo = ArisSortingHelper::getCurrentSorting('CategoryName');
		$categoryCount = $this->_quizController->call('getCategoryCount');
		if ($categoryCount <= $limitStart)
		{
			$limitStart = 0;
			AriQuizHelper::setLimitStart($limitStart, 'catlist');
		}
		
		$categoryList = $this->_quizController->call('getCategoryList', $sortInfo, $limitStart, $limit);
		if ($this->_isError())
		{
			return ; 
		}
		
		$this->addVar('pageNav', new JPagination($categoryCount, $limitStart, $limit));
		$this->addVar('categoryList', $categoryList);

		parent::execute();
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('delete', 'clickDelete');
	}
	
	function clickDelete($eventArgs)
	{
		$this->_quizController->call('deleteCategory', JRequest::getVar('categoryId', 0));
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.CategoryDelete', array('task' => 'category_list'));
		}
	}
}
?>
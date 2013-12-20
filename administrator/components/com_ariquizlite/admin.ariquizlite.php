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

defined('_JEXEC') or die('Restricted access');

$version = new JVersion();
if (!defined('J16')) 
	define('J16', version_compare($version->getShortVersion(), '1.6.0', '>='));

global $Itemid;
$option = $GLOBALS['option'] = 'com_ariquizlite';
if (empty($Itemid) && JRequest::getVar('Itemid') != null)
	$Itemid = $GLOBALS['Itemid'] = JRequest::getInt('Itemid');

$mainframe = JFactory::getApplication();

$basePath = JPATH_SITE . '/administrator/components/' . $option . '/';

require_once ($basePath . 'utils/constants.php');
require_once ($basePath . 'kernel/class.AriKernel.php');

AriKernel::import('Controllers.QuizController');
AriKernel::import('Controllers.ResultController');
AriKernel::import('Controllers.TextTemplateController');
AriKernel::import('Controllers.LicenseController');
AriKernel::import('Web.TaskManager');
AriKernel::import('Web.Controls.MultiplierControls');
AriKernel::import('Web.AdminQuizPageBase');
AriKernel::import('Web.UserQuizPageBase');
AriKernel::import('Date.Date');
AriKernel::import('Text.Text');
AriKernel::import('I18N.I18N');
AriKernel::import('Security.Security');
AriKernel::import('Entity.EntityFactory2');
AriKernel::import('Web.Utils.QuizWebHelper');
AriKernel::import('Web.Utils.Util');

AriQuizWebHelper::createBackendI18N(); 
$oldTask = JRequest::getString('oldTask', '');

AriTaskManager::registerTaskGroup('ajax', $basePath . 'ajax/');
AriTaskManager::registerTaskGroup('', $basePath . 'pages/', 
	array(ARI_TM_KEY_TEMPLATEDIR => $basePath . 'templates/', ARI_TM_KEY_TEMPLATEEXT => 'html.php'));

if (!AriTaskManager::doTask(JRequest::getString('task'))) $mainframe->redirect('index.php?option=' . $option . '&task=quiz_list');

class AriQuizHelper
{	
	function getQuestionTemplatePath($questionType, $dir = 'questions')
	{
		$path = '';
		if (!empty($questionType) && preg_match('/^[A-z]+$/', $questionType))
		{
			$path = dirname(__FILE__) . '/templates/' . $dir . '/' . strtolower($questionType) . '.html.php';
			if (!file_exists($path))
			{
				$path = '';
			}
		}
		
		return $path;
	}

	function getSortField($defField = null)
	{
		$sortInfo = ArisSortingHelper::getCurrentSorting('');

		return !empty($sortInfo->sortField)
			? $sortInfo->sortField
			: $defField;
	}
	
	function getSortDirection($sortField)
	{
		$sortInfo = ArisSortingHelper::getCurrentSorting('');
		$sortDirection = ARIS_SORTING_DIR_ASC;
		if ($sortInfo != null && $sortInfo->sortField == $sortField)
		{
			$sortDirection = $sortInfo->sortDirection;
		}
		
		return $sortDirection;
	}
	
	function _getLimit($prefix = null)
	{
		$mainframe =& JFactory::getApplication();

		return AriQuizHelper::_getSpecificState($prefix, 'vll', 'limit', $mainframe->getCfg('list_limit')); 
	}
	
	function setLimit($value, $prefix = null)
	{
		AriQuizHelper::setSpecificState($prefix, 'vll', $value);
	}
	
	function _getLimitStart($prefix = null)
	{
		return AriQuizHelper::_getSpecificState($prefix, 'vls', 'limitstart', 0);
	}
	
	function setLimitStart($value, $prefix = null)
	{
		AriQuizHelper::setSpecificState($prefix, 'vls', $value);
	}
	
	function _getSpecificState($prefix, $postfix, $reqName, $defaultValue)
	{
		global $oldTask;
		
		$mainframe =& JFactory::getApplication();
		
		$task = JRequest::getVar('task', '', 'method', 'string');
		if ($prefix === null) $prefix = $task;

		$key = AriQuizHelper::_getSpecificKey($prefix, $postfix);
		$value = (empty($oldTask) || $oldTask == $task) 
			? $mainframe->getUserStateFromRequest($key, $reqName, $defaultValue)
			: $mainframe->getUserState($key);
			
		if ($value === null) $value = $defaultValue;
		
		return $value;
	}
	
	function setSpecificState($prefix, $postfix, $value)
	{
		$mainframe =& JFactory::getApplication();
		
		$key = AriQuizHelper::_getSpecificKey($prefix, $postfix);
		
		$mainframe->setUserState($key, $value);
	}
	
	function _getSpecificKey($prefix, $postfix)
	{
		global $option;
		
		return "{$option}_{$prefix}_{$postfix}"; 
	}
}
?>
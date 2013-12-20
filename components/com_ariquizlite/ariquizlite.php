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

global $Itemid;
$option = $GLOBALS['option'] = 'com_ariquizlite';
if (empty($Itemid) && JRequest::getVar('Itemid') != null)
	$Itemid = $GLOBALS['Itemid'] = JRequest::getInt('Itemid');
$basePath = JPATH_SITE . '/components/' . $option . '/';
$adminBasePath = JPATH_SITE . '/administrator/components/' . $option . '/';

require_once ($adminBasePath . 'utils/constants.php');
require_once ($adminBasePath . 'kernel/class.AriKernel.php');

AriKernel::import('Controllers.QuizController');
AriKernel::import('Controllers.ResultController');
AriKernel::import('Controllers.TextTemplateController');
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
AriKernel::import('Xml.SimpleXml');

AriQuizWebHelper::createFrontendI18N();

AriTaskManager::registerTaskGroup('', $basePath . 'frontend_pages/', 
	array(ARI_TM_KEY_TEMPLATEDIR => $basePath . 'view/', ARI_TM_KEY_TEMPLATEEXT => 'html.php'));

if (!AriTaskManager::doTask(JRequest::getString('task'))) AriTaskManager::doTask('quiz_list');

class AriQuizFrontHelper
{	
	function addTmplToLink($link)
	{
		$tmpl = JRequest::getString('tmpl', '');
		if ($tmpl) $link .= '&tmpl=' . $tmpl;
		
		return $link;
	}
	
	function getQuestionTemplatePath($questionVersion)
	{
		$questionType = $questionVersion->QuestionType->ClassName;
		$path = '';
		if (!empty($questionType) && preg_match('/^[A-z]+$/', $questionType))
		{
			$path = dirname(__FILE__) . '/view/questions/' . strtolower($questionType) . '.html.php';
			if (!file_exists($path))
			{
				$path = '';
			}
		}
		
		return $path;
	}
	
	function _redirectToInfo($mid, $rurl = '', $params = array())
	{
		global $option, $Itemid;
		
		$mainframe =& JFactory::getApplication();
		
		$params['option'] = $option;
		$params['task'] = 'quiz_info';
		$params['mid'] = $mid;
		$params['Itemid'] = $Itemid;
		if (!empty($rurl)) $params['rurl'] = $rurl;
		
		$url = 'index.php?';
		$urlParams = '';
		foreach ($params as $key => $value)
		{
			if (!empty($urlParams)) $urlParams .= '&';
			$urlParams .= $key . '=' . urlencode($value);
		}
		
		$mainframe->redirect(
			AriQuizFrontHelper::addTmplToLink($url . $urlParams));
	}
	
	function _checkQuizAvailability($userId, $ticketId)
	{
		global $option;
		
		$user =& JFactory::getUser();
		$quizController = new AriQuizController();
		$canTake = $quizController->canTakeQuizByTicketId($ticketId, $userId, $user->get('usertype'));
		if (!$canTake)
		{
			AriQuizFrontHelper::_redirectToInfo('FrontEnd.QuizNotAvailable');
			return false;
		}
		
		return true;
	}
} 
?>
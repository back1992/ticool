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

$mainframe =& JFactory::getApplication();
if ($mainframe->isAdmin())
{
	$basePath = JPATH_SITE . '/administrator/components/' . $option . '/';
	
	require_once dirname(__FILE__) . '/toolbar.ariquizlite.html.php';
	//require_once($mainframe->getPath('toolbar_html'));
	//require_once($mainframe->getPath('toolbar_default'));
	
	require_once ($basePath . 'kernel/class.AriKernel.php');
	AriKernel::import('Web.TaskManager');
	
	$task = empty($task) ? JRequest::getString('task') : $task;
	$clearTask = AriTaskManager::getTask($task);
	
	switch ($clearTask)
	{
		case 'quiz_add':
			AriQuizMenu::updateQuizMenu();
			break;
	
		case 'category_add':
			AriQuizMenu::updateCategoryMenu();
			break;
			
		case 'category_list':
			AriQuizMenu::categoryListMenu();
			break;
	
		case 'question_list':
			AriQuizMenu::questionListMenu();
			break;
			
		case 'apply_qtype':
		case 'question_add':
			AriQuizMenu::updateQuestionMenu();
			break;
			
		case 'questioncategory_list':
			AriQuizMenu::questionCategoryListMenu();
			break;
			
		case 'questioncategory_add':
			AriQuizMenu::updateQuestionCategoryMenu();
			break;
			
		case 'qtemplate_list':
			AriQuizMenu::qtemplateListMenu();
			break;
			
		case 'qtemplate_add':
			AriQuizMenu::updateQTemplateMenu();
			break;
			
		case 'results':
			AriQuizMenu::resultsMenu();
			break;
			
		case 'result':
			AriQuizMenu::resultMenu();
			break;
			
		case 'question':
			AriQuizMenu::resultQuestionMenu();
			break;
			
		case 'quiz_list':
			AriQuizMenu::quizListMenu();
			break;
			
		case 'texttemplate_list':
			AriQuizMenu::gTemplateListMenu();
			break;
			
		case 'texttemplate_add':
			AriQuizMenu::gTemplateUpdateMenu();
			break;
			
		case 'templates':
			AriQuizMenu::templateMenu();
			break;
			
		case 'template_add':
			AriQuizMenu::templateAddMenu();
			break;
			
		case 'lang_backend':
			AriQuizMenu::blangListMenu();
			break;
			
		case 'blang_add':
			AriQuizMenu::blangUpdateMenu();
			break;
			
		case 'lang_frontend':
			AriQuizMenu::flangListMenu();
			break;
			
		case 'flang_add':
			AriQuizMenu::flangUpdateMenu();
			break;
			
		case 'help':
			AriQuizMenu::backQuizListMenu('Title.Help');
			break;
			
		case 'about':
			AriQuizMenu::backQuizListMenu('Title.About');
			break;
			
		case 'faq':
			AriQuizMenu::backQuizListMenu('Title.FAQ');
			break;
			
		case 'license':
			AriQuizMenu::backQuizListMenu('Title.License');
			break;
	}
	
	AriQuizMenu::addSubMenu($clearTask);
}
?>
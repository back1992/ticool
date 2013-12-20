<?php
defined('_JEXEC') or die('Restricted access');

global $Itemid;
$option = $GLOBALS['option'] = 'com_ariquizlite';
if (empty($Itemid) && JRequest::getVar('Itemid') != null)
	$Itemid = $GLOBALS['Itemid'] = JRequest::getInt('Itemid');

$basePath = JPATH_SITE . '/administrator/components/' . $option . '/';

require_once($basePath . 'utils/constants.php');
require_once ($basePath . 'kernel/class.AriKernel.php');

AriKernel::import('I18N.I18N');
AriKernel::import('Controllers.ResultController');

AriQuizWebHelper::createBackendI18N();

class AriQuizMenu
{
	function _addResourceTitle($resKey, $img = 'generic.png')
	{
		AriQuizMenu::_addTitle(AriQuizWebHelper::getResValue($resKey), $img);
	}
	
	function _addTitle($title, $img = 'generic.png')
	{
		JToolBarHelper::title($title, $img);
	}
	
	function resultsMenu()
	{
		global $option;
		
		AriQuizMenu::_addResourceTitle('Title.QuizResultList');
		
		JToolbarHelper::apply('results', AriQuizWebHelper::getResValue('Toolbar.Filters'));
		JToolbarHelper::spacer();
		JToolbarHelper::divider();
		JToolbarHelper::spacer();
		
		JToolbarHelper::custom('results$tocsv', 'archive.png', 'archive.png', AriQuizWebHelper::getResValue('Toolbar.ExportToCSV'), true);
		JToolbarHelper::spacer();

		JToolbarHelper::divider();
		JToolbarHelper::spacer();
		JToolbarHelper::back(AriQuizWebHelper::getResValue('Toolbar.QuizList'), sprintf('index.php?option=%s&task=%s',
			$option,
			'quiz_list'));
		JToolbarHelper::spacer();
	}
	
	function quizListMenu()
	{
		AriQuizMenu::_addResourceTitle('Title.QuizList');

		JToolbarHelper::apply('quiz_list', AriQuizWebHelper::getResValue('Toolbar.Filters'));
		JToolbarHelper::spacer();
		JToolbarHelper::divider();
		JToolbarHelper::spacer();
		JToolbarHelper::custom('quiz_list$questions', 'edit', 'edit', AriQuizWebHelper::getResValue('Label.Questions'));
		JToolbarHelper::spacer();
		JToolbarHelper::divider();
		JToolbarHelper::spacer();
		JToolbarHelper::publishList('quiz_list$activate', AriQuizWebHelper::getResValue('Button.Activate'));
		JToolbarHelper::spacer();
		JToolbarHelper::unpublishList('quiz_list$deactivate', AriQuizWebHelper::getResValue('Button.Deactivate'));
		JToolbarHelper::spacer();
		JToolbarHelper::addNew('quiz_add', AriQuizWebHelper::getResValue('Button.Add'));
		JToolbarHelper::spacer();
		JToolbarHelper::deleteList(
			AriQuizWebHelper::getResValue('Warning.QuizRemove'), 
			'quiz_list$delete', 
			AriQuizWebHelper::getResValue('Button.Remove'));
		JToolbarHelper::spacer();
	}

	function categoryListMenu()
	{
		AriQuizMenu::_addResourceTitle('Title.CategoryList');
		
		JToolbarHelper::addNew('category_add', AriQuizWebHelper::getResValue('Button.Add'));
		JToolbarHelper::spacer();
		JToolbarHelper::deleteList(
			AriQuizWebHelper::getResValue('Warning.CategoryRemove'), 
			'category_list$delete', 
			AriQuizWebHelper::getResValue('Button.Remove'));
		JToolbarHelper::spacer();
	}
	
	function questionListMenu()
	{
		global $option;
		
		AriQuizMenu::_addResourceTitle('Title.QuestionList');
		
		JToolbarHelper::addNew('question_add', AriQuizWebHelper::getResValue('Button.Add'));
		JToolbarHelper::spacer();
		JToolbarHelper::deleteList(
			AriQuizWebHelper::getResValue('Warning.QuestionRemove'), 
			'question_list$delete', 
			AriQuizWebHelper::getResValue('Button.Remove'));
		JToolbarHelper::spacer();
		JToolbarHelper::divider();
		JToolbarHelper::spacer();
		JToolbarHelper::back(AriQuizWebHelper::getResValue('Toolbar.QuizList'), sprintf('index.php?option=%s&task=%s',
			$option,
			'quiz_list'));
		JToolbarHelper::spacer();
	}
	
	function questionCategoryListMenu()
	{
		AriQuizMenu::_addResourceTitle('Title.QuestionCategoryList');
		
		JToolbarHelper::addNew('questioncategory_add', AriQuizWebHelper::getResValue('Button.Add'));
		JToolbarHelper::spacer();
		JToolbarHelper::deleteList(
			AriQuizWebHelper::getResValue('Warning.QCategoryRemove'), 
			'questioncategory_list$delete',
			AriQuizWebHelper::getResValue('Button.Remove'));
		JToolbarHelper::spacer();
	}
	
	function updateQuestionMenu()
	{
		$questionId = JRequest::getInt('questionId');
		AriQuizMenu::_addTitle(sprintf('%s : %s',
			AriQuizWebHelper::getResValue('Label.Question'),
			AriQuizWebHelper::getResValue($questionId ? 'Label.UpdateItem' : 'Label.AddItem')));
		
		JToolbarHelper::save('question_add$save', AriQuizWebHelper::getResValue('Toolbar.Save'));
		JToolbarHelper::spacer();
		JToolbarHelper::apply('question_add$apply', AriQuizWebHelper::getResValue('Toolbar.Apply'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::cancel('question_add$cancel', AriQuizWebHelper::getResValue('Toolbar.Cancel'));
		JToolbarHelper::spacer();
	}
	
	function updateQuizMenu()
	{
		$quizId = JRequest::getInt('quizId');
		AriQuizMenu::_addTitle(sprintf('%s : %s',
			AriQuizWebHelper::getResValue('Label.Quiz'),
			AriQuizWebHelper::getResValue($quizId ? 'Label.UpdateItem' : 'Label.AddItem')));
		
	    JToolbarHelper::save('quiz_add$save', AriQuizWebHelper::getResValue('Toolbar.Save'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::apply('quiz_add$apply', AriQuizWebHelper::getResValue('Toolbar.Apply'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::cancel('quiz_add$cancel', AriQuizWebHelper::getResValue('Toolbar.Cancel'));
	    JToolbarHelper::spacer();
	}

	function updateCategoryMenu()
	{
		$categoryId = JRequest::getInt('categoryId');
		AriQuizMenu::_addTitle(sprintf('%s : %s',
			AriQuizWebHelper::getResValue('Label.Category'),
			AriQuizWebHelper::getResValue($categoryId ? 'Label.UpdateItem' : 'Label.AddItem')));
		
	    JToolbarHelper::save('category_add$save', AriQuizWebHelper::getResValue('Toolbar.Save'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::apply('category_add$apply', AriQuizWebHelper::getResValue('Toolbar.Apply'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::cancel('category_add$cancel', AriQuizWebHelper::getResValue('Toolbar.Cancel'));
	    JToolbarHelper::spacer();
	}
	
	function updateQuestionCategoryMenu()
	{
		$qCategoryId = JRequest::getInt('qCategoryId');
		AriQuizMenu::_addTitle(sprintf('%s : %s',
			AriQuizWebHelper::getResValue('Title.QuestionCategory'),
			AriQuizWebHelper::getResValue($qCategoryId ? 'Label.UpdateItem' : 'Label.AddItem')));
		
	    JToolbarHelper::save('questioncategory_add$save', AriQuizWebHelper::getResValue('Toolbar.Save'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::apply('questioncategory_add$apply', AriQuizWebHelper::getResValue('Toolbar.Apply'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::cancel('questioncategory_add$cancel', AriQuizWebHelper::getResValue('Toolbar.Cancel'));
	    JToolbarHelper::spacer();
	}
	
	function backMenu($titleResKey = '')
	{
		AriQuizMenu::_addResourceTitle($titleResKey);
		
	    JToolbarHelper::back(AriQuizWebHelper::getResValue('Button.Back'));
	    JToolbarHelper::spacer();
	}
	
	function resultMenu()
	{
		global $option;
		
		$quizId = JRequest::getInt('quizId');

		AriQuizMenu::_addResourceTitle('Title.Result');
		
		JToolbarHelper::custom('result$res_template', 'preview.png', 'preview.png', AriQuizWebHelper::getResValue('Toolbar.Preview'), false);
		JToolbarHelper::spacer();
		JToolbarHelper::divider();
		JToolbarHelper::spacer();
		
		JToolbarHelper::custom('results$tocsv', 'archive.png', 'archive.png', AriQuizWebHelper::getResValue('Toolbar.ExportToCSV'), false);
		JToolbarHelper::spacer();
		JToolbarHelper::divider();
		JToolbarHelper::spacer();
		JToolbarHelper::back(AriQuizWebHelper::getResValue('Toolbar.ResultList'), sprintf('index.php?option=%s&task=%s&quizId=%d',
			$option,
			'results',
			$quizId));
		JToolbarHelper::spacer();
	}
	
	function resultQuestionMenu()
	{
		global $option;

		$statisticsId = JRequest::getInt('sid');
		$resultController = new AriQuizResultController();
		$nextPrev = $resultController->call('getPrevNextFinishedQuestion', $statisticsId);
		
		AriQuizMenu::_addResourceTitle('Title.QuestionResultView');
		
		if (!empty($nextPrev['prevStatisticsId']))
		{
			JToolbarHelper::custom('question$prev', 'back.png', 'back.png', AriQuizWebHelper::getResValue('Label.PrevResult'), false);
			JToolbarHelper::spacer();
		}
		
		if (!empty($nextPrev['nextStatisticsId']))
		{
			JToolbarHelper::custom('question$next', 'forward.png', 'forward.png', AriQuizWebHelper::getResValue('Label.NextResult'), false);
			JToolbarHelper::spacer();
		}

		if (!empty($nextPrev['statisticsInfoId']))
		{
		    JToolbarHelper::divider();
		    JToolbarHelper::spacer();
		  	JToolbarHelper::back(AriQuizWebHelper::getResValue('Toolbar.Results'),
		  		sprintf('index.php?option=%s&task=result&statisticsInfoId=%d',
		  			$option,
		  			$nextPrev['statisticsInfoId']));  
	    	JToolbarHelper::spacer();
		}
	}

	function qtemplateListMenu()
	{
		AriQuizMenu::_addResourceTitle('Title.TemplateList');
		
		JToolbarHelper::addNew('qtemplate_add', AriQuizWebHelper::getResValue('Button.Add'));
		JToolbarHelper::spacer();
		JToolbarHelper::deleteList(
			AriQuizWebHelper::getResValue('Warning.QTemplateRemove'), 
			'qtemplate_list$delete', 
			AriQuizWebHelper::getResValue('Button.Remove'));
		JToolbarHelper::spacer();
	}
	
	function updateQTemplateMenu()
	{
		$templateId = JRequest::getInt('templateId');
		AriQuizMenu::_addTitle(sprintf('%s : %s',
			AriQuizWebHelper::getResValue('Title.QuestionTemplate'),
			AriQuizWebHelper::getResValue($templateId ? 'Label.UpdateItem' : 'Label.AddItem')));
		
		JToolbarHelper::save('qtemplate_add$save', AriQuizWebHelper::getResValue('Toolbar.Save'));
		JToolbarHelper::spacer();
		JToolbarHelper::apply('qtemplate_add$apply', AriQuizWebHelper::getResValue('Toolbar.Apply'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::cancel('qtemplate_add$cancel', AriQuizWebHelper::getResValue('Toolbar.Cancel'));
		JToolbarHelper::spacer();
	}

	function gTemplateListMenu()
	{
		AriQuizMenu::_addResourceTitle('Title.TemplateList');
		
		JToolbarHelper::addNew('texttemplate_add', AriQuizWebHelper::getResValue('Button.Add'));
		JToolbarHelper::spacer();
		JToolbarHelper::deleteList(
			AriQuizWebHelper::getResValue('Warning.QTemplateRemove'), 
			'texttemplate_list$delete', 
			AriQuizWebHelper::getResValue('Button.Remove'));
		JToolbarHelper::spacer();
	}
	
	function gTemplateUpdateMenu()
	{
		$templateId = JRequest::getInt('templateId');
		AriQuizMenu::_addTitle(sprintf('%s : %s',
			AriQuizWebHelper::getResValue('Label.Template'),
			AriQuizWebHelper::getResValue($templateId ? 'Label.UpdateItem' : 'Label.AddItem')));
		
		JToolbarHelper::save('texttemplate_add$save', AriQuizWebHelper::getResValue('Toolbar.Save'));
		JToolbarHelper::spacer();
		JToolbarHelper::apply('texttemplate_add$apply', AriQuizWebHelper::getResValue('Toolbar.Apply'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::cancel('texttemplate_add$cancel', AriQuizWebHelper::getResValue('Toolbar.Cancel'));
		JToolbarHelper::spacer();
	}
	
	function blangListMenu()
	{
		AriQuizMenu::_addResourceTitle('Title.TemplateList');
		
		JToolbarHelper::spacer();
		
		JToolbarHelper::custom('lang_backend$export', 'restore.png', 'restore.png', AriQuizWebHelper::getResValue('Toolbar.Export'), true);
		
		JToolbarHelper::spacer();
		
		JToolbarHelper::custom('import', 'upload.png', 'upload.png', AriQuizWebHelper::getResValue('Toolbar.Import'), false);

		JToolbarHelper::spacer();
		JToolbarHelper::divider();
		JToolbarHelper::spacer();
		
		JToolbarHelper::apply('lang_backend$default', AriQuizWebHelper::getResValue('Toolbar.Default'));
		JToolbarHelper::spacer();
		JToolbarHelper::addNew('blang_add', AriQuizWebHelper::getResValue('Button.Add'));
		JToolbarHelper::spacer();
		JToolbarHelper::deleteList(
			AriQuizWebHelper::getResValue('Warning.QTemplateRemove'), 
			'lang_backend$delete', 
			AriQuizWebHelper::getResValue('Button.Remove'));
		JToolbarHelper::spacer();
	}
	
	function flangListMenu()
	{
		AriQuizMenu::_addResourceTitle('Title.TemplateList');
		
		JToolbarHelper::spacer();
		
		JToolbarHelper::custom('lang_frontend$export', 'restore.png', 'restore.png', AriQuizWebHelper::getResValue('Toolbar.Export'), true);
		
		JToolbarHelper::spacer();
		
		JToolbarHelper::custom('import', 'upload.png', 'upload.png', AriQuizWebHelper::getResValue('Toolbar.Import'), false);
		
		JToolbarHelper::spacer();
		JToolbarHelper::divider();
		JToolbarHelper::spacer();
		
		JToolbarHelper::apply('lang_frontend$default', AriQuizWebHelper::getResValue('Toolbar.Default'));
		JToolbarHelper::spacer();
		JToolbarHelper::addNew('flang_add', AriQuizWebHelper::getResValue('Button.Add'));
		JToolbarHelper::spacer();
		JToolbarHelper::deleteList(
			AriQuizWebHelper::getResValue('Warning.QTemplateRemove'), 
			'lang_frontend$delete', 
			AriQuizWebHelper::getResValue('Button.Remove'));
		JToolbarHelper::spacer();
	}
	
	function templateMenu()
	{
		AriQuizMenu::_addResourceTitle('Title.TemplateList');
		
		JToolbarHelper::spacer();
		JToolbarHelper::addNew('template_add', AriQuizWebHelper::getResValue('Button.Add'));
		JToolbarHelper::spacer();
		JToolbarHelper::deleteList(
			AriQuizWebHelper::getResValue('Warning.QTemplateRemove'), 
			'templates$delete', 
			AriQuizWebHelper::getResValue('Button.Remove'));
		JToolbarHelper::spacer();
	}
	
	function blangUpdateMenu()
	{
		$fileId = JRequest::getInt('fileId');
		AriQuizMenu::_addTitle(sprintf('%s : %s',
			AriQuizWebHelper::getResValue('Title.BLangResource'),
			AriQuizWebHelper::getResValue($fileId ? 'Label.UpdateItem' : 'Label.AddItem')));
		
		JToolbarHelper::save('blang_add$save', AriQuizWebHelper::getResValue('Toolbar.Save'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::apply('blang_add$apply', AriQuizWebHelper::getResValue('Toolbar.Apply'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::cancel('blang_add$cancel', AriQuizWebHelper::getResValue('Toolbar.Cancel'));
	    JToolbarHelper::spacer();
	}
	
	function flangUpdateMenu()
	{
		$fileId = JRequest::getInt('fileId');
		AriQuizMenu::_addTitle(sprintf('%s : %s',
			AriQuizWebHelper::getResValue('Title.FLangResource'),
			AriQuizWebHelper::getResValue($fileId ? 'Label.UpdateItem' : 'Label.AddItem')));
		
		JToolbarHelper::save('flang_add$save', AriQuizWebHelper::getResValue('Toolbar.Save'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::apply('flang_add$apply', AriQuizWebHelper::getResValue('Toolbar.Apply'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::cancel('flang_add$cancel', AriQuizWebHelper::getResValue('Toolbar.Cancel'));
	    JToolbarHelper::spacer();
	}
	
	function templateAddMenu()
	{
		$fileId = JRequest::getInt('fileId');
		AriQuizMenu::_addTitle(sprintf('%s : %s',
			AriQuizWebHelper::getResValue('Title.CSSTemplate'),
			AriQuizWebHelper::getResValue($fileId ? 'Label.UpdateItem' : 'Label.AddItem')));
		
		JToolbarHelper::save('template_add$save', AriQuizWebHelper::getResValue('Toolbar.Save'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::apply('template_add$apply', AriQuizWebHelper::getResValue('Toolbar.Apply'));
	    JToolbarHelper::spacer();
	    JToolbarHelper::cancel('template_add$cancel', AriQuizWebHelper::getResValue('Toolbar.Cancel'));
	    JToolbarHelper::spacer();
	}
	
	function backQuizListMenu($resKey = '')
	{
		global $option;
		
		AriQuizMenu::_addResourceTitle($resKey);
		
		JToolbarHelper::back(AriQuizWebHelper::getResValue('Toolbar.QuizList'), sprintf('index.php?option=%s&task=%s',
			$option,
			'quiz_list'));
	    JToolbarHelper::spacer();
	}
	
	public function addSubmenu($vName)
	{
		if (!class_exists('JSubMenuHelper'))
			return ;
	
		JSubMenuHelper::addEntry(
			JText::_('COM_ARIQUIZLITE_MENU_QUIZZES'),
			'index.php?option=com_ariquizlite&task=quiz_list',
			$vName == 'quiz_list'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_ARIQUIZLITE_MENU_QUIZCAT'),
			'index.php?option=com_ariquizlite&task=category_list',
			$vName == 'category_list'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_ARIQUIZLITE_MENU_QUECAT'),
			'index.php?option=com_ariquizlite&task=questioncategory_list',
			$vName == 'questioncategory_list'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_ARIQUIZLITE_MENU_QUETEMPLATE'),
			'index.php?option=com_ariquizlite&task=qtemplate_list',
			$vName == 'qtemplate_list'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_ARIQUIZLITE_MENU_TEXTTEMPLATE'),
			'index.php?option=com_ariquizlite&task=texttemplate_list',
			$vName == 'texttemplate_list'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_ARIQUIZLITE_MENU_RESULTS'),
			'index.php?option=com_ariquizlite&task=results',
			$vName == 'results'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_ARIQUIZLITE_MENU_LANG'),
			'index.php?option=com_ariquizlite&task=lang_backend',
			$vName == 'lang_backend'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_ARIQUIZLITE_MENU_FLANG'),
			'index.php?option=com_ariquizlite&task=lang_frontend',
			$vName == 'lang_frontend'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_ARIQUIZLITE_MENU_TEMPLATES'),
			'index.php?option=com_ariquizlite&task=templates',
			$vName == 'templates'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_ARIQUIZLITE_MENU_HELP'),
			'index.php?option=com_ariquizlite&task=help',
			$vName == 'help'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_ARIQUIZLITE_MENU_FAQ'),
			'index.php?option=com_ariquizlite&task=faq',
			$vName == 'faq'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_ARIQUIZLITE_MENU_ABOUT'),
			'index.php?option=com_ariquizlite&task=about',
			$vName == 'about'
		);
	}
}
?>
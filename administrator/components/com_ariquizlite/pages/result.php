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

class resultAriPage extends AriAdminQuizPageBase 
{
	var $_resultController;
	
	function _init()
	{
		$this->_resultController = new AriQuizResultController();
		
		parent::_init();
	}

	function execute()
	{
		$quizId = JRequest::getInt('quizId');
		$statisticsInfoId = JRequest::getInt('statisticsInfoId');
		$resultList = $this->_resultController->call('getResultList', $statisticsInfoId);
		$textTemplateList = $this->_getTextTemplateList();
		
		$this->addVar('quizId', $quizId);
		$this->addVar('statisticsInfoId', $statisticsInfoId);
		$this->addVar('resultList', $resultList);
		$this->addVar('textTemplateList', $textTemplateList);
		
		parent::execute();
	}
	
	function _getTextTemplateList()
	{
		$templateController = new AriTextTemplateController();
		$templateList = $templateController->call('getTemplateList', ARI_QUIZ_RESTEMPLATE_KEY);
		if (!empty($templateList))
		{
			foreach ($templateList as $key => $template)
			{
				$templateList[$key]->TemplateName = $template->TemplateName;
			}
		}

		return $templateList;
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('tohtml', 'clickToHtml');
		$this->_registerEventHandler('toword', 'clickToWord');
		$this->_registerEventHandler('toexcel', 'clickToExcel');
		$this->_registerEventHandler('tocsv', 'clickToCSV');
	}
	
	function clickToCSV($eventArgs)
	{
		$statisticsId = JRequest::getVar('statisticsInfoId', array());

		$result = $this->_resultController->call('getCSVView', $statisticsId,
			array('Anonymous' => AriQuizWebHelper::getResValue('Label.Guest'),
			'Passed' => AriQuizWebHelper::getResValue('Label.Passed'),
			'NoPassed' => AriQuizWebHelper::getResValue('Label.NoPassed')),
			AriQuizWebHelper::getShortPeriods());
		ArisResponse::sendContentAsAttach($result,
			sprintf('result.csv'));
		exit();
	}	
}
?>
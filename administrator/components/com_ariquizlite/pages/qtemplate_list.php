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

class qtemplate_listAriPage extends AriAdminQuizPageBase 
{
	function execute()
	{
		$sortInfo = ArisSortingHelper::getCurrentSorting('TemplateName');
		$templateList = $this->_quizController->call('getQuestionTemplateList', $sortInfo);
		
		$this->addVar('templateList', $templateList);
		
		parent::execute();
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('delete', 'clickDelete');
	}
	
	function clickDelete($eventArgs)
	{
		$this->_quizController->call('deleteQuestionTemplate', JRequest::getVar('templateId', array()));
		if (!$this->_isError())
		{
			AriQuizWebHelper::preCompleteAction('Complete.DeleteQTemplate', array('task' => 'qtemplate_list'));
		}
	}
}
?>
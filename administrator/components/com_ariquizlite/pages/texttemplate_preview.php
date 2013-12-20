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

class texttemplate_previewAriPage extends AriAdminQuizPageBase 
{	
	function execute()
	{
		$sid = JRequest::getInt('sid');
		$templateId = JRequest::getInt('templateId');
		if (!empty($templateId))
		{
			$templateController = new AriTextTemplateController();
			$template = $templateController->call('getTemplate', $templateId);
			if ($template && $template->TemplateId)
			{
				$resultController = new AriQuizResultController();
				$result = $resultController->call('getFinishedResultById', $sid);
				
				if (empty($result['UserName'])) $result['UserName'] = AriQuizWebHelper::getResValue('Label.Guest');
				$result['Passed'] = AriQuizWebHelper::getResValue($result['Passed'] ? 'Label.Passed' : 'Label.NoPassed');
				$result['StartDate'] = ArisDate::formatDate($result['StartDate']); 
				$result['EndDate'] = ArisDate::formatDate($result['EndDate']);
				$result['SpentTime'] = ArisDateDuration::toString(AriQuizWebHelper::getValue($result['SpentTime'], 0), AriQuizWebHelper::getShortPeriods(), ' ', true);;
				
				$resText = $template->parse($result);
				AriQuizWebHelper::displayDbValue($resText, false);
			}
		}
		
		exit();
	}
}
?>
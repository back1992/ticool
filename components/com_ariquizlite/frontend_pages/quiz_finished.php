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

class quiz_finishedAriPage extends AriUserQuizPageBase
{
	var $_templates = null;
	var $_result = null;
	var $_templateController;
	var $_stopExecution = false;
	var $_isQuizFinished = null;
	
	function _init()
	{
		$this->_templateController = new AriTextTemplateController();
		
		parent::_init();
	}
	
	function _checkQuizFinished($ticketId)
	{
		global $option, $Itemid;
		
		$mainframe =& JFactory::getApplication();
		
		if ($this->_isQuizFinished) return ;

		$this->_isQuizFinished = $this->_quizController->call('isQuizFinished', $ticketId);
		if (!$this->_isQuizFinished)
		{
			$mainframe->redirect('index.php?option=' . $option . '&task=question&ticketId=' . $ticketId . '&Itemid=' . $Itemid);
		}
	}
	
	function _sendResultToAdmin($result = null)
	{
		$ticketId = JRequest::getString('ticketId', '');
		$sendResultInfo = $this->_resultController->call('sendResultInfo', $ticketId);
		
		if (empty($sendResultInfo)) return false;

		$email = trim($sendResultInfo[0]);
		if (empty($email))
			return false;
		
		if (empty($result))
		{
			$user =& JFactory::getUser();		
			$userId = $user->get('id');
			$result = $this->_getResult($ticketId, $userId);
		}

		$templateKey = ARI_QUIZ_ADMIN_MAIL;
		if (!$this->_isVisibleCtrl($templateKey, $ticketId)) return false;
				
		$resultText = $this->_getResultText($templateKey, $ticketId, $result);
		if (!empty($resultText))
		{
			$body = $resultText;
			$conf =& JFactory::getConfig();
			
			$isSend = $this->sendMail(
				explode(';', $email), 
				AriQuizWebHelper::getResValue('Label.EmailQuizResult'), 
				$body);

			if ($isSend)
			{
				$this->_resultController->call('markResultSend', $ticketId);
			}
				
			return $isSend;
		}

		return false;
	}
	
	function execute()
	{
		if ($this->_stopExecution) return ;
		
		global $option;
		
		$ticketId = JRequest::getString('ticketId', ''); 
		$this->_checkQuizFinished($ticketId);
		
		$this->_quizController->call('markQuizAsFinished', $ticketId);
		$user =& JFactory::getUser();
		$userId = $user->get('id');
		$result = $this->_getResult($ticketId, $userId);
		$isPassed = !empty($result['_Passed']);
		
		$this->_sendResultToAdmin($result);
		
		$templateKey = $isPassed ? ARI_QUIZ_TT_SUCCESSFUL : ARI_QUIZ_TT_FAILED;
		$resultText = $this->_getResultText($templateKey, $ticketId, $result);
		$emailVisible = !empty($userId) && $this->_isVisibleCtrl($isPassed ? ARI_QUIZ_TT_SUCCESSFULEMAIL : ARI_QUIZ_TT_FAILEDEMAIL, $ticketId);
		$printVisible = $this->_isVisibleCtrl($isPassed ? ARI_QUIZ_TT_SUCCESSFULPRINT : ARI_QUIZ_TT_FAILEDPRINT, $ticketId);
		
		$this->addVar('ticketId', $ticketId);
		$this->addVar('resultText', $resultText);
		$this->addVar('result', $result);
		$this->addVar('emailVisible', $emailVisible);
		$this->addVar('printVisible', $printVisible);
		
		parent::execute();
	}
	
	function _getResultText($templateKey, $ticketId, $result)
	{
		$templates = $this->_getTemplates($ticketId);
		$resultText = '';
		if (isset($templates[$templateKey]))
		{
			$templateId = $templates[$templateKey];
			$template = $this->_templateController->call('getTemplate', $templateId);
			if ($template)
			{
				if (empty($result['UserName'])) $result['UserName'] = AriQuizWebHelper::getResValue('Label.Guest');
				$result['Passed'] = AriQuizWebHelper::getResValue($result['Passed'] ? 'Label.Passed' : 'Label.NoPassed');
				$result['StartDate'] = ArisDate::formatDate($result['StartDate']); 
				$result['EndDate'] = ArisDate::formatDate($result['EndDate']);
				$result['SpentTime'] = ArisDateDuration::toString(AriQuizWebHelper::getValue($result['SpentTime'], 0), AriQuizWebHelper::getShortPeriods(), ' ', true);;
				$resultText = $template->parse($result);
			}
		}
		
		return $resultText;
	}
	
	function _getTemplates($ticketId)
	{
		if ($this->_templates === null)
		{
			$quizId = $this->_quizController->call('getQuizIdByTicketId', $ticketId);
			$templates = $this->_templateController->call('getEntitySingleTemplate', ARI_QUIZ_ENTITY_KEY, $quizId);
			if (empty($templates)) $templates = array();
			$this->_templates = $templates;
		}

		return $this->_templates;
	}
	
	function _getResult($ticketId, $userId)
	{
		if ($this->_result === null)
		{
			$result = $this->_resultController->call('getFinishedResult', $ticketId, $userId, array('UserName' => 'Guest'));
			if (!is_array($result)) $result = array();
			$this->_result = $result;
		}
		
		return $this->_result;
	}
	
	function _registerEventHandlers()
	{
		$this->_registerEventHandler('email', 'clickEmail');
		$this->_registerEventHandler('print', 'clickPrint');
	}
	
	function _isVisibleCtrl($templateKey, $ticketId)
	{
		$templates = $this->_getTemplates($ticketId);
		return isset($templates[$templateKey]); 
	}
	
	function clickPrint($eventArgs)
	{
		global $option, $Itemid;
		
		$mainframe =& JFactory::getApplication();
		
		$ticketId = JRequest::getString('ticketId', '');
		$this->_checkQuizFinished($ticketId);
		
		$user =& JFactory::getUser();
		$userId = $user->get('id');
		
		$result = $this->_getResult($ticketId, $userId);
		$isPassed = !empty($result['_Passed']);
				
		$templateKey = $isPassed ? ARI_QUIZ_TT_SUCCESSFULPRINT : ARI_QUIZ_TT_FAILEDPRINT;
		if (!$this->_isVisibleCtrl($templateKey, $ticketId)) 
		{
			$mainframe->redirect('index.php?option=' . $option . '&task=quiz_finished&ticketId=' . $ticketId . '&Itemid=' . $Itemid);
		}

		$resultText = $this->_getResultText($templateKey, $ticketId, $result);
		
		AriQuizWebHelper::displayDbValue($resultText, false);
		$this->_stopExecution = true;
	}
	
	function clickEmail($eventArgs)
	{
		$ticketId = JRequest::getString('ticketId', '');
		$this->_checkQuizFinished($ticketId);

		$user =& JFactory::getUser();
		$userId = $user->get('id');
		if (!empty($userId))
		{
			$email = $user->get('email');
			if (!empty($email))
			{
				$result = $this->_getResult($ticketId, $userId);
				$isPassed = !empty($result['_Passed']);
				
				$templateKey = $isPassed ? ARI_QUIZ_TT_SUCCESSFULEMAIL : ARI_QUIZ_TT_FAILEDEMAIL;
				if (!$this->_isVisibleCtrl($templateKey, $ticketId)) return ;
				
				$resultText = $this->_getResultText($templateKey, $ticketId, $result);
				if (!empty($resultText))
				{
					$conf =& JFactory::getConfig();
					$body = $resultText;
					$isSend = $this->sendMail($email, AriQuizWebHelper::getResValue('Label.EmailQuizResult'), $body);
					$msg = AriQuizWebHelper::getResValue($isSend ? 'Label.EmailSend' : 'Label.EmailNotSend');
					$this->addVar('infoMsg', $msg);
				}
			}
		}
	}
	
	function sendMail($email, $subject, $body, $isHtml = true)
	{
		$isSend = false;
		if (J3_0)
		{
			$conf = JFactory::getConfig();
			$mailer = JFactory::getMailer();

			$mailer->setSender($conf->get('mailfrom'), $conf->get('fromname'));
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->IsHTML($isHtml);
		
			if ($email)
				$email = explode(';', $email);
			
			$mailer->addRecipient($email);
	
			$isSend = $mailer->Send();
			if (($isSend instanceof Exception) || empty($isSend)) 
				$isSend = false;
			else
				$isSend = true;
		}
		else
		{
			$conf = JFactory::getConfig();
			$isSend = JUtility::sendMail(
				$conf->getValue('config.mailfrom'), 
				$conf->getValue('config.fromname'), 
				$email, 
				$subject, 
				$body, 
				true
			);
		}
		
		return $isSend;
	}
}
?>
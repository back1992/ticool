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

define ('ARI_QUIZ_FT_DOC_TAG', 'answers');
define ('ARI_QUIZ_FT_ITEM_TAG', 'answer');
define ('ARI_QUIZ_FT_CI_ATTR', 'ci');
define ('ARI_QUIZ_FT_ID_ATTR', 'id');

AriKernel::import('Entity._QuestionEntity.QuestionBase');
AriKernel::import('Entity._QuestionEntity._Templates.QuestionTemplates');
AriKernel::import('Xml.XmlHelper');

jimport('phputf8.utf8');
jimport('phputf8.strcasecmp');

class FreeTextQuestion extends QuestionBase 
{
	function getDataFromXml($xml, $htmlSpecialChars = true)
	{
		$data = null;
		if (!empty($xml))
		{
			$xmlHandler = AriXmlHelper::getXML($xml, false);
			$childs = $xmlHandler->document->children();
			if (!empty($childs))
			{
				$data = array();
				foreach ($childs as $child)
				{
					$answer = AriXmlHelper::getData($child);
					$data[] = array(
						'tbxAnswer' => $htmlSpecialChars
							? AriQuizWebHelper::htmlSpecialChars($answer)
							: $answer,
						'hidQueId' => AriXmlHelper::getAttribute($child, ARI_QUIZ_FT_ID_ATTR),
						'cbCI' => AriXmlHelper::getAttribute($child, ARI_QUIZ_FT_CI_ATTR)
					);
				}
			}
		}

		return $data;
	}
	
	function getFrontXml()
	{
		$tbxAnswer = JRequest::getString('tbxAnswer', '', 'default', JREQUEST_ALLOWHTML);
		if (get_magic_quotes_gpc())
		{
			$tbxAnswer = stripslashes($tbxAnswer);
		}
		
		$xmlHandler = AriXmlHelper::getXML(sprintf(ARI_QT_TEMPLATE_XML, ARI_QUIZ_DB_CHARSET, ARI_QUIZ_FT_DOC_TAG), false);
		$xmlDoc = $xmlHandler->document; 
		$xmlItem = $xmlDoc->addChild(ARI_QUIZ_FT_ITEM_TAG);
		AriXmlHelper::setData($xmlItem, $tbxAnswer);

		return AriXmlHelper::toString($xmlDoc);	
	}
	
	function isCorrect($xml, $baseXml)
	{
		$isCorrect = false;
		if (!empty($xml) && !empty($baseXml))
		{
			$data = $this->getDataFromXml($baseXml);
			$xData = $this->getDataFromXml($xml);
			$answer = !empty($xData) && count($xData) > 0 ? trim($xData[0]['tbxAnswer']) : '';
			$answer = $answer;
			if (!empty($data) && !empty($answer))
			{
				foreach ($data as $dataItem)
				{	
					$correctAnswer = $dataItem['tbxAnswer'];
					if (!empty($dataItem['cbCI']))
					{
						$isCorrect = (utf8_strcasecmp($answer, $correctAnswer) === 0);
					}
					else
					{
						$isCorrect = strcmp($correctAnswer, $answer) === 0;
					}
					
					if ($isCorrect) break;
				}
			}
		}

		return $isCorrect;
	}
	
	function getXml()
	{
		$answers = WebControls_MultiplierControls::getData('tblQueContainer', array('tbxAnswer', 'cbCI', 'hidQueId'), null, true);
		
		$xmlStr = null;
		if (!empty($answers))
		{
			$xmlHandler = AriXmlHelper::getXML(sprintf(ARI_QT_TEMPLATE_XML, ARI_QUIZ_DB_CHARSET, ARI_QUIZ_FT_DOC_TAG), false);
			$xmlDoc = $xmlHandler->document;

			foreach ($answers as $answerItem)
			{
				$answer = trim($answerItem['tbxAnswer']);
				if (strlen($answer))
				{
					$xmlItem = $xmlDoc->addChild(ARI_QUIZ_FT_ITEM_TAG);
					AriXmlHelper::setData($xmlItem, $answer);

					if ($answerItem['cbCI'])
					{
						$xmlItem->addAttribute(ARI_QUIZ_FT_CI_ATTR, 'true');
					}
					
					$id = isset($answerItem['hidQueId']) && !empty($answerItem['hidQueId']) 
						? $answerItem['hidQueId'] 
						: uniqid('', true);
					$xmlItem->addAttribute(ARI_QUIZ_FT_ID_ATTR, $id);
				}
			}

			$xmlStr = AriXmlHelper::toString($xmlDoc);
		}

		return $xmlStr;
	}
}
?>
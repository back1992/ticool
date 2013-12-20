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

define ('ARI_QUIZ_SQ_DOC_TAG', 'answers');
define ('ARI_QUIZ_SQ_ITEM_TAG', 'answer');
define ('ARI_QUIZ_SQ_CI_ATTR', 'ci');
define ('ARI_QUIZ_SQ_ID_ATTR', 'id');
define ('ARI_QUIZ_SQ_CORRECT_ATTR', 'correct');

AriKernel::import('Entity._QuestionEntity.QuestionBase');
AriKernel::import('Entity._QuestionEntity._Templates.QuestionTemplates');
AriKernel::import('Xml.XmlHelper');

class SingleQuestion extends QuestionBase 
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
						'hidQueId' => AriXmlHelper::getAttribute($child, ARI_QUIZ_SQ_ID_ATTR),
						'hidCorrect' => AriXmlHelper::getAttribute($child, ARI_QUIZ_SQ_CORRECT_ATTR)
					);
				}
			}
		}

		return $data;
	}
	
	function getFrontXml()
	{
		$selectedAnswer = JRequest::getString('selectedAnswer', '');
		$xmlHandler = AriXmlHelper::getXML(sprintf(ARI_QT_TEMPLATE_XML, ARI_QUIZ_DB_CHARSET, ARI_QUIZ_SQ_DOC_TAG), false);
		$xmlDoc = $xmlHandler->document; 

		if (!empty($selectedAnswer))
		{
			$xmlItem = $xmlDoc->addChild(ARI_QUIZ_SQ_ITEM_TAG);
			$xmlItem->addAttribute(ARI_QUIZ_SQ_ID_ATTR, $selectedAnswer);
		}
		
		return AriXmlHelper::toString($xmlDoc);
	}
	
	function isCorrect($xml, $baseXml)
	{
		$isCorrect = false;
		if (!empty($xml) && !empty($baseXml))
		{
			$data = $this->getDataFromXml($baseXml);
			$correctId = null;
			if (!empty($data))
			{
				foreach ($data as $dataItem)
				{
					if (!empty($dataItem['hidCorrect']))
					{
						$correctId = $dataItem['hidQueId'];
						break;
					}
				}
			}

			if ($correctId)
			{
				$xData = $this->getDataFromXml($xml);
				if (!empty($xData) && $xData[0]['hidQueId'] == $correctId)
				{
					$isCorrect = true;
				}
			}
		}
		
		return $isCorrect;
	}

	function getXml()
	{
		$answers = WebControls_MultiplierControls::getData('tblQueContainer', array('tbxAnswer', 'cbCorrect', 'hidQueId', 'hidCorrect'), null, true);
		$xmlStr = null;
		if (!empty($answers))
		{
			$xmlHandler = AriXmlHelper::getXML(sprintf(ARI_QT_TEMPLATE_XML, ARI_QUIZ_DB_CHARSET, ARI_QUIZ_SQ_DOC_TAG), false);
			$xmlDoc = $xmlHandler->document;
			$i = 0;
			$isSetCorrect = false;
			foreach ($answers as $answerItem)
			{
				$answer = trim($answerItem['tbxAnswer']);
				if (strlen($answer))
				{
					if (empty($answer)) $answer .= ' ';
					$xmlItem = $xmlDoc->addChild(ARI_QUIZ_SQ_ITEM_TAG);
					AriXmlHelper::setData($xmlItem, $answer);
					
					$correct = isset($answerItem['rbCorrect']);
					if ($correct)
					{
						$xmlDoc->addAttribute(ARI_QUIZ_SQ_SCORE_ATTR, $i);
					}
					
					$id = isset($answerItem['hidQueId']) && !empty($answerItem['hidQueId']) 
						? $answerItem['hidQueId'] 
						: uniqid('', true);
					$xmlItem->addAttribute(ARI_QUIZ_SQ_ID_ATTR, $id);
					if (!$isSetCorrect && !empty($answerItem['hidCorrect']))
					{
						$xmlItem->addAttribute(ARI_QUIZ_SQ_CORRECT_ATTR, 'true');
						$isSetCorrect = true;
					}
					$i++;
				}
			}

			$xmlStr = AriXmlHelper::toString($xmlDoc);
		}

		return $xmlStr;
	}
}
?>
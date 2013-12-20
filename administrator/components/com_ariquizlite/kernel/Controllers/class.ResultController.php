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

AriKernel::import('Controllers.ControllerBase');

class AriQuizResultController extends AriControllerBase
{
	function sendResultInfo($ticketId)
	{
		$ticketId = trim($ticketId);
		if (empty($ticketId)) return null;
		
		$query = sprintf('SELECT Q.AdminEmail' .
			' FROM #__ariquizstatisticsinfo QSI INNER JOIN #__ariquiz Q' .
			'	ON QSI.QuizId = Q.QuizId' .
			' INNER JOIN #__arigenerictemplateentitymap GTEM' .
			'	ON GTEM.EntityId = Q.QuizId' .
			' WHERE QSI.TicketId = %s AND (Q.AdminEmail IS NOT NULL AND LENGTH(Q.AdminEmail) > 0) AND GTEM.EntityName = %s AND GTEM.TemplateType = %s AND QSI.ResultEmailed <> 1' .
			' LIMIT 0,1',
			$this->_db->Quote($ticketId),
			$this->_db->Quote(ARI_QUIZ_ENTITY_KEY),
			$this->_db->Quote(ARI_QUIZ_ADMIN_MAIL));
		$this->_db->setQuery($query);
		$info = $this->_db->loadResultArray();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt check result.', E_USER_ERROR);
			return null;
		}

		return $info;
	}
	
	function markResultSend($ticketId)
	{
		$ticketId = trim($ticketId);
		if (empty($ticketId)) return ;
		
		$query = sprintf('UPDATE #__ariquizstatisticsinfo SET ResultEmailed = 1 WHERE TicketId = %s',
			$this->_db->Quote($ticketId));
		$this->_db->setQuery($query);
		$this->_db->query();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt mark result is sent.', E_USER_ERROR);
			return ;
		}
	}
	
	function getTopResults($count = 5)
	{
		$query = sprintf('SELECT U.name AS UserName, Q.QuizName, QSI.UserScore, (MAX(QSI.UserScore / QSI.MaxScore) * 100) AS PercentScore' . 
			' FROM #__ariquizstatisticsinfo QSI INNER JOIN #__ariquiz Q' .
			' 	ON QSI.QuizId = Q.QuizId' . 
			' INNER JOIN #__users U' .
			'  ON QSI.UserId = U.id' .
			' WHERE QSI.UserId > 0 AND QSI.Status = "Finished" AND Q.Status = %d' .
			' GROUP BY QSI.QuizId' .
			' ORDER BY MAX(QSI.UserScore / QSI.MaxScore) DESC' .
			' LIMIT 0,%d',
			ARI_QUIZ_STATUS_ACTIVE, $count);
		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			//trigger_error('ARI: Couldnt get top user result list.', E_USER_ERROR);
			return null;
		}
		
		return $results;
	}
	
	function getLastResults($count = 5)
	{
		$results = null;
		$query = sprintf('SELECT U.name AS UserName, Q.QuizName, QSI.UserScore, ((QSI.UserScore / QSI.MaxScore) * 100) AS PercentScore' . 
			' FROM #__ariquizstatisticsinfo QSI INNER JOIN #__ariquiz Q' .
			' 	ON QSI.QuizId = Q.QuizId' .
			' INNER JOIN #__users U' .
			'   ON QSI.UserId = U.id' . 
			' WHERE QSI.UserId > 0 AND QSI.Status = "Finished" AND Q.Status = %d' .
			' ORDER BY QSI.EndDate DESC' .
			' LIMIT 0,%d',
			ARI_QUIZ_STATUS_ACTIVE, $count);
		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			//trigger_error('ARI: Couldnt get last result list.', E_USER_ERROR);
			return null;
		}
		
		return $results;
	}
	
	function getLastUserResults($userId, $count = 5)
	{
		$userId = intval($userId);
		$results = null;
		if ($userId > 0)
		{
			$query = sprintf('SELECT Q.QuizName, QSI.UserScore, ((QSI.UserScore / QSI.MaxScore) * 100) AS PercentScore' . 
				' FROM #__ariquizstatisticsinfo QSI INNER JOIN #__ariquiz Q' .
				' 	ON QSI.QuizId = Q.QuizId' . 
				' WHERE UserId = %d AND QSI.Status = "Finished" AND Q.Status = %d' .
				' ORDER BY QSI.EndDate DESC' .
				' LIMIT 0,%d',
				$userId, ARI_QUIZ_STATUS_ACTIVE, $count);
			$this->_db->setQuery($query);
			$results = $this->_db->loadObjectList();
			if ($this->_db->getErrorNum())
			{
				//trigger_error('ARI: Couldnt get last user result list.', E_USER_ERROR);
				return null;
			}
		}
		
		return $results;
	}
	
	function getTopUserResults($userId, $count = 5)
	{
		$userId = intval($userId);
		$results = null;
		if ($userId > 0)
		{
			$query = sprintf('SELECT Q.QuizName, QSI.UserScore, (MAX(QSI.UserScore / QSI.MaxScore) * 100) AS PercentScore' . 
				' FROM #__ariquizstatisticsinfo QSI INNER JOIN #__ariquiz Q' .
				' 	ON QSI.QuizId = Q.QuizId' . 
				' WHERE UserId = %d AND QSI.Status = "Finished" AND Q.Status = %d' .
				' GROUP BY QSI.QuizId' .
				' ORDER BY MAX(QSI.UserScore / QSI.MaxScore) DESC' .
				' LIMIT 0,%d',
				$userId, ARI_QUIZ_STATUS_ACTIVE, $count);
			$this->_db->setQuery($query);
			$results = $this->_db->loadObjectList();
			if ($this->_db->getErrorNum())
			{
				//trigger_error('ARI: Couldnt get top user result list.', E_USER_ERROR);
				return null;
			}
		}
		
		return $results;
	}
	
	function getFinishedResultById($statisticsInfoId, $defaults = array())
	{
		$error = 'ARI: Couldnt get finished result.';
		
		$statisticsInfo = $this->getStatisticsInfo($statisticsInfoId);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		$result = null;
		if ($statisticsInfo && $statisticsInfo->StatisticsInfoId)
		{
			$result = $this->getFinishedResult($statisticsInfo->TicketId, $statisticsInfo->UserId, $defaults);
			if ($this->_isError(true, false))
			{
				trigger_error($error, E_USER_ERROR);
				return null;
			}
		}
		
		return $result;
	}
	
	function getStatisticsInfo($statisticsInfoId)
	{
		$statisticsInfo = EntityFactory::createInstance('StatisticsInfoEntity', ARI_ENTITY_GROUP);
		if (!$statisticsInfo->load($statisticsInfoId))
		{
			trigger_error('ARI: Couldnt get statistics info entity', E_USER_ERROR);
			return null;
		}
		
		return $statisticsInfo;
	}
	
	function getFinishedResult($ticketId, $userId, $defaults = array())
	{
		$userId = intval($userId);
		$query = sprintf('SELECT U.Name AS UserName, Q.QuizName, QSI.PassedScore, QSI.MaxScore, QSI.UserScore, ((QSI.UserScore / QSI.MaxScore) * 100) AS PercentScore, QSI.Passed, QSI.Passed AS _Passed, QSI.StartDate, QSI.EndDate, (UNIX_TIMESTAMP(QSI.EndDate) - UNIX_TIMESTAMP(QSI.StartDate)) AS SpentTime, QSI.QuizId' . 
			' FROM #__ariquizstatisticsinfo QSI' . 
			' LEFT JOIN #__ariquiz Q ON QSI.QuizId = Q.QuizId' .
			' LEFT JOIN #__users U ON QSI.UserId = U.Id' .
			' WHERE QSI.TicketId = %s AND QSI.Status = "Finished" AND (QSI.UserId = 0 OR QSI.UserId = %d)' .
			' GROUP BY QSI.StatisticsInfoId' .
			' LIMIT 0,1',
			$this->_db->Quote($ticketId),
			$userId);
		$this->_db->setQuery($query);
		$obj = $this->_db->loadAssocList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get finished result.', E_USER_ERROR);
			return null;
		}
		
		$obj = (!empty($obj) && count($obj) > 0) ? $obj[0] : null;
		if ($obj != null)
		{
			if (!empty($defaults))
			{
				foreach ($defaults as $key => $value)
				{
					if (key_exists($key, $obj) && empty($obj[$key]))
					{ 
						$obj[$key] = $value;
					}
				}
			}
			
			$obj['PercentScore'] = sprintf('%.2f', $obj['PercentScore']);
			$obj['PassedScore'] = sprintf('%.2f', $obj['PassedScore']);
		}
		
		
		return $obj;
	}
	
	function getFinishedInfo($statisticsInfoId)
	{
		$statisticsInfoId = intval($statisticsInfoId);
		$query = sprintf('SELECT SUM(QQV.Score) AS MaxScore,SUM(QS.Score) AS UserScore,(100 * (SUM(QS.Score) / SUM(QQV.Score)) >= QSI.PassedScore) AS Passed' .
			' FROM #__ariquizstatisticsinfo QSI INNER JOIN #__ariquizstatistics QS' .
			'	ON QSI.StatisticsInfoId = QS.StatisticsInfoId' .
			' INNER JOIN #__ariquizquestionversion QQV' .
			'	ON QQV.QuestionVersionId = QS.QuestionVersionId' .
			' WHERE QSI.StatisticsInfoId = %d' .
			' GROUP BY QSI.StatisticsInfoId' .
			' LIMIT 0,1',
			$statisticsInfoId);
		$this->_db->setQuery($query);
		$obj = $this->_db->loadAssocList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get finished info.', E_USER_ERROR);
			return null;
		}
		
		$obj = (!empty($obj) && count($obj) > 0) ? $obj[0] : null;
		
		return $obj;
	}
	
	function getNextFinishedQuestion($currentStatisticsId)
	{
		$currentStatisticsId = intval($currentStatisticsId);
		$query = sprintf('SELECT QS1.StatisticsId' . 
			' FROM #__ariquizstatistics QS LEFT JOIN #__ariquizstatistics QS1' .
			' 	ON QS.StatisticsInfoId = QS1.StatisticsInfoId' .
			' WHERE QS.StatisticsId = %d AND QS1.QuestionIndex = QS.QuestionIndex + 1' .
			' LIMIT 0,1',
			$currentStatisticsId);
		$this->_db->setQuery($query);
		$nextStatisticsId = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get next statistics entity.', E_USER_ERROR);
			return null;
		}

		return $nextStatisticsId;
	}
	
	function getPrevFinishedQuestion($currentStatisticsId)
	{
		$currentStatisticsId = intval($currentStatisticsId);
		$query = sprintf('SELECT QS1.StatisticsId' . 
			' FROM #__ariquizstatistics QS LEFT JOIN #__ariquizstatistics QS1' .
			' 	ON QS.StatisticsInfoId = QS1.StatisticsInfoId' .
			' WHERE QS.StatisticsId = %d AND QS1.QuestionIndex = QS.QuestionIndex - 1' .
			' LIMIT 0,1',
			$currentStatisticsId);
		$this->_db->setQuery($query);
		$prevStatisticsId = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get prev statistics entity.', E_USER_ERROR);
			return null;
		}
		
		return $prevStatisticsId;
	}
	
	function getPrevNextFinishedQuestion($currentStatisticsId)
	{
		$nextPrev = array('nextStatisticsId' => null, 'prevStatisticsId' => null);
		$currentStatisticsId = intval($currentStatisticsId);
		if (empty($currentStatisticsId)) return $nextPrev;
		
		$query = sprintf('SELECT QS1.StatisticsId,QS1.StatisticsInfoId' .
			' FROM #__ariquizstatistics QS LEFT JOIN #__ariquizstatistics QS1' .
			' 	ON QS.StatisticsInfoId = QS1.StatisticsInfoId' .
			' WHERE QS.StatisticsId = %d AND (QS.QuestionIndex = 0 OR QS1.QuestionIndex >= QS.QuestionIndex - 1) AND QS1.QuestionIndex <= QS.QuestionIndex + 1' .
			' ORDER BY QS1.QuestionIndex ASC' .
			' LIMIT 0,3',
			$currentStatisticsId);
		$this->_db->setQuery($query);
		$res = $this->_db->loadAssocList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get next/prev statistics entity.', E_USER_ERROR);
			return null;
		}
		
		if (!empty($res) && count($res) > 0)
		{
			$nextPrev['statisticsInfoId'] = $res[0]['StatisticsInfoId'];
			$key = 'prevStatisticsId';
			foreach ($res as $value)
			{
				$id = $value['StatisticsId'];
				if ($id == $currentStatisticsId)
				{
					$key = 'nextStatisticsId';
				}
				else
				{
					$nextPrev[$key] = $id;
				}
			}
		}

		return $nextPrev;
	}
	
	function getResultList($statisticsInfoId)
	{
		$statisticsInfoId = intval($statisticsInfoId);

		$query = sprintf('SELECT U.name, SS.Score, QQV.Score AS MaxScore, SS.StatisticsId, QQV.Question, QQC.CategoryName, QQT.QuestionType, SS.QuestionVersionId, SS.StartDate, SS.StartDate, (UNIX_TIMESTAMP(SS.EndDate) - UNIX_TIMESTAMP(SS.StartDate) + UsedTime) AS TotalTime,SS.QuestionTime,SSI.TotalTime AS QuizTotalTime' .
			' FROM #__ariquizstatisticsinfo SSI INNER JOIN #__ariquizstatistics SS ON SSI.StatisticsInfoId = SS.StatisticsInfoId' .
			' INNER JOIN #__ariquizquestionversion QQV ON QQV.QuestionVersionId = SS.QuestionVersionId' .
			' INNER JOIN #__ariquizquestiontype QQT ON QQV.QuestionTypeId = QQT.QuestionTypeId' .
			' LEFT JOIN #__ariquizquestioncategory QQC ON QQV.QuestionCategoryId = QQC.QuestionCategoryId' .
			' LEFT JOIN #__users U ON SSI.UserId = U.id' .
			' WHERE SSI.Status = "Finished" AND SS.StatisticsInfoId = %d' .
			' ORDER BY SS.QuestionIndex',
			$statisticsInfoId);

		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get result list.', E_USER_ERROR);
			return null;
		}

		if (!empty($result) && count($result) > 0)
		{
			$quizTotalTime = $result[0]->QuizTotalTime;
			$isIgnoreTime = empty($quizTotalTime);
			$sumTotalTime = 0;
			foreach ($result as $item)
			{
				$queTotalTime = $item->TotalTime ? $item->TotalTime : $item->QuestionTime;
				if (!$isIgnoreTime && !empty($queTotalTime))
				{
					 if (($sumTotalTime + $queTotalTime) < $quizTotalTime)
					 {
					 	$sumTotalTime += $queTotalTime;
					 }
					 else
					 {
					 	$queTotalTime = $quizTotalTime - $sumTotalTime;
					 	$sumTotalTime = $quizTotalTime;
					 }
				}

				$item->TotalTime = $queTotalTime;
			}
		}

		return $result;
	}
	
	function getFinishedQuizList()
	{
		$query = sprintf('SELECT Q.QuizId, Q.QuizName' . 
			' FROM #__ariquizstatisticsinfo SSI INNER JOIN  #__ariquiz Q ON SSI.QuizId = Q.QuizId' .
			' WHERE SSI.Status = "Finished"' .
			' GROUP BY Q.QuizId' .
			' ORDER BY Q.QuizName ASC');
		
		$this->_db->setQuery($query);
		$quizList = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get finished quiz list.', E_USER_ERROR);
			return null;
		}
		
		return $quizList;
	}
	
	function getFinishedUserList($quizId = 0, $addAnonymousUser = true, $params = array())
	{
		$query = sprintf('SELECT U.Id, U.Name' . 
			' FROM #__ariquizstatisticsinfo SSI INNER JOIN  #__users U' .
			' 	ON SSI.UserId = U.Id' .
			' WHERE SSI.Status = "Finished" AND (%d = 0 OR SSI.QuizId = %d)' .
			' GROUP BY U.Id' .
			' ORDER BY U.UserName ASC',
			$quizId,
			$quizId);

		$this->_db->setQuery($query);
		$userList = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get user list.', E_USER_ERROR);
			return null;
		}
		
		if ($addAnonymousUser)
		{
			$anonUser = new stdClass();
			$anonUser->Id = '0';
			$anonUser->Name = isset($params['Anonymous']) ? $params['Anonymous'] : '';
			if (empty($userList)) $userList = array();
			array_unshift($userList, $anonUser);
		}
		
		return $userList;
	}
	
	function getResultCount($quizId = 0, $userId = 0)
	{
		$quizId = intval($quizId);
		$query = sprintf('SELECT COUNT(*) FROM ' .
			'#__ariquizstatisticsinfo SSI INNER JOIN #__ariquiz S ON SSI.QuizId = S.QuizId ' .
			' LEFT JOIN #__users U' .
			' 	ON SSI.UserId = U.id ' .
			' WHERE (%1$d = 0 OR SSI.QuizId = %1$d) AND SSI.Status = "Finished" AND (%2$s IS NULL OR SSI.UserId = %3$d)',
			$quizId,
			$this->_normalizeValue($userId), 
			intval($userId));

		$this->_db->setQuery($query);
		$count = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get result count.', E_USER_ERROR);
			return 0;
		}
		
		return $count;
	}
	
	function getResults($sortInfo = null, $limitStart = null, $limit = null, $quizId = 0, $userId = 0)
	{
		$quizId = intval($quizId);
		$query = sprintf('SELECT SSI.TicketId,SSI.StatisticsInfoId, SSI.Passed, SSI.UserScore, SSI.MaxScore, U.Name, U.Id, S.QuizName, S.QuizId, SSI.StartDate, SSI.EndDate' .
			' FROM #__ariquizstatisticsinfo SSI INNER JOIN #__ariquiz S' .
			' 	ON SSI.QuizId = S.QuizId' .
			' LEFT JOIN #__users U' .
			' 	ON SSI.UserId = U.id ' .
			' WHERE (%1$d = 0 OR SSI.QuizId = %1$d) AND SSI.Status = "Finished" AND (%2$s IS NULL OR SSI.UserId = %3$d)' . 
			' GROUP BY SSI.StatisticsInfoId ' .
			$this->_getOrder($sortInfo) .
			$this->_getLimit($limitStart, $limit),
			$quizId,
			$this->_normalizeValue($userId), 
			intval($userId));

		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get results.', E_USER_ERROR);
			return null;
		}

		return $results;
	}
	
	function getBaseView($statisticsInfoId)
	{
		$idList = $this->_fixIdList($statisticsInfoId);
		if (empty($idList)) return null;

		$idStr = join(',', $this->_quoteValues($idList));
		
		$query = sprintf('SELECT SSI.StartDate AS QuizStartDate, SSI.EndDate AS QuizEndDate, SS.QuestionIndex, SSI.UserScore AS QuizUserScore,SSI.QuestionCount,SSI.MaxScore AS QuizMaxScore,SSI.Passed,SSI.PassedScore AS QuizPassedScore, U.name AS UserName, Q.QuizName, Q.QuizId, SS.Score, SS.StatisticsInfoId, SS.StatisticsId, QQV.Question, QQV.Data AS BaseData, QQV.Score AS MaxScore, SS.Data, QQC.CategoryName, QQT.QuestionType, SS.QuestionVersionId, SS.IpAddress, SS.StartDate, SS.EndDate,  (UNIX_TIMESTAMP(SS.EndDate) - UNIX_TIMESTAMP(SS.StartDate) + UsedTime) AS TotalTime' .
			' FROM #__ariquizstatisticsinfo SSI INNER JOIN #__ariquizstatistics SS ON SSI.StatisticsInfoId = SS.StatisticsInfoId' .
			' INNER JOIN #__ariquiz Q ON SSI.QuizId = Q.QuizId' .
			' INNER JOIN #__ariquizquestionversion QQV ON QQV.QuestionVersionId = SS.QuestionVersionId' .
			' INNER JOIN #__ariquizquestiontype QQT ON QQV.QuestionTypeId = QQT.QuestionTypeId' .
			' LEFT JOIN #__ariquizquestioncategory QQC ON QQV.QuestionCategoryId = QQC.QuestionCategoryId' .
			' LEFT JOIN #__users U ON SSI.UserId = U.id' .
			' WHERE SSI.Status = "Finished" AND SS.StatisticsInfoId IN (%s)' .
			' ORDER BY SS.StatisticsInfoId, SS.QuestionIndex',
			$idStr);
		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get statistics.', E_USER_ERROR);
			return null;
		}
		
		return $results;
	}
	
	function getSimpleBaseView($statisticsInfoId)
	{
		$idList = $this->_fixIdList($statisticsInfoId);
		if (empty($idList)) return null;

		$idStr = join(',', $this->_quoteValues($idList));
		
		$query = sprintf('SELECT SSI.StatisticsInfoId,SSI.UserScore AS QuizUserScore,SSI.QuestionCount,SSI.MaxScore AS QuizMaxScore,SSI.Passed,SSI.PassedScore AS QuizPassedScore, U.name AS UserName, Q.QuizName, Q.QuizId, SSI.StartDate, SSI.EndDate,  (UNIX_TIMESTAMP(SSI.EndDate) - UNIX_TIMESTAMP(SSI.StartDate)) AS TotalTime' .
			' FROM #__ariquizstatisticsinfo SSI INNER JOIN #__ariquiz Q ON SSI.QuizId = Q.QuizId' .
			' LEFT JOIN #__users U ON SSI.UserId = U.id' .
			' WHERE SSI.Status = "Finished" AND SSI.StatisticsInfoId IN (%s)' .
			' ORDER BY SSI.StatisticsInfoId',
			$idStr);
		$this->_db->setQuery($query);
		$results = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get statistics.', E_USER_ERROR);
			return null;
		}
		
		return $results;
	}

	function getCSVView($statisticsInfoId, $params = array(), $periods = null)
	{
		$fields = array('#', 'Quiz Name', 'User', 'Question Count', 'Passed', 'Start Date', 'End Date', 'Spent Time', 'User Score', 'User Score Percent', 'Max Score', 'Passing Score');
		$fields = array_map(create_function('$v', 'return \'"\' . $v . \'"\';'), $fields);
		$csv = join("\t", $fields);
		
		$results = $this->getSimpleBaseView($statisticsInfoId);
		if ($this->_isError(true, false))
		{
			trigger_error('ARI: Couldnt export to CSV.', E_USER_ERROR);
			return '';
		}
		
		if (!empty($results))
		{
			$anonymous = isset($params['Anonymous']) ? $params['Anonymous'] : '';
			$passed = isset($params['Passed']) ? $params['Passed'] : '';
			$noPassed = isset($params['NoPassed']) ? $params['NoPassed'] : '';
			$i = 1;
			foreach ($results as $result)
			{
				$csv .= "\r\n";
				$userScorePercent = $result->QuizMaxScore 
					? round(100 * $result->QuizUserScore / $result->QuizMaxScore)
					: 100;
				$rowData = array(
					$i, 
					$result->QuizName,
					!empty($result->UserName) ? $result->UserName : $anonymous,
					$result->QuestionCount,
					$result->Passed ? $passed : $noPassed,
					ArisDate::formatDate($result->StartDate),
					ArisDate::formatDate($result->EndDate),
					ArisDateDuration::toString($result->TotalTime, $periods, ' ', true),
					$result->QuizUserScore,
					$userScorePercent . '%',
					$result->QuizMaxScore,
					$result->QuizPassedScore . '%'); 
				foreach ($rowData as $key => $dataItem)
				{
					$rowData[$key] = str_replace("\t", ' ', $dataItem);
				}

				$csv .= join("\t", $rowData);
				
				++$i;
			}
		}
		
		if (function_exists('iconv'))
		{
			$csv = chr(255) . chr(254) . @iconv('UTF-8', 'UTF-16LE', $csv);
		}
		else if (function_exists('mb_convert_encoding'))
		{
			$csv = chr(255) . chr(254) . @mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');
		}
		
		return $csv;
	}	
}
?>
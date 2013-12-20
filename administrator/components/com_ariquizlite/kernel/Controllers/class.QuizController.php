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

class AriQuizController extends AriControllerBase
{	
	function getUserCompletedQuestion($ticketId)
	{
		$query = sprintf('SELECT COUNT(*) FROM' . 
			' #__ariquizstatisticsinfo SSI LEFT JOIN #__ariquizstatistics SS' . 
			' 	ON SSI.StatisticsInfoId = SS.StatisticsInfoId' . 
			' WHERE SSI.TicketId = %s AND' . 
			' (SS.EndDate IS NOT NULL OR' . 
			' (SS.StartDate IS NOT NULL AND (SS.QuestionTime IS NOT NULL AND SS.QuestionTime > 0) AND (UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(SS.StartDate) + UsedTime) >= SS.QuestionTime))', 
			$this->_db->Quote($ticketId));
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get user completed question count.', E_USER_ERROR);
			return 0;
		}
		
		return $result;
	}
	
	function getUserQuizInfo($ticketId)
	{
		$query = sprintf('SELECT QSI.TotalTime, UNIX_TIMESTAMP(QSI.StartDate) AS StartDate, UNIX_TIMESTAMP(UTC_TIMESTAMP()) AS Now, Q.QuizName,Q.CanSkip,Q.CssTemplateId,QSI.QuestionCount' .
				' FROM #__ariquizstatisticsinfo QSI LEFT JOIN #__ariquiz Q' .
				'	ON QSI.QuizId = Q.QuizId' .
				' WHERE QSI.TicketId = %s AND QSI.Status = "Process" LIMIT 0,1',
				$this->_db->Quote($ticketId));
		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get user quiz info.', E_USER_ERROR);
			return null;
		}
		
		return $result && count($result) > 0 ? $result[0] : null;
	}
	
	function getNotFinishedTicketId($quizId, $userId)
	{
		$ticketId = '';
		if (!empty($userId))
		{
			$query = sprintf('SELECT QSI.TicketId' .
				' FROM #__ariquizstatisticsinfo QSI' .
				' WHERE (QSI.Status = "Process" OR QSI.Status = "Prepare") AND UserId = %d AND QuizId = %d ORDER BY StatisticsInfoId DESC LIMIT 0,1',
				$userId,
				$quizId);
			$this->_db->setQuery($query);
			$ticketId = $this->_db->loadResult();
			if ($this->_db->getErrorNum())
			{
				trigger_error('ARI: Couldnt get not finished ticket id.', E_USER_ERROR);
				return '';
			}
		}
		
		return $ticketId;
	}
	
	function isHasNotFinishedQuiz($quizId, $userId)
	{
		$cnt = 0;
		if (!empty($userId))
		{
			$query = sprintf('SELECT COUNT(QSI.*)' .
				' FROM #__ariquizstatisticsinfo QSI' .
				' WHERE (QSI.Status = "Process" OR QSI.Status = "Prepare") AND UserId = %d AND QuizId = %d GROUP BY QuizId',
				$userId,
				$quizId);
			$this->_db->setQuery($query);
			$cnt = $this->_db->loadResult();
			if ($this->_db->getErrorNum())
			{
				trigger_error('ARI: Couldnt check not finished quiz.', E_USER_ERROR);
				return TRUE;
			}
		}
		
		return ($cnt > 0);
	}
	
	function isUniqueQuizName($name, $id = null)
	{
		$isUnique = $this->_isUniqueField('#__ariquiz', 'QuizName', $name, 'QuizId', $id);
		if ($this->_isError(true, false))
		{
			trigger_error('ARI: Couldnt check unique quiz name.', E_USER_ERROR);
			return false;
		}
		
		return $isUnique;
	}
	
	function isUniqueCategoryName($name, $id = null)
	{
		$isUnique = $this->_isUniqueField('#__ariquizcategory', 'CategoryName', $name, 'CategoryId', $id);
		if ($this->_isError(true, false))
		{
			trigger_error('ARI: Couldnt check unique category name.', E_USER_ERROR);
			return false;
		}
		
		return $isUnique;
	}
	
	function composeUserQuiz($quizId, $ticketId, $userId)
	{
		$error = 'ARI: Couldnt compose user quiz.';
		
		$quizId = intval($quizId);
		$userId = intval($userId);
		
		if ($this->isComposedUserQuiz($ticketId))
		{
			return true;
		}
		
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return false;
		}
		
		$quiz = $this->getQuiz($quizId);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return false;
		}
		
		if (empty($quiz->QuizId))
		{
			return false;
		}
		
		$statisticsId = $this->getStatisticsInfoIdByTicketId($ticketId, 0, 'Prepare');
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return false;
		}

		if (empty($statisticsId))
		{
			return false;
		}
		
		$qCategoryList = $this->getQuestionCategoryList($quizId, null, null);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return false;
		}
		
		if (!is_array($qCategoryList))
			$qCategoryList = array();
			
		// add uncategory questions
		$uncategory = new stdClass();
		$uncategory->QuestionCategoryId = 0;
		$uncategory->QuestionCount = $quiz->QuestionCount;
		$uncategory->QuestionTime = $quiz->QuestionTime;
		$qCategoryList[] = $uncategory;
		
		$defaultQuestionTime = $quiz->QuestionTime;
		$questions = $this->_composeQuestions($quizId, $quiz->RandomQuestion, $qCategoryList, $defaultQuestionTime);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return false;
		}

		$questionCount = !empty($questions) ? count($questions) : 0;
		if ($questionCount > 0)
		{
			$queryList = array();
			$index = 0;
			
			$queryList[] = sprintf('DELETE FROM #__ariquizstatistics WHERE StatisticsInfoId = %d', $statisticsId);
			foreach ($questions as $question)
			{
				$queryList[] = sprintf('INSERT INTO #__ariquizstatistics (QuestionVersionId,StatisticsInfoId,QuestionIndex,QuestionTime,QuestionCategoryId) ' . 
					'VALUES(%d,%d,%d,%d,%d)', 
					$question->QuestionVersionId, 
					$statisticsId, 
					$index,
					$question->QuestionTime, 
					$question->QuestionCategoryId);
				++$index;
			}
			
			$queryList[] = sprintf('UPDATE #__ariquizstatisticsinfo' . 
				' SET Status = "Process", StartDate = %s, PassedScore = %d, QuestionCount = %d, TotalTime = %s' .
				' WHERE StatisticsInfoId = %d AND Status = "Prepare"', 
				$this->_db->Quote(ArisDate::getDbUTC()),
				$quiz->PassedScore,
				$questionCount,
				is_null($quiz->TotalTime) ? 'NULL' : $quiz->TotalTime, 
				$statisticsId);
				
			foreach ($queryList as $queryItem)
			{
				$this->_db->setQuery($queryItem);
				$this->_db->query();
				if ($this->_db->getErrorNum())
				{
					trigger_error($error, E_USER_ERROR);
					return false;
				}
			}
		}
		
		return ($questionCount > 0);
	}
	
	function _composeQuestions($quizId, $randomQuestion, $qCategoryList, $defaultQuestionTime)
	{
		$error = 'ARI: Couldnt compose questions.';
		
		$questions = array();
		if (!empty($qCategoryList))
		{
			foreach ($qCategoryList as $qCategory)
			{
				$curQuestionTime = !empty($qCategory->QuestionTime) ? $qCategory->QuestionTime : $defaultQuestionTime;
				$questionCount = $qCategory->QuestionCount;
				$categoryId = $qCategory->QuestionCategoryId;
				$catQuestions = $randomQuestion 
					? $this->getRandomQuestions($quizId, $questionCount, $categoryId)
					: $this->getOrderedQuestions($quizId, $questionCount, $categoryId);
				if ($this->_isError(true, false))
				{
					trigger_error($error, E_USER_ERROR);
					return null;
				}
					
				$count = is_array($catQuestions) ? count($catQuestions) : 0;
				
				if ($count > 0)
				{
					if (!empty($curQuestionTime))
					{
						for ($i = 0; $i < $count; $i++)
						{
							$question =& $catQuestions[$i];
							if (empty($question->QuestionTime))
								$question->QuestionTime = $curQuestionTime;
						}
					}
					
					$questions = array_merge($questions, $catQuestions);
				}
			}

			$questions = $randomQuestion
				? $this->_normalizeRandomQuestions($questions)
				: $this->_normalizeOrderedQuestions($questions);
		}
		
		return $questions;
	}
	
	function _normalizeOrderedQuestions($questions)
	{
		$newQuestions = array();
		if (!empty($questions))
		{
			foreach ($questions as $question)
			{
				$newQuestions[$question->QuestionIndex] = $question;
			}
			
			ksort($newQuestions);
			$newQuestions = array_values($newQuestions); 
		}
		
		return $newQuestions;
	}
	
	function _normalizeRandomQuestions($questions)
	{
		if (!empty($questions))
		{
			// init rand mechanism for php < 4.2.0 version
			srand((float) microtime() * 10000000);
			shuffle($questions);
		}
		
		return $questions;
	}
	
	function getOrderedQuestions($quizId, $questionCount = null, $qCategoryId = 0)
	{
		$results = $this->_getQuestionForUserQuiz($quizId, ' QuestionIndex', $questionCount, $qCategoryId);
		if ($this->_isError(true, false))
		{
			trigger_error('ARI: Couldnt get ordered questions.', E_USER_ERROR);
			return null;
		}
		
		return $results;
	}
	
	function getRandomQuestions($quizId, $questionCount = null, $qCategoryId = 0)
	{
		$results = $this->_getQuestionForUserQuiz($quizId, ' RAND()', $questionCount, $qCategoryId);
		if ($this->_isError(true, false))
		{
			trigger_error('ARI: Couldnt get ordered questions.', E_USER_ERROR);
			return null;
		}
		
		return $results;
	}

	function _getQuestionForUserQuiz($quizId, $orderStr, $questionCount = null, $qCategoryId = 0)
	{
		$catPredicate = '';
		if ($qCategoryId !== null)
		{
			if (!empty($qCategoryId))
			{
				$catPredicate = sprintf(' AND SQV.QuestionCategoryId = %d', $qCategoryId);
			}
			else
			{
				$catPredicate = ' AND (SQV.QuestionCategoryId = 0 OR SQV.QuestionCategoryId IS NULL)';
			}
		}
		
		$query = sprintf('SELECT SQ.QuestionVersionId,SQ.QuestionIndex,SQV.QuestionTime,SQV.QuestionCategoryId' . 
			' FROM #__ariquizquestion SQ INNER JOIN #__ariquizquestionversion SQV' . 
			' 	ON SQ.QuestionVersionId = SQV.QuestionVersionId' . 
			' WHERE SQ.Status = %d AND SQ.QuizId = %d %s' . 
			' ORDER BY %s' . 
			' %s', 
			ARI_QUIZ_QUE_STATUS_ACTIVE,
			$quizId, 
			$catPredicate,
			$orderStr, 
			$this->_getLimit(!empty($questionCount) ? 0 : null, $questionCount));
		
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get questions for user quiz.', E_USER_ERROR);
			return null;
		}
		
		return $rows;
	}
	
	function isComposedUserQuiz($ticketId)
	{
		$query = sprintf('SELECT COUNT(*) FROM #__ariquizstatisticsinfo WHERE TicketId = %s AND Status <> "Prepare" LIMIT 0, 1', 
			$this->_db->Quote($ticketId));
		$this->_db->setQuery($query);
		$count = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt check thar quiz composed.', E_USER_ERROR);
			return false;
		}
		
		return ($count > 0);
	}
	
	function canTakeQuizByTicketId($ticketId, $userId, $group)
	{
		$error = 'ARI: Cant check quiz availability by ticket id.';
		
		$query = sprintf('SELECT QuizId FROM #__ariquizstatisticsinfo WHERE TicketId = %s AND (UserId = 0 OR UserId IS NULL OR UserId = %d) LIMIT 0,1',
			$this->_db->Quote($ticketId),
			intval($userId));
		$this->_db->setQuery($query);
		$quizId = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error($error, E_USER_ERROR);
			return false;
		}
		
		$canTake = $this->canTakeQuiz($quizId, $userId, $group);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return false;
		}
		
		return $canTake;
	}
	
	function skipQuestion($statisticsId, $skipDate = null)
	{
		if (empty($skipDate)) $skipDate = ArisDate::getDbUTC();
		$skipDate = $this->_db->Quote($skipDate);
		
		$query = sprintf('UPDATE #__ariquizstatistics' .
			' SET UsedTime = UsedTime + (UNIX_TIMESTAMP(%s) - UNIX_TIMESTAMP(StartDate)),' .
			' SkipCount = SkipCount + 1,SkipDate = %s,StartDate = NULL' .
			' WHERE StatisticsId = %d', 
			$skipDate,
			$skipDate, 
			intval($statisticsId));
		$this->_db->setQuery($query);
		$this->_db->query();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt skip question.', E_USER_ERROR);
			return null;
		}
		
		return true;
	}
	
	function updateStatisticsInfo($statistics)
	{
		if ($statistics && !$statistics->store())
		{
			trigger_error('ARI: Couldnt update statistics.', E_USER_ERROR);
			return false;
		}
		
		return true;
	}
	
	function getCurrentQuestion($ticketId, $userId = 0)
	{
		$error = 'ARI: Couldnt get current question.';
		
		$statisticsId = $this->getCurrentStatisticsId($ticketId, $userId);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		$statistics = $this->getStatistics($statisticsId, true);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		} 
		
		return $statistics;
	}
	
	function getCurrentStatisticsId($ticketId, $userId = 0)
	{
		$userId = intval($userId);
		$query = sprintf('SELECT SS.StatisticsId FROM' . 
			' #__ariquizstatisticsinfo SSI LEFT JOIN #__ariquizstatistics SS' . 
			' 	ON SSI.StatisticsInfoId = SS.StatisticsInfoId' . 
			' WHERE SSI.TicketId = %s AND' .
			' (%d = 0 OR SSI.UserId = %d) AND' .
			' SS.StartDate IS NOT NULL AND' .
			' SS.EndDate IS NULL AND' .
			' ((SS.QuestionTime IS NULL OR SS.QuestionTime = 0) OR (UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(SS.StartDate) + UsedTime < SS.QuestionTime)) AND' .
			' (SSI.TotalTime IS NULL OR SSI.TotalTime = 0 OR (UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(SSI.StartDate)) < SSI.TotalTime)' .
			' ORDER BY SS.SkipDate ASC,SS.QuestionIndex ASC' . 
			' LIMIT 0,1', 
			$this->_db->Quote($ticketId), 
			$userId, 
			$userId);
		$this->_db->setQuery($query);
		
		$result = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get current statistics id.', E_USER_ERROR);
			return 0;
		}
		
		return $result;
	}
	
	function getQuizIdByTicketId($ticketId)
	{
		$query = sprintf('SELECT QuizId FROM #__ariquizstatisticsinfo WHERE TicketId = %s LIMIT 0, 1',
			$this->_db->Quote($ticketId));
		$this->_db->setQuery($query);
		$quizId = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get quiz id by ticket id.', E_USER_ERROR);
			return 0;
		}
		
		return $quizId;
	}
	
	function getQuizByTicketId($ticketId)
	{
		$error = 'ARI: Couldnt get quiz by ticket ID.';
		
		$quiz = EntityFactory::createInstance('QuizEntity', ARI_ENTITY_GROUP);
		$query = sprintf('SELECT Q.*' .
			' FROM #__ariquiz Q INNER JOIN #__ariquizstatisticsinfo QSI' .
			' 	ON Q.QuizId = QSI.QuizId' .
			' WHERE QSI.TicketId = %s LIMIT 0,1',
			$this->_db->Quote($ticketId));
		$this->_db->setQuery($query);
		$quizFields = $this->_db->loadAssocList();
		if ($this->_db->getErrorNum())
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if (!empty($quizFields) && count($quizFields) > 0)
		{
			if (!$quiz->bind($quizFields[0]))
			{
				trigger_error($error, E_USER_ERROR);
				return null;
			}
		}

		return $quiz;
	}
	
	function setStatisticsStart($statisticsId, $ip, $startDate = null)
	{
		if (empty($startDate)) $startDate = ArisDate::getDbUTC();
		
		$query = sprintf('UPDATE #__ariquizstatistics SET StartDate = %s,IpAddress = %d WHERE StatisticsId = %d', 
			$this->_db->Quote($startDate),
			ip2long($ip), 
			intval($statisticsId));
		$this->_db->setQuery($query);
		$this->_db->query();
		
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: couldnt set statistics start date.', E_USER_ERROR);
			return false;
		}
		
		return true;
	}
	
	function getNextQuestion($ticketId, $userId = 0)
	{
		$error = 'ARI: Couldnt get next question.';
		
		$statisticsId = $this->getNextStatisticsId($ticketId, $userId);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if (empty($statisticsId)) return null;

		$statistics = $this->getStatistics($statisticsId, true);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		} 
		
		return $statistics;
	}
	
	function getNextStatisticsId($ticketId, $userId = 0)
	{
		$userId = intval($userId);
		$query = sprintf('SELECT SS.StatisticsId FROM' . 
			' #__ariquizstatisticsinfo SSI LEFT JOIN #__ariquizstatistics SS' . 
			' 	ON SSI.StatisticsInfoId = SS.StatisticsInfoId' . 
			' WHERE SSI.TicketId = %s AND' .
			' (%d = 0 OR SSI.UserId = %d) AND' . 
			' SS.EndDate IS NULL AND' . 
			' (SS.StartDate IS NULL OR (SS.QuestionTime IS NULL OR SS.QuestionTime = 0) OR (UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(SS.StartDate) + UsedTime) < SS.QuestionTime) AND' .
			' (SSI.TotalTime IS NULL OR SSI.TotalTime = 0 OR (UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(SSI.StartDate)) < SSI.TotalTime)' . 
			' ORDER BY SS.SkipDate ASC,SS.QuestionIndex ASC' . 
			' LIMIT 0,1', 
			$this->_db->Quote($ticketId), 
			$userId, 
			$userId);
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: couldnt get next statistics Id.', E_USER_ERROR);
			return null;
		}
		
		return $result;
	}
	
	function markQuizAsFinished($ticketId, $userId = 0)
	{
		$error = 'ARI: Couldnt mark quiz as finished.';
		$statisticsInfoId = $this->getStatisticsInfoIdByTicketId($ticketId, $userId, 'Process');
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return false;
		}
		
		if (!empty($statisticsInfoId))
		{
			$resultController = new AriQuizResultController();
			
			$finishedInfo = $resultController->getFinishedInfo($statisticsInfoId);
			if ($this->_isError(true, false))
			{
				trigger_error($error, E_USER_ERROR);
				return false;
			}
			
			$finishedDate = $this->getFinishedQuizDate($statisticsInfoId);
			if ($this->_isError(true, false))
			{
				trigger_error($error, E_USER_ERROR);
				return false;
			}
			
			$query = sprintf('UPDATE #__ariquizstatisticsinfo SET Status = "Finished",EndDate = %s,MaxScore = %d,UserScore = %d,Passed = %d WHERE StatisticsInfoId = %d',
				$this->_db->Quote($finishedDate),
				$finishedInfo['MaxScore'],
				$finishedInfo['UserScore'],
				$finishedInfo['Passed'],
				$statisticsInfoId);
			$this->_db->setQuery($query);
			$this->_db->query();
			if ($this->_db->getErrorNum())
			{
				trigger_error($error, E_USER_ERROR);
				return false;
			}
		}
		
		return true;
	}
	
	function getFinishedQuizDate($statisticsInfoId)
	{
		$statisticsInfoId = intval($statisticsInfoId);
		$query = sprintf('SELECT FROM_UNIXTIME(UNIX_TIMESTAMP(QS.StartDate) + UNIX_TIMESTAMP(QS.QuestionTime) - QS.UsedTime) AS EndDate1, QS.EndDate FROM #__ariquizstatistics QS WHERE StatisticsInfoId = %d ORDER BY QS.StartDate DESC LIMIT 0,1',
			$statisticsInfoId);
		$this->_db->setQuery($query);
		$date = null;
		$obj = $this->_db->loadAssocList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get finished quiz date.', E_USER_ERROR);
			return false;
		}

		if (!empty($obj) && count($obj) > 0)
		{
			$obj = $obj[0];
			$date = !empty($obj['EndDate']) ? $obj['EndDate'] : $obj['EndDate1'];
		}

		return $date;
	}
	
	function isQuizFinished($ticketId)
	{
		$error = 'ARI: Couldnt check that quiz finished.'; 
		
		$statisticsInfoId = $this->getStatisticsInfoIdByTicketId($ticketId);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return false;			
		}
		
		if (empty($statisticsInfoId))
		{
			return false;
		}

		$query = sprintf('SELECT COUNT(*) FROM #__ariquizstatistics QS LEFT JOIN #__ariquizstatisticsinfo QSI' .
			' 	ON QS.StatisticsInfoId = QSI.StatisticsInfoId' . 
			' WHERE QS.StatisticsInfoId = %d AND' . 
			' (QS.StartDate IS NULL OR' .
			' (QS.EndDate IS NULL AND' . 
			' (QS.QuestionTime IS NULL OR QS.QuestionTime = 0 OR' . 
			' UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(QS.StartDate) + QS.UsedTime < QS.QuestionTime ))) AND' .
			' (UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(QSI.StartDate)) < QSI.TotalTime' . 
			' LIMIT 0,1', 
			$statisticsInfoId);
		$this->_db->setQuery($query);
		$count = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error($error, E_USER_ERROR);
			return false;			
		}
		
		return ($count === 0 || $count === '0');
	}
	
	function getStatisticsInfoIdByTicketId($ticketId, $userId = 0, $status = null, $quizId = null)
	{
		$userId = intval($userId);
		if ($status != null && !is_array($status)) $status = array($status);

		$query = sprintf('SELECT StatisticsInfoId' .
			' FROM #__ariquizstatisticsinfo' .
			' WHERE TicketId = %s AND (%d = 0 OR UserId = %d) AND (%s IS NULL OR Status IN (%s)) AND (IFNULL(%s, 0) = 0 OR QuizId = %s)' .
			' LIMIT 0,1', 
			$this->_db->Quote($ticketId), 
			$userId, 
			$userId, 
			$status == null ? 'NULL' : '""',
			$status == null ? 'NULL' : join(',', $this->_quoteValues($status)),
			$quizId == null ? 'NULL' : $quizId,
			$quizId == null ? 'NULL' : $quizId);
			
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt statistics info entity by ticket ID.', E_USER_ERROR);
			return null;
		}
		
		return $result;
	}
	
	function createTicketId($quizId, $userId = 0)
	{		
		$quizId = intval($quizId);
		$userId = intval($userId);
		$ticketId = $this->_generateTicketId();
		$createdDate = ArisDate::getDbUTC();
		$query = sprintf('INSERT INTO #__ariquizstatisticsinfo (QuizId,UserId,Status,TicketId,CreatedDate)' . 
			' VALUES(%d,%d,"Prepare",%s,%s)', 
			$quizId, 
			$userId,
			$this->_db->Quote($ticketId),
			$this->_db->Quote($createdDate));
		$this->_db->setQuery($query);
		$this->_db->query();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt create ticket ID.', E_USER_ERROR);
			return '';
		}
		
		return $ticketId;
	}
	
	function _generateTicketId()
	{
		mt_srand((float)microtime() * 1000000);
		$key = mt_rand();
		
		return md5($key);
	}
	
	function canTakeQuiz($quizId, $userId, $group)
	{
		$error = 'ARI: Cant check quiz availability.';

		$acl =& JFactory::getACL();
		$quiz = $this->getQuiz($quizId);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return false;
		}
		
		if (!empty($quiz) && !empty($quiz->QuizId))
		{
			if ($quiz->Status == ARI_QUIZ_STATUS_ACTIVE)
			{
				// check quiz count and lag time
				if (!empty($userId) && (!empty($quiz->LagTime) || !empty($quiz->AttemptCount)))
				{
					$query = sprintf('SELECT COUNT(QuizId) AS QuizCount, (UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(MAX(EndDate))) AS LagTime' .
						' FROM #__ariquizstatisticsinfo' .
						' WHERE Status = "Finished" AND UserId = %d AND QuizId = %d' .
						' GROUP BY QuizId' .
						' LIMIT 0,1',
						$userId,
						$quizId);
					$this->_db->setQuery($query);
					$result = $this->_db->loadAssocList();
					if ($this->_db->getErrorNum())
					{
						trigger_error($error, E_USER_ERROR);
						return false;
					}
					
					$result = count($result) > 0 ? $result[0] : array('QuizCount' => 0, 'LagTime' => 0);
					if (($quiz->AttemptCount > 0 && $result['QuizCount'] >= $quiz->AttemptCount) ||
						($quiz->LagTime > 0 && $result['QuizCount'] > 0 && $result['LagTime'] < $quiz->LagTime))
					{
						return false;
					}
				}
				
				$accessList = $quiz->AccessList;
				if (empty($accessList))
				{
					return true;
				}

				$version = new JVersion();
				$j15 = version_compare($version->getShortVersion(), '1.6.0', '<');
				
				if ($j15)
				{
					$uah = new UserAccessHelper($acl);
					foreach ($accessList as $accessItem)
					{
						if ((!empty($userId) && $accessItem->value == UAH_REGISTERED_GROUP_ID) ||
							$uah->isGroupOrChildOfGroup($group, $accessItem->value))
						{
							return true;
						}
					}
				}
				else 
				{
					$user =& JFactory::getUser();
					if ($user && $user->get('id') > 0)
						return true;
				}
			}
		}
		
		return false;
	}
	
	function getUserQuizList()
	{
		$query = sprintf('SELECT QC.CategoryId,QC.CategoryName,Q.QuizName,Q.QuizId' .
			' FROM #__ariquiz Q LEFT JOIN #__ariquizquizcategory QQC' .
			' 	ON Q.QuizId = QQC.QuizId' .
			' LEFT JOIN #__ariquizcategory QC' .
			' 	ON QC.CategoryId = QQC.CategoryId' .
			' WHERE Q.Status = %d' .
			' ORDER BY QC.CategoryName ASC, Q.QuizName ASC',
			ARI_QUIZ_STATUS_ACTIVE);
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get quiz for user.', E_USER_ERROR);
			return null;
		}
		
		return $rows;
	}
	
	function getStatistics($statisticsId, $fullLoad = false)
	{		
		$error = 'ARI: Couldnt get statistics entity.';
		
		$statisticsId = intval($statisticsId);
		$stat = EntityFactory::createInstance('StatisticsEntity', ARI_ENTITY_GROUP);
		if (!$stat->load($statisticsId))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if ($fullLoad)
		{
			$question = $this->getQuestionByVersionId($stat->QuestionVersionId);
			if ($this->_isError(true, false))
			{
				trigger_error($error, E_USER_ERROR);
				return null;
			}
			
			$stat->Question = $question; 
		}
		
		return $stat;
	}
	
	function getQuestionByVersionId($questionVersionId)
	{
		$error = 'ARI: Couldnt get question by version id.';
		
		$questionVersion = $this->getQuestionVersion($questionVersionId);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		$question = $this->getQuestion($questionVersion->QuestionId, false);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}

		$question->QuestionVersion = $questionVersion;
		$question->QuestionVersionId = $questionVersionId;
		
		return $question;
	}
	
	function saveQuestionCategory($categoryId, $fields, $quizId, $ownerId)
	{		
		$error = 'ARI: Couldnt save question category.';
		
		$categoryId = intval($categoryId);
		$isUpdate = ($categoryId > 0);
		$row = $isUpdate ? $this->getQuestionCategory($categoryId) : EntityFactory::createInstance('QuestionCategoryEntity', ARI_ENTITY_GROUP);
		
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if (!$row->bind($fields))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if ($isUpdate)
		{
			$row->Modified = ArisDate::getDbUTC();
			$row->ModifiedBy = $ownerId;
		} 
		else
		{
			$row->QuizId = $quizId;
			$row->Created = ArisDate::getDbUTC();
			$row->CreatedBy = $ownerId;
		}
		
		if (!$row->store())
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		return $row;
	}
	
	function getQuestionCategory($questionCategoryId, $loadQuiz = false)
	{
		$error = 'ARI: Couldnt get question category.';
		
		$questionCategoryId = intval($questionCategoryId);
		$category = EntityFactory::createInstance('QuestionCategoryEntity', ARI_ENTITY_GROUP);
		if (!$category->load($questionCategoryId))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if ($loadQuiz)
		{
			$category->Quiz = $this->getQuiz($category->QuizId);
			if ($this->_isError(true, false))
			{
				trigger_error($error, E_USER_ERROR);
				return null;
			}
		}
		
		return $category;
	}
	
	function deleteQuestionCategory($idList, $deleteQuestions = false)
	{
		$idList = $this->_fixIdList($idList);
		if (empty($idList)) return true;
		
		$catStr = join(',', $this->_quoteValues($idList));

		$queryList = array();
		$queryList[] = sprintf('DELETE FROM #__ariquizquestioncategory WHERE QuestionCategoryId IN (%s)', 
			$catStr);

		if ($deleteQuestions)
		{
			$queryList[] = sprintf('UPDATE #__ariquizquestion QQ INNER JOIN #__ariquizquestionversion QQV' .
				'	 ON QQ.QuestionVersionId = QQV.QuestionVersionId' .
				' SET QQ.Status = %d WHERE QQV.QuestionCategoryId IN (%s)', 
				ARI_QUIZ_QUE_STATUS_DELETE, 
				$catStr);
		}
		else 
		{
			$queryList[] = sprintf('UPDATE #__ariquizquestion QQ INNER JOIN #__ariquizquestionversion QQV' .
				'	 ON QQ.QuestionVersionId = QQV.QuestionVersionId' .
				' SET QQV.QuestionCategoryId = 0 WHERE QQV.QuestionCategoryId IN (%s)',  
				$catStr);
		}
			
		foreach ($queryList as $queryItem)
		{
			$this->_db->setQuery($queryItem);
			$this->_db->query();
			if ($this->_db->getErrorNum())
			{
				trigger_error('ARI: Couldnt delete question category.', E_USER_ERROR);
				return false;
			}
		}

		return true;
	}
	
	function getQuestionCategoryCount($quizId)
	{
		$count = $this->_getRecordCount('#__ariquizquestioncategory', array('QuizId' => $quizId));
		if ($this->_isError(true, false))
		{
			trigger_error('ARI: Couldnt get question category count.', E_USER_ERROR);
		}
		
		return $count;
	}
	
	function getQuestionCategoryList($sortInfo = null, $quizId, $limitStart = null, $limit = null)
	{
		$quizId = intval($quizId);
		$query = sprintf('SELECT SQC.QuestionCategoryId, SQC.CategoryName, SQC.QuestionCount, SQC.QuestionTime, S.QuizName, S.QuizId' . 
			' FROM #__ariquizquestioncategory SQC INNER JOIN #__ariquiz S' . 
			' ON SQC.QuizId = S.QuizId ' . 
			' WHERE (%d = 0 OR SQC.QuizId = %d) AND (S.Status = %d OR S.Status = %d) AND (SQC.Status = %d) %s %s', 
			$quizId, 
			$quizId,
			ARI_QUIZ_STATUS_ACTIVE,
			ARI_QUIZ_STATUS_INACTIVE, 
			ARI_QUIZ_STATUS_ACTIVE,
			$this->_getOrder($sortInfo), 
			$this->_getLimit($limitStart, $limit));
		
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get question category list.', E_USER_ERROR);
			return null;
		}
		
		return $rows;
	}
	
	function saveQuestion($questionId, $quizId, $questionTypeId, $ownerId, $fields, $data)
	{
		$error = 'ARI: Couldnt save question.';

		$row = EntityFactory::createInstance('QuestionEntity', ARI_ENTITY_GROUP);
		$isUpdate = ($questionId > 0);
		if ($isUpdate)
		{
			$row = $this->getQuestion($questionId);
			if ($this->_isError(true, false))
			{
				trigger_error($error, E_USER_ERROR);
				return null;
			}
			
			$row->Modified = ArisDate::getDbUTC();
			$row->ModifiedBy = $ownerId;
		} 
		else
		{
			$row->QuizId = $quizId;
			$row->Created = ArisDate::getDbUTC();
			$row->CreatedBy = $ownerId;
			$row->QuestionIndex = $this->_getRecordCount('#__ariquizquestion', array('QuizId' => $quizId));
			if ($this->_isError(true, false))
			{
				trigger_error($error, E_USER_ERROR);
				return null;
			}
			$row->Status = ARI_QUIZ_QUE_STATUS_ACTIVE;
		}
		
		if (!$row->bind($fields))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if (!$row->store())
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		$subRow = EntityFactory::createInstance('QuestionVersionEntity', ARI_ENTITY_GROUP);
		$subRow->QuestionId = $row->QuestionId;
		$subRow->Data = $data;
		$subRow->Created = ArisDate::getDbUTC();
		$subRow->CreatedBy = $ownerId;
		$subRow->QuestionTypeId = $questionTypeId;
		if (!$subRow->bind($fields) || !$subRow->store())
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		$row->QuestionVersionId = $subRow->QuestionVersionId;
		if (!$row->store())
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		return $row;
	}
	
	function getQuestion($questionId, $loadQuestionVersion = true)
	{
		$error = 'ARI: Couldnt get question.';

		$question = EntityFactory::createInstance('QuestionEntity', ARI_ENTITY_GROUP);
		if (!$question->load($questionId))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if ($loadQuestionVersion && $question != null && !empty($question->QuestionVersionId))
		{
			$questionVersion = $this->getQuestionVersion($question->QuestionVersionId);
			if ($this->_isError(true, false))
			{
				trigger_error($error, E_USER_ERROR);
				return null;
			}
			
			$question->QuestionVersion = $questionVersion; 
		}
		
		return $question;
	}
	
	function getQuestionVersion($questionVersionId)
	{
		$error = 'ARI: Couldnt get question version.';
		
		$questionVersion = EntityFactory::createInstance('QuestionVersionEntity', ARI_ENTITY_GROUP);
		if (!$questionVersion->load($questionVersionId))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		$questionType = $this->getQuestionType($questionVersion->QuestionTypeId);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		$questionVersion->QuestionType = $questionType; 
		
		return $questionVersion;
	}
	
	function deleteQuestion($idList)
	{
		$complete = $this->changeQuestionStatus($idList, ARI_QUIZ_QUE_STATUS_DELETE);
		if ($this->_isError(true, false))
		{
			trigger_error('ARI: Couldnt delete question.', E_USER_ERROR);
			return false;
		}
		
		return $complete;
	}
	
	function changeQuestionStatus($idList, $status)
	{
		$idList = $this->_fixIdList($idList);
		if (empty($idList)) return true;
		
		$status = intval($status);
		$query = sprintf('UPDATE #__ariquizquestion SET Status = %d WHERE QuestionId IN (%s)', 
			$status, 
			join(',', $this->_quoteValues($idList)));
		$this->_db->setQuery($query);
		$this->_db->query();

		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt change question status.', E_USER_ERROR);
			return false;
		}
		
		return true;
	}
	
	function changeQuestionOrder($questionId, $dir)
	{
		$error = 'ARI: Couldnt change question order.';
		
		$questionId = intval($questionId);
		$dir = intval($dir);
		$query = sprintf('SELECT QQ1.QuestionId, QQ.QuestionIndex AS OldIndex, QQ1.QuestionIndex AS NewIndex ' .
			' FROM #__ariquizquestion QQ LEFT JOIN #__ariquizquestion QQ1' .
			' 	ON QQ.QuizId = QQ1.QuizId' . 
			' WHERE QQ.QuestionId = %d AND QQ1.QuestionIndex %s QQ.QuestionIndex AND QQ1.Status = %d ORDER BY QQ1.QuestionIndex %s LIMIT 0,1',
			$questionId,
			$dir > 0 ? '>' : '<',
			ARI_QUIZ_QUE_STATUS_ACTIVE,
			$dir > 0 ? 'ASC' : 'DESC');
		
		$this->_db->setQuery($query);
		$obj = $this->_db->loadAssocList();
		if ($this->_db->getErrorNum())
		{
			trigger_error($error, E_USER_ERROR);
			return false;
		}
		
		if (!empty($obj) && count($obj) > 0)
		{
			$obj = $obj[0];
			$queryList = array();
			$queryList[] = sprintf('UPDATE #__ariquizquestion SET QuestionIndex = %d WHERE QuestionId = %d',
				$obj['NewIndex'],
				$questionId);
			$queryList[] = sprintf('UPDATE #__ariquizquestion SET QuestionIndex = %d WHERE QuestionId = %d',
				$obj['OldIndex'],
				$obj['QuestionId']);
				
			foreach ($queryList as $queryItem)
			{
				$this->_db->setQuery($queryItem);
				$this->_db->query();
				if ($this->_db->getErrorNum())
				{
					trigger_error($error, E_USER_ERROR);
					return false;
				}
			}
		}
		
		return true;
	}
	
	function getQuestionCount($quizId)
	{
		$count = $this->_getRecordCount('#__ariquizquestion', array('QuizId' => $quizId, 'Status' => ARI_QUIZ_QUE_STATUS_ACTIVE));
		if ($this->_isError(true, false))
		{
			trigger_error('ARI: Couldnt get question count.', E_USER_ERROR);
		}
		
		return $count;
	}
	
	function getQuestionList($sortInfo = null, $quizId, $limitStart = null, $limit = null)
	{
		$query = sprintf('SELECT S.QuizName, S.QuizId, SQ.QuestionId, SQV.Question, QQT.QuestionType, SQV.Created, SQC.QuestionCategoryId, SQC.CategoryName ' . 
			' FROM #__ariquiz S INNER JOIN #__ariquizquestion SQ' . 
			' 	ON S.QuizId = SQ.QuizId' . 
			' INNER JOIN #__ariquizquestionversion SQV' . 
			' 	ON SQ.QuestionVersionId = SQV.QuestionVersionId' .
			' INNER JOIN #__ariquizquestiontype QQT' .
			'	ON SQV.QuestionTypeId = QQT.QuestionTypeId' . 
			' LEFT JOIN #__ariquizquestioncategory SQC' . 
			' 	ON SQV.QuestionCategoryId = SQC.QuestionCategoryId' . 
			' WHERE SQ.Status = %d AND S.QuizId = %s' . 
			' ORDER BY QuestionIndex ASC'.
			' %s',
			ARI_QUIZ_QUE_STATUS_ACTIVE, 
			$this->_db->Quote($quizId),  
			$this->_getLimit($limitStart, $limit));

		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get question list.', E_USER_ERROR);
			return null;
		}
		
		return $rows;
	}
	
	function saveQuestionTemplate($templateId, $questionTypeId, $ownerId, $fields, $data)
	{
		$error = 'ARI: Couldnt save question template.';

		$row = EntityFactory::createInstance('QuestionTemplateEntity', ARI_ENTITY_GROUP);
		$isUpdate = ($templateId > 0);
		if ($isUpdate)
		{
			$row = $this->getQuestionTemplate($templateId);
			if ($this->_isError(true, false))
			{
				trigger_error($error, E_USER_ERROR);
				return null;
			}

			$row->Modified = ArisDate::getDbUTC();
			$row->ModifiedBy = $ownerId;
		} 
		else
		{
			$row->Created = ArisDate::getDbUTC();
			$row->CreatedBy = $ownerId;
		}
		
		$row->DisableValidation = !empty($fields['DisableValidation']);
		
		if (!$row->bind($fields))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		$row->QuestionTypeId = $questionTypeId;
		$row->Data = $data;
		if (!$row->store())
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		return $row;
	}
	
	function getQuestionTemplate($templateId)
	{
		$error = 'ARI: Couldnt get question template.';
		
		$templateId = intval($templateId);
		$template = EntityFactory::createInstance('QuestionTemplateEntity', ARI_ENTITY_GROUP);
		if (!$template->load($templateId))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		$questionType = $this->getQuestionType($template->QuestionTypeId);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		$template->QuestionType = $questionType;
		
		return $template;
	}
	
	function getQuestionType($questionTypeId)
	{
		$questionTypeId = intval($questionTypeId);
		$questionType = EntityFactory::createInstance('QuestionTypeEntity', ARI_ENTITY_GROUP);
		if (!$questionType->load($questionTypeId))
		{
			trigger_error('ARI: Couldnt get question type.', E_USER_ERROR);
			return null;
		}
		
		return $questionType;
	}
	
	function getQuestionTypeList($forTemplate = false)
	{
		$query = sprintf('SELECT QuestionTypeId, QuestionType, ClassName FROM #__ariquizquestiontype' . 
			' WHERE %s IS NULL OR CanHaveTemplate = 1 ORDER BY `Default` DESC, QuestionType ASC',
			$forTemplate ? '1' : 'NULL');
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get question type list.', E_USER_ERROR);
			return null;
		}
		
		return $rows;
	}
	
	function deleteQuestionTemplate($idList)
	{
		$idList = $this->_fixIdList($idList);
		if (empty($idList)) return true;
		
		$idStr = join(',', $this->_quoteValues($idList));
		$query = sprintf('DELETE FROM #__ariquizquestiontemplate WHERE TemplateId IN (%s)', 
			$idStr);

		$this->_db->setQuery($query);
		$this->_db->query();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt delete question template.', E_USER_ERROR);
			return false;
		}

		return true;
	}
	
	function getQuestionTemplateList($sortInfo = null)
	{
		$query = sprintf('SELECT QQT.QuestionTypeId, QQT.TemplateName, QQT.TemplateId, QQT.Created, QQT.Modified, QQTY.QuestionType ' .
			' FROM #__ariquizquestiontemplate QQT' .
			' INNER JOIN #__ariquizquestiontype QQTY ON QQT.QuestionTypeId = QQTY.QuestionTypeId %s',
			$this->_getOrder($sortInfo));
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get question template list.', E_USER_ERROR);
			return null;
		}
		
		return $rows;
	}
	
	function saveCategory($categoryId, $fields, $ownerId)
	{
		$error = 'ARI: Couldnt save category.'; 
		
		$categoryId = intval($categoryId);
		$isUpdate = ($categoryId > 0);
		$row = $isUpdate ? $this->getCategory($categoryId) : EntityFactory::createInstance('CategoryEntity', ARI_ENTITY_GROUP);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if (!$row->bind($fields))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if ($isUpdate)
		{
			$row->Modified = ArisDate::getDbUTC();
			$row->ModifiedBy = $ownerId;
		} 
		else
		{
			$row->Created = ArisDate::getDbUTC();
			$row->CreatedBy = $ownerId;
		}
		
		if (!$row->store())
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		return $row;
	}
	
	function getCategory($categoryId)
	{
		$categoryId = intval($categoryId);
		$category = EntityFactory::createInstance('CategoryEntity', ARI_ENTITY_GROUP);
		if (!$category->load($categoryId))
		{
			trigger_error('ARI: Couldnt get category.', E_USER_ERROR);
			return null;
		}
		
		return $category;
	}
	
	function deleteCategory($idList)
	{
		$idList = $this->_fixIdList($idList);
		if (empty($idList)) return true;
		
		$queryList = array();
		$catStr = join(',', $this->_quoteValues($idList));
		$queryList[] = sprintf('DELETE FROM #__ariquizcategory WHERE CategoryId IN (%s)', 
			$catStr);
		$queryList[] = sprintf('DELETE FROM #__ariquizquizcategory WHERE CategoryId IN (%s)', 
			$catStr);
			
		foreach ($queryList as $queryItem)
		{
			$this->_db->setQuery($queryItem);
			$this->_db->query();
			if ($this->_db->getErrorNum())
			{
				trigger_error('ARI: Couldnt delete category.', E_USER_ERROR);
				return false;
			}
		}

		return true;
	}
	
	function getCategoryCount()
	{
		$count = $this->_getRecordCount('#__ariquizcategory');
		if ($this->_isError(true, false))
		{
			trigger_error('ARI: Couldnt get category count.', E_USER_ERROR);
		}
		
		return $count; 
	}
	
	function saveQuiz($quizId, $fields, $ownerId, $categoryList, $accessList, $textTemplateList)
	{
		$error = 'ARI: Couldnt save quiz.';
		
		$quizId = @intval($quizId, 10);
		$isUpdate = ($quizId > 0);
		$row = $isUpdate ? $this->getQuiz($quizId) : EntityFactory::createInstance('QuizEntity', ARI_ENTITY_GROUP);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if (!$row->bind($fields))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		if ($isUpdate)
		{
			// clear old category
			$query = sprintf('DELETE FROM #__ariquizquizcategory WHERE QuizId = %d', $quizId);
			$this->_db->setQuery($query);
			$this->_db->query();
			
			// clear old access group
			$query = sprintf('DELETE FROM #__ariquizaccess WHERE QuizId = %d', $quizId);
			$this->_db->setQuery($query);
			$this->_db->query();
			
			$row->Modified = ArisDate::getDbUTC();
			$row->ModifiedBy = $ownerId;
		} 
		else
		{
			$row->Created = ArisDate::getDbUTC();
			$row->CreatedBy = $ownerId;
		}
		
		$row->Status = !empty($fields['Active']) ? ARI_QUIZ_STATUS_ACTIVE : ARI_QUIZ_STATUS_INACTIVE;
		$row->CanSkip = !empty($fields['CanSkip']);
		$row->RandomQuestion = !empty($fields['RandomQuestion']);
		
		if (!$row->store())
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		$templateController = new AriTextTemplateController();
		$templateController->setEntitySingleTemplate(ARI_QUIZ_ENTITY_KEY, $row->QuizId, $textTemplateList);
		
		if (!empty($categoryList))
		{
			$query = sprintf('INSERT INTO #__ariquizquizcategory (CategoryId,QuizId) VALUES(%s,%s)', '%s', $row->QuizId);
			foreach ($categoryList as $categoryId)
			{
				$this->_db->setQuery(sprintf($query, $this->_db->Quote($categoryId)));
				$this->_db->query();
			}
		}
		
		if (!empty($accessList))
		{
			$query = sprintf('INSERT INTO #__ariquizaccess (QuizId,GroupId) VALUES(%d,%s)', $row->QuizId, '%d');
			foreach ($accessList as $groupId)
			{
				$groupId = intval($groupId);
				if ($groupId > 0)
				{
					$this->_db->setQuery(sprintf($query, $groupId));
					$this->_db->query();
				}
			}
		}

		return $row;
	}
	
	function getQuiz($quizId)
	{
		$error = 'ARI: Couldnt get quiz.';
		
		$quizId = intval($quizId);
		$quiz = EntityFactory::createInstance('QuizEntity', ARI_ENTITY_GROUP);
		$quiz->load($quizId);
		
		$query = sprintf('SELECT CategoryId FROM #__ariquizquizcategory WHERE QuizId = %d', $quizId);
		$this->_db->setQuery($query);
		$quiz->CategoryList = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		$quiz->AccessList = $this->getQuizAccessList($quizId);
		if ($this->_isError(true, false))
		{
			trigger_error($error, E_USER_ERROR);
			return null;
		}
		
		return $quiz;
	}
	
	function getQuizAccessList($quizId)
	{
		$query = sprintf('SELECT GroupId AS value FROM #__ariquizaccess WHERE QuizId = %d', $quizId);
		$this->_db->setQuery($query);
		$accessList = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get quiz access list.', E_USER_ERROR);
			return null;
		}
		
		return $accessList;
	}
	
	function changeQuizStatus($idList, $status)
	{
		$idList = $this->_fixIdList($idList);
		if (empty($idList)) return true;
		
		$status = intval($status);
		$query = sprintf('UPDATE #__ariquiz SET Status = %d WHERE QuizId IN (%s)', 
			$status, 
			join(',', $this->_quoteValues($idList)));
		$this->_db->setQuery($query);
		$this->_db->query();

		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt change quiz status.', E_USER_ERROR);
			return false;
		}
		
		return true;
	}
	
	function deleteQuiz($idList)
	{
		$complete = $this->changeQuizStatus($idList, ARI_QUIZ_STATUS_DELETE);
		if ($this->_isError(true, false))
		{
			trigger_error('ARI: Couldnt delete quiz.', E_USER_ERROR);
			return false;
		}
		
		return $complete;
	}

	function activateQuiz($idList)
	{
		$complete = $this->changeQuizStatus($idList, ARI_QUIZ_STATUS_ACTIVE);
		if ($this->_isError(true, false))
		{
			trigger_error('ARI: Couldnt activate quiz.', E_USER_ERROR);
			return false;
		}
		
		return $complete;
	}
	
	function deactivateQuiz($idList)
	{
		$complete = $this->changeQuizStatus($idList, ARI_QUIZ_STATUS_INACTIVE);
		if ($this->_isError(true, false))
		{
			trigger_error('ARI: Couldnt deactivate quiz.', E_USER_ERROR);
			return false;
		}
		
		return $complete;
	}
	
	function getCategoryList($sortInfo = null, $limitStart = null, $limit = null)
	{
		$query = 'SELECT CategoryId, CategoryName' . 
			' FROM #__ariquizcategory ' . 
			$this->_getOrder($sortInfo) . 
			$this->_getLimit($limitStart, $limit);
		
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get category list.', E_USER_ERROR);
			return null;
		}
		
		return $rows;
	}
	
	function getQuizList($categoryId = null, $status = null, $sortInfo = null, $limitStart = null, $limit = null)
	{
		$query = sprintf('SELECT Q.QuizId, Q.QuizName, Q.Status, QC.CategoryName' . 
			' FROM #__ariquiz Q LEFT JOIN #__ariquizquizcategory QQC' .
			' 	ON Q.QuizId = QQC.QuizId' .
			' LEFT JOIN #__ariquizcategory QC' .
			' 	ON QQC.CategoryId = QC.CategoryId' .
			' WHERE Status <> ' . ARI_QUIZ_STATUS_DELETE . 
			' AND (%1$s IS NULL OR Q.Status = %2$d) AND (%3$s IS NULL OR QQC.CategoryId = %4$d)' .
			' GROUP BY Q.QuizId' . 
			$this->_getOrder($sortInfo) . 
			$this->_getLimit($limitStart, $limit),
			$this->_normalizeValue($status),
			intval($status),
			$this->_normalizeValue($categoryId),
			intval($categoryId));
		
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get quiz list.', E_USER_ERROR);
			return null;
		}
		
		return $rows;
	}
	
	function getQuizCount($categoryId = null, $status = null)
	{
		$query = sprintf('SELECT COUNT(*)' . 
			' FROM #__ariquiz Q LEFT JOIN #__ariquizquizcategory QQC' .
			' 	ON Q.QuizId = QQC.QuizId' .
			' LEFT JOIN #__ariquizcategory QC' .
			' 	ON QQC.CategoryId = QC.CategoryId' .
			' WHERE Q.Status <> ' . ARI_QUIZ_STATUS_DELETE . 
			' AND (%1$s IS NULL OR Q.Status = %2$d) AND (%3$s IS NULL OR QQC.CategoryId = %4$d)',
			$this->_normalizeValue($status),
			intval($status),
			$this->_normalizeValue($categoryId),
			intval($categoryId));
		$this->_db->setQuery($query);
		$count = $this->_db->loadResult();
		if ($this->_db->getErrorNum())
		{
			trigger_error('ARI: Couldnt get quiz count.', E_USER_ERROR);
			return 0;
		}
		
		return $count;
	}	
}
?>
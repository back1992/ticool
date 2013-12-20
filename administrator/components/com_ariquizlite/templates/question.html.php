<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$option = $processPage->getVar('option');
	$task = $processPage->getVar('task');
	$statistics = $processPage->getVar('statistics');
	$className = $processPage->getVar('className');
	$specificQuestion = $processPage->getVar('specificQuestion');
	$statisticsId = $processPage->getVar('statisticsId');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminform">
	<tr>
		<td style="width: 1%; text-align: left; white-space: nowrap;"><b><?php AriQuizWebHelper::displayResValue('Label.Score'); ?> :</b></td>
		<td>
			<?php AriQuizWebHelper::showValue($statistics->Score, 0); ?> / <?php echo $statistics->Question->QuestionVersion->Score; ?>
		</td>
	</tr>
	<tr>
		<td style="width: 1%; text-align: left; white-space: nowrap;"><b><?php AriQuizWebHelper::displayResValue('Label.Question'); ?> <?php AriQuizWebHelper::displayResValue('Label.NumberPos'); ?> :</b></td>
		<td>
			<?php echo ($statistics->QuestionIndex + 1); ?>
		</td>
	</tr>
	<tr>
		<td style="width: 1%; text-align: left; white-space: nowrap;"><b><?php AriQuizWebHelper::displayResValue('Label.Question'); ?> :</b></td>
		<td>
			<?php AriQuizWebHelper::displayDbValue($statistics->Question->QuestionVersion->Question, false); ?>
		</td>
	</tr>
</table>
<br/>
<?php
	$path = AriQuizHelper::getQuestionTemplatePath($className, 'statistics');
	if (!empty($path)) require_once($path);
?>
<br /><br />
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="question" />
<input type="hidden" id="tbxStatisticsId" name="sid" value="<?php echo $statisticsId; ?>" />
</form>
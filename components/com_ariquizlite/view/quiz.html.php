<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$quiz = $processPage->getVar('quiz');
	$ticketId = $processPage->getVar('ticketId');
	$arisI18N = $processPage->getVar('arisI18N');
	$option = $processPage->getVar('option');
	$task = $processPage->getVar('task');
	$canTakeQuiz = $processPage->getVar('canTakeQuiz');
	$Itemid = $processPage->getVar('Itemid');
?>

<div style="margin: 4px 4px 4px 4px;">
	<form action="<?php echo JURI::root(true); ?>/index.php" method="post">
	<h1 align="center"><?php AriQuizWebHelper::displayDbValue($quiz->QuizName); ?></h1><br />
	<?php AriQuizWebHelper::displayDbValue($quiz->Description, false); ?>
	<br /><br />
<?php
	if ($canTakeQuiz)
	{
?>
	<input type="submit" class="button" value="<?php AriQuizWebHelper::displayResValue('Label.Continue'); ?>" />
<?php
	}
?>
	<input type="hidden" name="tmpl" value="<?php echo JRequest::getString('tmpl', ''); ?>" />
	<input type="hidden" name="task" value="get_ticket" />
	<input type="hidden" name="quizId" value="<?php echo $quiz->QuizId; ?>" />
	<input type="hidden" name="option" value="<?php echo $option; ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
	</form>
</div>
<br/>
<div style="text-align: center;">
Developed by <a href="http://www.ari-soft.com" target="_blank" title="ARI Soft">ARI Soft</a>.
</div>
<br/>
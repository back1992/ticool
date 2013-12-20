<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$arisI18N = $processPage->getVar('arisI18N');
	$option = $processPage->getVar('option');
	$ticketId = $processPage->getVar('ticketId');
	$questionVersion = $processPage->getVar('questionVersion');
	$questionVersionId = $processPage->getVar('questionVersionId');
	$questionTime = $processPage->getVar('questionTime');
	$quizInfo = $processPage->getVar('quizInfo');
	$statistics = $processPage->getVar('statistics');
	$questionData = $processPage->getVar('questionData');
	$progressPercent = $processPage->getVar('progressPercent');
	$completedCount = $processPage->getVar('completedCount');
	$totalTime = $processPage->getVar('totalTime');
	$hasSplitter = ($questionTime != null && $totalTime != null);
	$itemId = $processPage->getVar('Itemid');
	$tmpl = JRequest::getString('tmpl', '');
	
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/yahoo-dom-event.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/validators.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/ari.dom.js');
?>

<form method="post" action="<?php echo JURI::root(true); ?>/index.php" name="formQue_<?php echo $questionVersionId; ?>" id="formQue_<?php echo $questionVersionId; ?>" style="margin: 5px 5px 5px 5px;">

<table class="ariQuizHeaderTable">
	<tr>
		<td class="ariQuizHeaderName">
			<?php AriQuizWebHelper::displayDbValue($quizInfo->QuizName); ?>
		</td>
		<td rowspan="2" class="ariQuizTimeCnt">
			<?php
				if ($questionTime !== null || $totalTime !== null) echo AriQuizWebHelper::getResValue('Label.RemainingTime') . ' : ';  
			?>
			<input type="text" size="<?php echo $questionTime != null ? strlen($questionTime) : '1'; ?>" readonly="readonly" style="display: none;" <?php if ($questionTime != null) { ?>class="ariQuizTime<?php if ($questionTime < 31) echo ' ariQuizTimeEnd'; ?>"<?php } ?> id="tbxAriQuizTime_<?php echo $questionVersionId; ?>" value="<?php echo $questionTime != null ? $questionTime : ''; ?>" />
			<?php
				if ($hasSplitter) echo ' / ';
			?>
			<input type="text" size="<?php echo $totalTime != null ? strlen($totalTime) : '1'; ?>" readonly="readonly" style="display: none;" <?php if ($totalTime != null) { ?>class="ariQuizTime<?php if ($totalTime < 31) echo ' ariQuizTimeEnd'; ?>"<?php } ?> id="tbxAriQuizTotalTime_<?php echo $questionVersionId; ?>" value="<?php echo $totalTime != null ? $totalTime : ''; ?>" />
		</td>
	</tr>
	<tr>
		<td>
		<table class="ariQuizHeaderInfo">
			<tr valign="middle">
				<td style="white-space: nowrap; width: 1%;">
					<?php AriQuizWebHelper::displayResValue('Label.Completed'); ?>
				</td>
				<td>
					<div class="ariQuizProgressWrap" title="<?php echo $completedCount . ' / ' . $quizInfo->QuestionCount; ?>">
						<div class="ariQuizProgress" style="width: <?php echo $progressPercent; ?>%;"><?php echo JHTML::image('components/' . $option . '/images/x.gif', '', array('border' => 0, 'width' => 1, 'height' => 7)); ?></div>
					</div>
				</td>
			</tr>
		</table>
		</td>
	</tr>
</table>

<table class="ariQuizQuestionTable">
	<tr>
		<td class="ariQuizQuestionTitleCnt ariQuizQuestionLeft"><div class="ariQuizQuestionTitle"><?php AriQuizWebHelper::displayResValue('Label.Question'); ?></div></td>
		<td class="ariQuizQuestionRight"><?php AriQuizWebHelper::displayDbValue($questionVersion->Question, false); ?></td>
	</tr>
	<tr>
		<td colspan="2">
			<?php
				$path = AriQuizFrontHelper::getQuestionTemplatePath($questionVersion);
				if (!empty($path)) require_once($path);
			?>			
		</td>
	</tr>
	<tr>
		<td rowspan="2">&nbsp;</td>
		<td>
			<input type="submit" class="button" value="<?php AriQuizWebHelper::displayResValue('Label.Save'); ?>" name="ariEvent[save]" disabledAfterSubmit="true" onclick="return aris.validators.alertSummaryValidators.validate();" />
			<?php
			if ($quizInfo->CanSkip)
			{
			?>	
			<input type="submit" class="button" value="<?php AriQuizWebHelper::displayResValue('Label.Skip'); ?>" name="ariEvent[skip]" disabledAfterSubmit="true" />
			<?php
			}
			?>
		</td>
	</tr>
	<tr>
		<td><?php printf(AriQuizWebHelper::getResValue('Label.QuestionInfo'), $statistics->QuestionIndex + 1, $quizInfo->QuestionCount); ?>
		</td>
	</tr>
</table>

<?php if ($tmpl): ?>
<input type="hidden" name="tmpl" value="<?php echo $tmpl; ?>" />
<?php endif; ?>
<?php if (!is_null($itemId) && strlen($itemId) > 0): ?>
<input type="hidden" name="Itemid" value="<?php echo $itemId; ?>" />
<?php endif; ?>
<input type="hidden" name="option" value="<?php echo $option; ?>" />
<input type="hidden" name="task" id="task" value="question" />
<input type="hidden" name="ticketId" value="<?php echo $ticketId; ?>" />
<input type="hidden" name="qid" value="<?php echo $questionVersion->QuestionId; ?>" />
<input type="hidden" name="timeOver" id="timeOver" value="false" /> 
</form>
<script type="text/javascript">
	var frm = document.forms['formQue_<?php echo $questionVersionId; ?>'];
	frm.onsubmit = function()
	{
		var frm = document.forms['formQue_<?php echo $questionVersionId; ?>'];
		var elements = aris.DOM.getChildElementsByAttribute(frm, 'disabledAfterSubmit');
		if (elements)
		{
			for (var i = 0; i < elements.length; i++)
			{
				var el = elements[i];
				if (typeof(el.disabled) != 'undefined')
				{
					var cloneEl = el.cloneNode(true);
					cloneEl.disabled = true;
					el.parentNode.insertBefore(cloneEl, el);
					el.style.display = 'none';
				}
			}
		}
	}
</script>
<?php
	if ($questionTime != null || $totalTime != null)
	{
?>
<script type="text/javascript">
	var ariQuestionTime = <?php echo is_null($questionTime) ? 'null' : $questionTime; ?>; 
	var ariTotalTime = <?php echo is_null($totalTime) ? 'null' : $totalTime; ?>;
	var ariStartDate = (new Date()).getTime();

	var timeUpdateTimer = YAHOO.util.Lang.later(
		999, 
		null, 
		function()
		{
			var curDate = (new Date()).getTime();
			var subSeconds = Math.round((curDate - ariStartDate) / 1000); 
			if (ariQuestionTime != null) ariQuestionTime -= subSeconds;
			if (ariTotalTime != null) ariTotalTime -= subSeconds;
			ariStartDate = curDate;
			if ((ariQuestionTime != null && ariQuestionTime <= 0) ||
				(ariTotalTime != null && ariTotalTime <= 0))
			{
				ariQuestionTime = 0;
				ariTotalTime = 0;
				timeUpdateTimer.cancel();
				var frm = document.forms['formQue_<?php echo $questionVersionId; ?>'];
				if (frm)
				{
					var timeOver = YAHOO.util.Dom.get('timeOver');
					if (timeOver) timeOver.value = 'true';
					frm.submit();
					return ;
				}
			}
			
			if (ariQuestionTime != null)
			{
				var tbxAriQuizTime = YAHOO.util.Dom.get('tbxAriQuizTime_<?php echo $questionVersionId; ?>');
				if (ariQuestionTime < 31) YAHOO.util.Dom.addClass(tbxAriQuizTime, 'ariQuizTimeEnd');
				tbxAriQuizTime.value = ariQuestionTime;
			}
			
			if (ariTotalTime != null)
			{
				var tbxAriQuizTotalTime = YAHOO.util.Dom.get('tbxAriQuizTotalTime_<?php echo $questionVersionId; ?>');
				if (ariTotalTime < 31) YAHOO.util.Dom.addClass(tbxAriQuizTotalTime, 'ariQuizTimeEnd');
				tbxAriQuizTotalTime.value = ariTotalTime;
			}
		},
		null, true);
</script>
<?php
	}
?>
<br/>
<div style="text-align: center;">
Developed by <a href="http://www.ari-soft.com" target="_blank" title="ARI Soft">ARI Soft</a>.
</div>
<br/>
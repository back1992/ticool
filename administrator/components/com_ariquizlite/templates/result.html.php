<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$option = $processPage->getVar('option');
	$task = $processPage->getVar('task');
	$quizId = $processPage->getVar('quizId');
	$resultList = $processPage->getVar('resultList');
	$statisticsInfoId = $processPage->getVar('statisticsInfoId');
	$textTemplateList = $processPage->getVar('textTemplateList');
?>

<table class="adminheading">
	<tr>
		<td align="right" style="white-space: nowrap;">
			<?php
				if (!empty($textTemplateList) && count($textTemplateList) > 0)
				{
			?>
			<b><?php AriQuizWebHelper::displayResValue('Label.PreviewTemplate'); ?> : </b>
			<?php
					echo JHTML::_('select.genericlist', $textTemplateList, 'templateId', 'id="ddlTemplate" class="text_area"', 'TemplateId', 'TemplateName', null);
				}
			?>
		</td>
	</tr>
</table>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th class="title" width="20"><?php echo JText::_( 'Num' ); ?></th>
			<th class="title" width="10%"><?php AriQuizWebHelper::displayResValue('Label.User'); ?></th>
			<th class="title"><?php AriQuizWebHelper::displayResValue('Label.Question'); ?></th>
			<th class="title"><?php AriQuizWebHelper::displayResValue('Label.Category'); ?></th>
			<th class="title" width="5%"><?php AriQuizWebHelper::displayResValue('Label.QuestionType'); ?></th>
			<th class="title" width="5%" nowrap="nowrap"><?php AriQuizWebHelper::displayResValue('Label.TotalTime'); ?></th>
			<th class="title" width="5%"><?php AriQuizWebHelper::displayResValue('Label.Score'); ?></th>
			<th class="title" width="5%"><?php AriQuizWebHelper::displayResValue('Label.Details'); ?></th>
		</tr>
	</thead>
	<?php
		if (!empty($resultList))
		{
			$i = 0;
			$name = !empty($resultList[0]->name) ? $resultList[0]->name : AriQuizWebHelper::getResValue('Label.Guest');
			foreach ($resultList as $result)
			{
	 ?>
	 <tr class="<?php echo 'row' . ($i % 2); ?>">
	 	<td align="center"><?php echo ($i + 1); ?></td>
    	<td align="center"><?php AriQuizWebHelper::displayDbValue($name); ?></td>
	 	<td align="left"><?php AriQuizWebHelper::displayDbValue($result->Question, false); ?></td>
	 	<td align="left"><?php AriQuizWebHelper::displayDbValue($result->CategoryName); ?></td>
	 	<td align="center" nowrap="nowrap"><?php echo $result->QuestionType; ?></td>
	 	<td align="center" nowrap="nowrap"><?php echo ArisDateDuration::toString(AriQuizWebHelper::getValue($result->TotalTime, 0), AriQuizWebHelper::getShortPeriods(), ' ', true); ?></td>
	 	<td align="center" nowrap="nowrap"><?php AriQuizWebHelper::showValue($result->Score, 0); ?> / <?php echo $result->MaxScore; ?></td>
	 	<td align="center" nowrap="nowrap"><a href="index.php?option=<?php echo $option; ?>&task=question&sid=<?php echo $result->StatisticsId; ?>"><?php AriQuizWebHelper::displayResValue('Label.View'); ?></a></td>
	 </tr>
	 <?php
	 			++$i;
			}
		} 
	 ?>
</table>
<script type="text/javascript">
<?php echo J16 ? 'Joomla.submitbutton' : 'submitbutton'; ?> = function(pressbutton)
	{
		if (pressbutton == 'result$res_template')
		{
			var ddlTemplate = document.getElementById('templateId');
			if (ddlTemplate && ddlTemplate.value)
			{
				window.open('index3.php?option=<?php echo $option; ?>&task=texttemplate_preview&sid=<?php echo $statisticsInfoId; ?>&templateId=' + ddlTemplate.value, 'blank');
			}
			return;
		}

		<?php echo J16 ? 'Joomla.submitform' : 'submitform'; ?>(pressbutton);
		if (pressbutton == 'result$tocsv' || pressbutton == 'result$toexcel' || pressbutton == 'result$tohtml' || pressbutton == 'result$toword')
		{
			document.getElementById('task').value = 'result';
		}
	}
</script>
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="quizId" value="<?php echo $quizId;?>" />
<input type="hidden" name="task" id="task" value="result" />
<input type="hidden" name="statisticsInfoId" value="<?php echo $statisticsInfoId; ?>" />
</form>
<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');

	$option = $processPage->getVar('option');
	$act = $processPage->getVar('act');
	$task = $processPage->getVar('task');
	$pageNav = $processPage->getVar('pageNav');
	$quizId = $processPage->getVar('quizId');
	$resultFilters = $processPage->getVar('resultFilters');
	$results = $processPage->getVar('results');
	
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/common.js');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminheading" style="width: 100%;">
	<tr>
		<td align="right" style="white-space: nowrap;">
			<?php AriQuizWebHelper::displayResValue('Label.Filter'); ?> : <?php echo $resultFilters->draw(); ?>
		</td>
	</tr>
</table>
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th class="title" width="20"><?php echo JText::_( 'Num' ); ?></th>
			<th class="title" width="20"><input type="checkbox" name="toggle" value="" onclick="<?php if (J3_0):?>Joomla.checkAll(this);<?php else: ?>checkAll(<?php echo count($results); ?>)<?php endif; ?>;"/></th>
			<th class="title"><?php echo str_replace('Joomla.tableOrdering', 'tableOrdering', JHTML::_('grid.sort', AriQuizWebHelper::getResValue('Label.User'), 'Name', AriQuizHelper::getSortDirection('Name'), AriQuizHelper::getSortField('Name'), 'results')); ?></th>
			<th class="title"><?php echo str_replace('Joomla.tableOrdering', 'tableOrdering', JHTML::_('grid.sort', AriQuizWebHelper::getResValue('Label.Quiz'), 'QuizName', AriQuizHelper::getSortDirection('QuizName'), AriQuizHelper::getSortField('Name'), 'results')); ?></th>
			<th class="title" width="5%"><?php echo str_replace('Joomla.tableOrdering', 'tableOrdering', JHTML::_('grid.sort', AriQuizWebHelper::getResValue('Label.StartDate'), 'StartDate', AriQuizHelper::getSortDirection('StartDate'), AriQuizHelper::getSortField('Name'), 'results')); ?></th>
			<th class="title" width="5%"><?php echo str_replace('Joomla.tableOrdering', 'tableOrdering', JHTML::_('grid.sort', AriQuizWebHelper::getResValue('Label.EndDate'), 'EndDate', AriQuizHelper::getSortDirection('EndDate'), AriQuizHelper::getSortField('Name'), 'results')); ?></th>
			<th class="title" width="5%"><?php AriQuizWebHelper::displayResValue('Label.Score'); ?></th>
			<th class="title" width="5%"><?php echo str_replace('Joomla.tableOrdering', 'tableOrdering', JHTML::_('grid.sort', AriQuizWebHelper::getResValue('Label.Passed'), 'Passed', AriQuizHelper::getSortDirection('Passed'), AriQuizHelper::getSortField('Name'), 'results')); ?></th>
			<th class="title" width="5%"><?php AriQuizWebHelper::displayResValue('Label.Details'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="9"><?php echo $pageNav->getListFooter(); ?></td>
		</tr>
	</tfoot>
	<tbody>
	<?php
		if (!empty($results))
		{
			$i = 0;
			$passedText = AriQuizWebHelper::getResValue('Label.Passed');
			$noPassedText = AriQuizWebHelper::getResValue('Label.NoPassed');
			$viewText = AriQuizWebHelper::getResValue('Label.View');
			$guestText = AriQuizWebHelper::getResValue('Label.Guest');
			foreach ($results as $row)
			{
	 ?>
	 <tr class="<?php echo 'row' . ($i % 2); ?>">
    	<td align="center"><?php echo $pageNav->getRowOffset($i); ?></td>
	 	<td align="center"><?php echo JHTML::_('grid.id', $i, $row->StatisticsInfoId, false, 'statisticsInfoId'); ?></td>
	 	<td align="left"><?php echo !empty($row->Name) ? $row->Name : $guestText; ?></td>
	 	<td align="left"><a href="index.php?option=<?php echo $option; ?>&task=quiz_add&quizId=<?php echo $row->QuizId; ?>"><?php AriQuizWebHelper::displayDbValue($row->QuizName); ?></a></td>
	 	<td align="center" nowrap="nowrap"><?php echo ArisDate::formatDate($row->StartDate); ?></td>
	 	<td align="center" nowrap="nowrap"><?php echo ArisDate::formatDate($row->EndDate); ?></td>
	 	<td align="center" nowrap="nowrap"><?php echo $row->UserScore . ' / ' . $row->MaxScore; ?></td>
	 	<td align="center" nowrap="nowrap"><?php echo $row->Passed ? $passedText : $noPassedText; ?></td>
	 	<td align="center" nowrap="nowrap"><a href="index.php?option=<?php echo $option; ?>&task=result&quizId=<?php echo $row->QuizId; ?>&statisticsInfoId=<?php echo $row->StatisticsInfoId; ?>"><?php echo $viewText; ?></a></td>
	 </tr>
	 <?php
		 		++$i;
			}
		} 
	 ?>
	 </tbody>
</table>

<input type="hidden" name="quizId" value="<?php echo $quizId;?>" />
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" id="task" value="results" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="oldTask" value="<?php echo $task; ?>" />
</form>
<script type="text/javascript">
	<?php echo J16 ? 'Joomla.submitbutton' : 'submitbutton'; ?> = function(pressbutton)
	{
		<?php echo J16 ? 'Joomla.submitform' : 'submitform'; ?>(pressbutton);

		if (pressbutton == 'results$tocsv' || pressbutton == 'results$toexcel' || pressbutton == 'results$tohtml' || pressbutton == 'results$toword')
		{
			document.getElementById('task').value = 'results';
		}
	}
</script>
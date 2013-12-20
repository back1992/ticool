<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$results = $processPage->getVar('results');
	$option =  $processPage->getVar('option');
	$Itemid = $processPage->getVar('Itemid');
?>

<div class="componentheading"><?php AriQuizWebHelper::displayResValue('Title.QuizResultList'); ?></div>
<table style="width: 100%;" class="ariStatTable">
	<tr>
		<td class="sectiontableheader"><?php AriQuizWebHelper::displayResValue('Label.NumberPos'); ?></td>
		<td class="sectiontableheader"><?php AriQuizWebHelper::displayResValue('Label.Quiz'); ?></td>
		<td class="sectiontableheader"><?php AriQuizWebHelper::displayResValue('Label.StartDate'); ?></td>
		<td class="sectiontableheader"><?php AriQuizWebHelper::displayResValue('Label.EndDate'); ?></td>
		<td class="sectiontableheader"><?php AriQuizWebHelper::displayResValue('Label.Score'); ?></td>
		<td class="sectiontableheader"><?php AriQuizWebHelper::displayResValue('Label.Passed'); ?></td>
		<td class="sectiontableheader"><?php AriQuizWebHelper::displayResValue('Label.Details'); ?></td>
	</tr>
	<?php
		if (!empty($results))
		{
			$i = 0;
			$lblView = AriQuizWebHelper::getResValue('Label.View');
			$lblPassed = AriQuizWebHelper::getResValue('Label.Passed');
			$lblNoPassed = AriQuizWebHelper::getResValue('Label.NoPassed');
			foreach ($results as $row)
			{
	 ?>
	 <tr class="<?php echo 'sectiontableentry' . ($i % 2); ?>">
    	<td align="left"><?php echo ($i + 1); ?></td>
	 	<td><?php AriQuizWebHelper::displayDbValue($row->QuizName); ?></td>
	 	<td><?php echo ArisDate::formatDate($row->StartDate); ?></td>
	 	<td><?php echo ArisDate::formatDate($row->EndDate); ?></td>
	 	<td><?php echo $row->UserScore . ' / ' . $row->MaxScore; ?></td>
	 	<td><?php echo $row->Passed ? $lblPassed : $lblNoPassed; ?></td>
	 	<td><a href="index.php?option=<?php echo $option; ?>&task=quiz_finished&ticketId=<?php echo $row->TicketId; ?>&Itemid1=<?php echo $Itemid; ?>"><?php echo $lblView; ?></a></td>
	 </tr>
	 <?php
		 		++$i;
			}
		} 
		else
		{
	 ?>
	 <tr>
	 	<td colspan="7" align="left"><?php AriQuizWebHelper::displayResValue('Label.NotItemsFound'); ?></td>
	 </tr>
	 <?php
		}
	 ?>
</table>
<br/>
<div style="text-align: center;">
Developed by <a href="http://www.ari-soft.com" target="_blank" title="ARI Soft">ARI Soft</a>.
</div>
<br/>
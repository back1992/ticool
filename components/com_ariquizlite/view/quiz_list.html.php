<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$quizList = $processPage->getVar('quizList');
	$arisI18N = $processPage->getVar('arisI18N');
	$option = $processPage->getVar('option');
	$Itemid = $processPage->getVar('Itemid');
?>

<?php
if (!empty($quizList))
{
?>
<table cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
<?php
	$prevCategoryId = -1;
	foreach ($quizList as $quiz)
	{
		if ($quiz->CategoryId != $prevCategoryId)
		{
?>
	<tr>
		<th class="sectiontableheader"><?php echo empty($quiz->CategoryId) ? AriQuizWebHelper::getResValue('Category.Uncategory') : $quiz->CategoryName; ?></th>
	</tr>
<?php
		}
?>
	<tr>
		<td><a href="index.php?option=<?php echo $option; ?>&task=quiz&quizId=<?php echo $quiz->QuizId; ?>&Itemid=<?php echo $Itemid; ?>"><?php AriQuizWebHelper::displayDbValue($quiz->QuizName); ?></a></td>
	</tr>
<?php		
		$prevCategoryId = $quiz->CategoryId;
	}
?>
</table>
<?php
}
else
{
	AriQuizWebHelper::displayResValue('Label.NotItemsFound');
}
?>
<br/>
<div style="text-align: center;">
Developed by <a href="http://www.ari-soft.com" target="_blank" title="ARI Soft">ARI Soft</a>.
</div>
<br/>
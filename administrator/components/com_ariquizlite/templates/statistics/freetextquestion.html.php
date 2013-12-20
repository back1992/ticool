<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
?>

<?php
	$data = $specificQuestion->getDataFromXml($statistics->Question->QuestionVersion->Data);
	$xData = $specificQuestion->getDataFromXml($statistics->Data);
	$userAnswer = !empty($xData) && count($xData) > 0 ? $xData[0]['tbxAnswer'] : '';	
?>
<table style="width: 100%;" cellpadding="0" cellspacing="0" class="adminlist">
	<tr>
		<th style="width: 1%; text-align: center;"><?php AriQuizWebHelper::displayResValue('Label.Correct'); ?></th>
		<th style="text-align: left;"><?php AriQuizWebHelper::displayResValue('Label.User'); ?></th>
	</tr>
	<tr>
		<td align="center"><img src="components/com_ariquizlite/images/<?php echo $statistics->Score == $statistics->Question->QuestionVersion->Score ? 'tick.png' : 'publish_x.png'; ?>" border="0" /></td>
		<td><?php echo stripslashes($userAnswer); ?></td>
	</tr>
</table>
<br/>
<table style="width: 100%;" cellpadding="0" cellspacing="0" class="adminlist table table-striped">
	<tr>
		<th class="title" style="width: 1%; text-align: center;"><?php AriQuizWebHelper::displayResValue('Label.NumberPos'); ?></th>
		<th class="title" style="width: 1%; white-space: nowrap;"><?php AriQuizWebHelper::displayResValue('Label.TextCI'); ?></th>
		<th class="title" style="text-align: left;"><?php AriQuizWebHelper::displayResValue('Label.CorrectAnswers'); ?></th>
	</tr>
<?php
	if (!empty($data))
	{
		$i = 0;
		foreach ($data as $dataItem)
		{
?>
	<tr>
		<td><?php echo (++$i); ?>.&nbsp;</td>
		<td align="center"><img src="components/com_ariquizlite/images/<?php echo !empty($dataItem['cbCI']) ? 'tick.png' : 'publish_x.png'; ?>" border="0" /></td>
		<td><?php echo $dataItem['tbxAnswer']; ?></td>
	</tr>
<?php
		}
	}
?>
</table>
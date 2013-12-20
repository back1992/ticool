<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
?>

<?php
	$data = $specificQuestion->getDataFromXml($statistics->Question->QuestionVersion->Data);
	$xData = $specificQuestion->getDataFromXml($statistics->Data);
	$selId = !empty($xData) && count($xData) > 0 ? $xData[0]['hidQueId'] : null;
?>

<table style="width: 100%;" cellpadding="0" cellspacing="0" class="adminlist table table-striped">
	<tr>
		<th class="title" style="width: 1%; text-align: center;"><?php AriQuizWebHelper::displayResValue('Label.NumberPos'); ?></th>
		<th class="title" style="width: 5%;text-align: center;"><?php AriQuizWebHelper::displayResValue('Label.User'); ?></th>
		<th class="title" style="width: 5%; text-align: center;"><?php AriQuizWebHelper::displayResValue('Label.Correct'); ?></th>
		<th class="title" style="text-align: left;"><?php AriQuizWebHelper::displayResValue('Label.Answer'); ?></th>
	</tr>
<?php
$i = 0;
foreach ($data as $dataItem)
{
?>
	<tr>
		<td><?php echo (++$i); ?>.</td>
		<td align="center"><?php if ($dataItem['hidQueId'] == $selId) { ?><img src="components/com_ariquizlite/images/tick.png" border="0" /><?php }; ?></td>
		<td align="center"><?php if (!empty($dataItem['hidCorrect'])) { ?><img src="components/com_ariquizlite/images/tick.png" border="0" /><?php }; ?></td>
		<td><?php echo $dataItem['tbxAnswer']; ?></td>
	</tr>
<?php
}
?>
</table>
<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$templateList = $processPage->getVar('templateList');
	$option = $processPage->getVar('option');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th class="title" width="20"><?php echo JText::_( 'Num' ); ?></th>
			<th class="title" width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($templateList); ?>);"/></th>
			<th class="title"><?php AriQuizWebHelper::displayResValue('Label.Name'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	if (!empty($templateList)):
		$i = 0;
		foreach ($templateList as $template):
	?>
		<tr>
			<td align="center"><?php echo ($i + 1); ?></td>
			<td align="center"><?php echo JHTML::_('grid.id', $i, $template->FileId, false, 'fileId'); ?></td>
			<td align="left"><a href="index.php?option=<?php echo $option; ?>&hidemainmenu=1&task=template_add&fileId=<?php echo $template->FileId; ?>"><?php AriQuizWebHelper::displayDbValue($template->ShortDescription); ?></a></td>
		</tr>
	<?php
			++$i;
		endforeach;
	endif;
	?>
	</tbody>
</table>
<input type="hidden" name="hidemainmenu" value="0" />
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="templates" />
<input type="hidden" name="boxchecked" value="0" />
</form>
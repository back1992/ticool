<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$option = $processPage->getVar('option');
	$task = $processPage->getVar('task');
	$templateList = $processPage->getVar('templateList');
	
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/common.js');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th class="title" width="20"><?php AriQuizWebHelper::displayResValue('Label.NumberPos'); ?></th>
			<th class="title" width="20"><input type="checkbox" name="toggle" value="" onclick="<?php if (J3_0):?>Joomla.checkAll(this);<?php else: ?>checkAll(<?php echo count($templateList); ?>)<?php endif; ?>;"/></th>
			<th class="title"><?php echo str_replace('Joomla.tableOrdering', 'tableOrdering', JHTML::_('grid.sort', AriQuizWebHelper::getResValue('Label.Template'), 'TemplateName', AriQuizHelper::getSortDirection('TemplateName'), AriQuizHelper::getSortField('TemplateName'), $task)); ?></th>
			<th class="title" nowrap="nowrap" width="15%"><?php echo str_replace('Joomla.tableOrdering', 'tableOrdering', JHTML::_('grid.sort', AriQuizWebHelper::getResValue('Label.QuestionType'), 'QuestionType', AriQuizHelper::getSortDirection('QuestionType'), AriQuizHelper::getSortField('TemplateName'), $task)); ?></th>
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
			<td align="center"><?php echo JHTML::_('grid.id', $i, $template->TemplateId, false, 'templateId'); ?></td>
			<td align="left"><a href="index.php?option=<?php echo $option; ?>&hidemainmenu=1&task=qtemplate_add&templateId=<?php echo $template->TemplateId ?>"><?php AriQuizWebHelper::displayDbValue($template->TemplateName); ?></a></td>
			<td align="center"><?php echo $template->QuestionType; ?></td>
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
<input type="hidden" name="task" value="qtemplate_list" />
<input type="hidden" name="boxchecked" value="0" />
</form>
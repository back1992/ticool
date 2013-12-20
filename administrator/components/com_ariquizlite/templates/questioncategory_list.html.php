<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$option = $processPage->getVar('option');
	$categoryList = $processPage->getVar('categoryList');
	$pageNav = $processPage->getVar('pageNav');
	$task = $processPage->getVar('task');
	$quizId = $processPage->getVar('quizId');
	
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/common.js');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th class="title" width="20"><?php echo JText::_( 'Num' ); ?></th>
			<th class="title" width="20"><input type="checkbox" name="toggle" value="" onclick="<?php if (J3_0):?>Joomla.checkAll(this);<?php else: ?>checkAll(<?php echo count($categoryList); ?>)<?php endif; ?>;"/></th>
			<th class="title"><?php echo str_replace('Joomla.tableOrdering', 'tableOrdering', JHTML::_('grid.sort', AriQuizWebHelper::getResValue('Label.Category'), 'CategoryName', AriQuizHelper::getSortDirection('CategoryName'), AriQuizHelper::getSortField('CategoryName'), 'questioncategory_list')); ?></th>
			<th class="title"><?php echo str_replace('Joomla.tableOrdering', 'tableOrdering', JHTML::_('grid.sort', AriQuizWebHelper::getResValue('Label.Quiz'), 'QuizName', AriQuizHelper::getSortDirection('QuizName'), AriQuizHelper::getSortField('CategoryName'), 'questioncategory_list')); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="4">
				<?php echo $pageNav->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php
		if (!empty($categoryList)):
			$i = 0;
			foreach ($categoryList as $row):
	 ?>
		 <tr class="<?php echo 'row' . ($i % 2); ?>">
	    	<td align="center"><?php echo $pageNav->getRowOffset($i); ?></td>
		 	<td align="center"><?php echo JHTML::_('grid.id', $i, $row->QuestionCategoryId, false, 'questionCategoryId'); ?></td>
		 	<td align="left">
				<a href="index.php?option=<?php echo $option; ?>&hidemainmenu=1&task=questioncategory_add&qCategoryId=<?php echo $row->QuestionCategoryId; ?>"><?php AriQuizWebHelper::displayDbValue($row->CategoryName, true); ?></a>
		 	</td>
		 	<td align="left"><a href="index.php?option=<?php echo $option; ?>&hidemainmenu=1&task=quiz_add&quizId=<?php echo $row->QuizId; ?>"><?php AriQuizWebHelper::displayDbValue($row->QuizName, true); ?></a></td>
		 </tr>
	 <?php
	 			++$i;
			endforeach;
		endif; 
	 ?>
	 </tbody>
</table>
<script type="text/javascript">
<?php echo J16 ? 'Joomla.submitbutton' : 'submitbutton'; ?> = function(pressbutton)
	{
		if (pressbutton == 'questioncategory_list$delete')
		{
			var isDeleteQuestions = confirm('<?php AriQuizWebHelper::displayResValue('Warning.DeleteQueFromQCategory'); ?>');
			document.getElementById('zq_deleteQuestions').value = isDeleteQuestions ? '1' : '0';
		}
		
		<?php echo J16 ? 'Joomla.submitform' : 'submitform'; ?>(pressbutton);
	}
</script>
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" id="task" value="questioncategory_list" />
<input type="hidden" name="quizId" id="quizId" value="<?php echo $quizId; ?>" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="hidemainmenu" value="0" />
<input type="hidden" id="zq_deleteQuestions" name="zq_deleteQuestions" value="0" />
<input type="hidden" name="oldTask" value="<?php echo $task; ?>" />
</form>
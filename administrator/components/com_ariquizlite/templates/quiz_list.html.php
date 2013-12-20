<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$option = $processPage->getVar('option');
	$quizList = $processPage->getVar('quizList');
	$quizFilters = $processPage->getVar('quizFilters');
	$pageNav = $processPage->getVar('pageNav');
	$task = $processPage->getVar('task');
	
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/common.js');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminheading" style="width: 100%;">
	<tr>
		<td align="right" style="white-space: nowrap;">
			<?php AriQuizWebHelper::displayResValue('Label.Filter'); ?>: <?php echo $quizFilters->draw(); ?>
		</td>
	</tr>
</table>
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th class="title" width="20"><?php echo JText::_( 'Num' ); ?></th>
			<th class="title" width="20"><input type="checkbox" name="toggle" value="" onclick="<?php if (J3_0):?>Joomla.checkAll(this);<?php else: ?>checkAll(<?php echo count($quizList); ?>)<?php endif; ?>;"/></th>
			<th class="title" width="20" nowrap="nowrap">ID</th>
			<th class="title"><?php echo str_replace('Joomla.tableOrdering', 'tableOrdering', JHTML::_('grid.sort', AriQuizWebHelper::getResValue('Label.Name'), 'QuizName', AriQuizHelper::getSortDirection('QuizName'), AriQuizHelper::getSortField('QuizName'), 'quiz_list')); ?></th>
			<th class="title"><?php echo str_replace('Joomla.tableOrdering', 'tableOrdering', JHTML::_('grid.sort', AriQuizWebHelper::getResValue('Label.Category'), 'CategoryName', AriQuizHelper::getSortDirection('CategoryName'), AriQuizHelper::getSortField('QuizName'), 'quiz_list')); ?></th>		
			<th class="title" width="5%"><?php echo str_replace('Joomla.tableOrdering', 'tableOrdering', JHTML::_('grid.sort', AriQuizWebHelper::getResValue('Label.Status'), 'Status', AriQuizHelper::getSortDirection('Status'), AriQuizHelper::getSortField('QuizName'), 'quiz_list')); ?></th>
			<th class="title" width="10%" nowrap="nowrap"><?php AriQuizWebHelper::displayResValue('Label.Questions'); ?></th>
			<th class="title" width="10%" nowrap="nowrap"><?php AriQuizWebHelper::displayResValue('Label.Results'); ?></th>
			<th class="title" width="10%" nowrap="nowrap"><?php AriQuizWebHelper::displayResValue('Label.QCategories'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="9">
				<?php echo $pageNav->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php
		if (!empty($quizList)):
			$i = 0;
			foreach ($quizList as $row):
	 ?>
		 <tr class="<?php echo 'row' . ($i % 2); ?>">
	    	<td align="center"><?php echo $pageNav->getRowOffset($i); ?></td>
		 	<td align="center"><?php echo JHTML::_('grid.id', $i, $row->QuizId, false, 'quizId'); ?></td>
		 	<td align="center"><?php echo $row->QuizId; ?></td>
		 	<td align="left">
				<a href="index.php?option=<?php echo $option; ?>&hidemainmenu=1&task=quiz_add&quizId=<?php echo $row->QuizId; ?>"><?php AriQuizWebHelper::displayDbValue($row->QuizName); ?></a>
		 	</td>
		 	<td align="left"><?php AriQuizWebHelper::displayDbValue($row->CategoryName); ?></td>
		 	<td align="center">
		 	<?php
		 		$resKey = '';
		 		$img = '';
		 		$sTask = '';
		 		if ($row->Status == ARI_QUIZ_STATUS_ACTIVE)
		 		{
		 			$resKey = 'Label.Active';
		 			$img = 'tick.png';
		 			$sTask = 'quiz_list$deactivate';
		 		}
		 		else if ($row->Status == ARI_QUIZ_STATUS_INACTIVE)
		 		{
		 			$resKey = 'Label.Inactive';
		 			$img = 'publish_x.png';
		 			$sTask = 'quiz_list$activate';
		 		}
		 	?>
		 		<a href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i; ?>', '<?php echo $sTask; ?>');"><img src="components/com_ariquizlite/images/<?php echo $img; ?>" border="0" /></a>
		 	</td>
		 	<td align="center"><a href="index.php?option=<?php echo $option; ?>&task=question_list&quizId=<?php echo $row->QuizId ?>"><?php AriQuizWebHelper::displayResValue('Label.View'); ?></a></td>
		 	<td align="center"><a href="index.php?option=<?php echo $option; ?>&task=results&quizId=<?php echo $row->QuizId; ?>"><?php AriQuizWebHelper::displayResValue('Label.View'); ?></a></td>
		 	<td align="center"><a href="index.php?option=<?php echo $option; ?>&task=questioncategory_list&quizId=<?php echo $row->QuizId; ?>"><?php AriQuizWebHelper::displayResValue('Label.View'); ?></a></td>
		 </tr>
	 <?php
	 			++$i;
			endforeach;
		endif; 
	 ?>
	 </tbody>
</table>
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="quiz_list" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="hidemainmenu" value="0" />
<input type="hidden" name="oldTask" value="<?php echo $task; ?>" />
</form>
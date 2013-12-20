<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$option = $processPage->getVar('option');
	$task = $processPage->getVar('task');
	$pageNav = $processPage->getVar('pageNav');
	$quizId = $processPage->getVar('quizId');
	$questionList = $processPage->getVar('questionList');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th class="title" width="20"><?php echo JText::_( 'Num' ); ?></th>
			<th class="title" width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($questionList); ?>);"/></th>
			<th class="title"><?php AriQuizWebHelper::displayResValue('Label.Question'); ?></th>
			<th class="title"><?php AriQuizWebHelper::displayResValue('Label.Category'); ?></th>
			<th class="title" width="5%" nowrap="nowrap"><?php AriQuizWebHelper::displayResValue('Label.QuestionType'); ?></th>
			<th class="title" colspan="2" width="1%" nowrap="nowrap"><?php AriQuizWebHelper::displayResValue('Label.Reorder'); ?></th>
			<th class="title" width="5%"><?php AriQuizWebHelper::displayResValue('Label.Quiz'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="8"><?php echo $pageNav->getListFooter(); ?></td>
		</tr>
	</tfoot>
	<tbody>
	<?php
		if (!empty($questionList)):
			$i = 0;
			$queCount = count($questionList);
			foreach ($questionList as $row):
				$question = strip_tags($row->Question);
				if (AriText::htmlStrLen($question) > 50)
				{
					$question = AriText::htmlSubStr($question, 0, 50) . '...';
				}
	 ?>
		 <tr class="<?php echo 'row' . ($i % 2); ?>">
	    	<td align="center"><?php echo $pageNav->getRowOffset($i); ?></td>
		 	<td align="center"><?php echo JHTML::_('grid.id', $i, $row->QuestionId, false, 'questionId'); ?></td>
		 	<td align="left">
		 		<a href="index.php?option=<?php echo $option; ?>&hidemainmenu=1&task=question_add&questionId=<?php echo $row->QuestionId; ?>">
		 			<?php echo $question; ?>
		 		</a>
		 	</td>
		 	<td align="left">
				<?php AriQuizWebHelper::displayDbValue($row->CategoryName); ?>
		 	</td>
		 	<td align="center" nowrap="nowrap">
		 		<?php AriQuizWebHelper::displayDbValue($row->QuestionType); ?>
		 	</td>
		 	<td align="center" nowrap="nowrap" class="order">
		 		<?php echo $pageNav->orderUpIcon($i, true, 'question_list$orderup'); ?>
		 	</td>
		 	<td align="center" nowrap="nowrap" class="order">
		 		<?php echo $pageNav->orderDownIcon($i, $queCount, true, 'question_list$orderdown'); ?>
		 	</td>
		 	<td><?php AriQuizWebHelper::displayDbValue($row->QuizName); ?></td>
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
<input type="hidden" name="task" value="question_list" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="quizId" value="<?php echo $quizId; ?>" />
<input type="hidden" name="oldTask" value="<?php echo $task; ?>" />
</form>
<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$option = $processPage->getVar('option');
	$task = $processPage->getVar('task');
	$qCategoryId = $processPage->getVar('qCategoryId');
	$quizId = $processPage->getVar('quizId');
	$isUpdate = $processPage->getVar('isUpdate');
	$quizList = $processPage->getVar('quizList');
	$category = $processPage->getVar('category');
	
	jimport( 'joomla.html.editor' );
	$editor = &JFactory::getEditor();
	
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/yahoo-dom-event.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/validators.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/ari.dom.js');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminform" style="width: 100%;">
	<tbody>
		<tr>
			<th colspan="2"><?php AriQuizWebHelper::displayResValue('Label.MainSettings'); ?></th>
		</tr>
	</tbody>
	<tbody id="tbCategorySettings">
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Quiz'); ?> :</td>
			<td align="left">
			<?php
				if (!$isUpdate)
				{
					echo JHTML::_('select.genericlist', $quizList, 'quizId', 'id="ddlCategory" class="text_area"', 'QuizId', 'QuizName', $quizId); 
				}
				else 
				{
					AriQuizWebHelper::displayDbValue($category->Quiz->QuizName);
				} 
			?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Name'); ?> :</td>
			<td align="left"><input type="text" class="text_area" id="tbxCategoryName" name="zCategory[CategoryName]" size="70" maxlength="200" value="<?php AriQuizWebHelper::displayDbValue($category->CategoryName); ?>"></td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.QuestionCount'); ?> :</td>
			<td align="left"><input type="textbox" class="text_area" name="zCategory[QuestionCount]" id="tbxQuestionCount" value="<?php echo $category->QuestionCount; ?>"></td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.QuestionTime'); ?> :</td>
			<td align="left"><input type="textbox" class="text_area" name="zCategory[QuestionTime]" id="tbxQuestionTime" value="<?php echo $category->QuestionTime; ?>"></td>
		</tr>
		<tr valign="top">
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Description'); ?> :</td>
			<td align="left">
			<?php
				echo $editor->display('zCategory[Description]', $category->Description, '100%;', '250', '60', '20' ) ; 
			?>
			</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
	aris.validators.validatorManager.addValidator(
		new aris.validators.requiredValidator('tbxCategoryName',
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.NameRequired'); ?>'}));
	aris.validators.validatorManager.addValidator(
		new aris.validators.rangeValidator('tbxQuestionCount', 0, null, aris.validators.rangeValidatorType.int, 
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.QuestionCount'); ?>'}));
			
	aris.validators.validatorManager.addValidator(
		new aris.validators.rangeValidator('tbxQuestionTime', 0, null, aris.validators.rangeValidatorType.int, 
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.QuestionTime'); ?>'}));

	<?php echo J16 ? 'Joomla.submitbutton' : 'submitbutton'; ?> = function(pressbutton)
	{
		if (pressbutton == 'questioncategory_add$save' || pressbutton == 'questioncategory_add$apply')
		{
			if (!aris.validators.alertSummaryValidators.validate())
			{
				return;
			}
		}
		
		<?php echo J16 ? 'Joomla.submitform' : 'submitform'; ?>(pressbutton);
	}
</script>
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="questioncategory_add$save" />
<input type="hidden" name="qCategoryId" value="<?php echo $qCategoryId; ?>" />
<?php
	if ($isUpdate)
	{
?>
<input type="hidden" name="quizId" value="<?php echo $quizId; ?>" />
<?php
	}
 ?>
</form>
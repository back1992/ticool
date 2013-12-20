<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$option = $processPage->getVar('option');
	$task = $processPage->getVar('task');
	$categoryList = $processPage->getVar('categoryList');
	$templateList = $processPage->getVar('templateList');
	$quiz = $processPage->getVar('quiz');
	$quizId = $processPage->getVar('quizId');
	$question = $processPage->getVar('question');
	$questionId = $processPage->getVar('questionId');
	$questionData = $processPage->getVar('questionData');
	$className = $processPage->getVar('className');
	$specificQuestion = $processPage->getVar('specificQuestion');
	$questionTypeId = $processPage->getVar('questionTypeId');
	$questionTypeList = $processPage->getVar('questionTypeList');
	
	jimport( 'joomla.html.editor' );
	$editor = &JFactory::getEditor();
	
		
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/yahoo-dom-event.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/validators.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/ari.dom.js');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<table class="adminform" style="width: 100%;">
	<tbody>
		<tr>
			<th colspan="2"><?php AriQuizWebHelper::displayResValue('Label.MainSettings'); ?></th>
		</tr>
	</tbody>
	<tbody id="tbQuestionSettings">
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Quiz'); ?> :</td>
			<td align="left"><?php AriQuizWebHelper::displayDbValue($quiz->QuizName); ?></td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Category'); ?> :</td>
			<td align="left">
				<?php
					if (empty($categoryList))
					{
						AriQuizWebHelper::displayResValue('Label.NotSelectedItem'); 
					}
					else
					{
						echo JHTML::_('select.genericlist', $categoryList, 'zQuiz[QuestionCategoryId]', 'id="ddlCategory" class="text_area"', 'QuestionCategoryId', 'CategoryName', $question != null ? $question->QuestionVersion->QuestionCategoryId : null);
					}
				?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Title.QuestionTemplate'); ?> :</td>
			<td align="left">
				<?php
					if (empty($templateList))
					{
						AriQuizWebHelper::displayResValue('Label.NotSelectedItem'); 
					}
					else
					{
						echo JHTML::_('select.genericlist', $templateList, 'templateId', 'id="ddlTemplate" class="text_area" onchange="submitbutton(\'question_add\')"', 'TemplateId', 'TemplateName', null);
					}
				?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.QuestionType'); ?> :</td>
			<td align="left">
				<?php echo JHTML::_('select.genericlist', $questionTypeList, 'questionTypeId', ' class="text_area" onchange="submitbutton(\'question_add$apply_qtype\')"', 'QuestionTypeId', 'QuestionType', $questionTypeId); ?>
			</td>
		</tr> 
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Score'); ?> :</td>
			<td align="left"><input type="text" id="tbxScore" class="text_area" name="zQuiz[Score]" value="<?php echo $question->QuestionVersion->Score; ?>" /></td>
		</tr>
		<tr valign="top">
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Question'); ?> :</td>
			<td align="left">
			<?php
				echo $editor->display('zQuiz[Question]', $question->QuestionVersion->Question, '100%;', '250', '40', '20' ) ; 
			?>
			</td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<th colspan="2"><?php AriQuizWebHelper::displayResValue('Label.AdditionalSettings'); ?></th>
		</tr>
	</tbody>
	<tbody id="tbQueDetailsSettings">
		<tr>
			<td colspan="2" align="left">
				<?php
					$path = AriQuizHelper::getQuestionTemplatePath($className);
					if (!empty($path)) require_once($path);
				?>
			</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
	aris.validators.validatorManager.addValidator(
		new aris.validators.customValidator('zQuiz[Question]',
			function(val)
			{
				var isValid = true;
				<?php echo $editor->save('zQuiz[Question]') ; ?>
				var value = val.getValue();
				isValid = (value && value.replace(/^\s+|\s+$/g, '').length > 0);

				return isValid;
			},
			{emptyValidate : true, errorMessage : "<?php AriQuizWebHelper::displayResValue('Validator.QuestionRequired'); ?>"}));
			
	aris.validators.validatorManager.addValidator(
		new aris.validators.requiredValidator('tbxScore',
			{errorMessage : "<?php AriQuizWebHelper::displayResValue('Validator.QuestionScoreRequired'); ?>"}));
			
	aris.validators.validatorManager.addValidator(
		new aris.validators.rangeValidator('tbxScore', 0, null, aris.validators.rangeValidatorType.int,
			{errorMessage : "<?php AriQuizWebHelper::displayResValue('Validator.QuestionScore'); ?>"}));

	<?php echo J16 ? 'Joomla.submitbutton' : 'submitbutton'; ?> = function(pressbutton)
	{
		if (pressbutton == 'question_add$save' || pressbutton == 'question_add$apply')
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
<input type="hidden" name="task" value="question_add" />
<input type="hidden" name="quizId" value="<?php echo $quizId; ?>" />
<input type="hidden" name="questionId" value="<?php echo $questionId; ?>" />
</form>
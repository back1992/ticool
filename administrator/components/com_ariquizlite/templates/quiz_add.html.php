<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');

	$option = $processPage->getVar('option');
	$act = $processPage->getVar('act');
	$categoryList = $processPage->getVar('categoryList');
	$quiz = $processPage->getVar('quiz');
	$quizId = $processPage->getVar('quizId');
	$task = $processPage->getVar('task');
	$groupsTree = $processPage->getVar('groupsTree');
	$textTemplateList = $processPage->getVar('textTemplateList');
	$quizTextTemplates = $processPage->getVar('quizTextTemplates');
	$cssTemplates = $processPage->getVar('cssTemplates');

	jimport( 'joomla.html.editor' );
	$editor = &JFactory::getEditor();
	
		
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/yahoo-dom-event.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/validators.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/ari.dom.js');
?>

<?php JHTML::_('behavior.tooltip'); ?>
<table class="adminheading">
	<tr>
		<th><?php AriQuizWebHelper::displayResValue('Label.Quiz'); ?>: <?php AriQuizWebHelper::displayResValue($quizId ? 'Label.UpdateItem' : 'Label.AddItem'); ?></th>
	</tr>
</table>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminform" style="width: 100%;">
	<tbody>
		<tr>
			<th colspan="2"><?php AriQuizWebHelper::displayResValue('Label.MainSettings'); ?></th>
		</tr>
	</tbody>
	<tbody id="tbQuizSettings">
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Name'); ?> :</td>
			<td align="left"><input type="text" class="text_area" id="tbxQuizName" name="zQuiz[QuizName]" size="70" maxlength="200" value="<?php AriQuizWebHelper::displayDbValue($quiz->QuizName); ?>"></td>
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
						echo JHTML::_('select.genericlist', $categoryList, 'Category[]', 'id="ddlCategory" class="text_area"', 'CategoryId', 'CategoryName', !empty($quiz->CategoryList) && count($quiz->CategoryList) > 0 ? $quiz->CategoryList[0]->CategoryId : null);
					}
				?>
			</td>
		</tr>
		<tr valign="top">
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Access'); ?> :</td>
			<td align="left">
				<?php echo JHTML::_('select.genericlist', $groupsTree, 'AccessGroup[]', 'size="5"  class="text_area"', 'value', 'text', !empty($quiz->AccessList) ? $quiz->AccessList : 0); ?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Active'); ?> :</td>
			<td align="left"><input type="checkbox" name="zQuiz[Active]" <?php if ($quiz->Status & ARI_QUIZ_STATUS_ACTIVE > 0) echo 'checked'; ?> value="1"></td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.TotalTime'); ?> :</td>
			<td align="left"><input type="text" class="text_area" name="zQuiz[TotalTime]" id="tbxTotalTime" value="<?php echo $quiz->TotalTime; ?>"></td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.PassedScore'); ?> :</td>
			<td align="left"><input type="text" class="text_area" name="zQuiz[PassedScore]" id="tbxPassedScore" value="<?php echo $quiz->PassedScore; ?>"></td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.QuestionCount'); ?> :</td>
			<td align="left"><input type="text" class="text_area" name="zQuiz[QuestionCount]" id="tbxQuestionCount" value="<?php echo $quiz->QuestionCount; ?>"></td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.QuestionTime'); ?> :</td>
			<td align="left"><input type="text" class="text_area" name="zQuiz[QuestionTime]" id="tbxQuestionTime" value="<?php echo $quiz->QuestionTime; ?>"></td>
		</tr>
		<tr valign="top">
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Description'); ?> :</td>
			<td align="left">
				<?php
					echo $editor->display('zQuiz[Description]', $quiz->Description, '100%;', '250', '60', '20' ) ; 
				?>
			</td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<th colspan="2"><?php AriQuizWebHelper::displayResValue('Label.TextTemplates'); ?></th>
		</tr>
	</tbody>
	<tbody id="tbQuizTemplate">
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.SucEmailTemplate'); ?> :</td>
			<td align="left">
				<?php
					echo JHTML::_('select.genericlist', $textTemplateList, 'zTextTemplate[' . ARI_QUIZ_TT_SUCCESSFULEMAIL . ']', 'id="ddlSuccessfulEmailTemplate" class="text_area"', 'TemplateId', 'TemplateName', JArrayHelper::getValue($quizTextTemplates, ARI_QUIZ_TT_SUCCESSFULEMAIL, 0, ''));
				?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.FailedEmailTemplate'); ?> :</td>
			<td align="left">
				<?php
					echo JHTML::_('select.genericlist', $textTemplateList, 'zTextTemplate[' . ARI_QUIZ_TT_FAILEDEMAIL . ']', 'id="ddlFailedEmailTemplate" class="text_area"', 'TemplateId', 'TemplateName', JArrayHelper::getValue($quizTextTemplates, ARI_QUIZ_TT_FAILEDEMAIL, 0, ''));
				?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.SucPrintTemplate'); ?> :</td>
			<td align="left">
				<?php
					echo JHTML::_('select.genericlist', $textTemplateList, 'zTextTemplate[' . ARI_QUIZ_TT_SUCCESSFULPRINT . ']', 'id="ddlSuccessfulPrintTemplate" class="text_area"', 'TemplateId', 'TemplateName', JArrayHelper::getValue($quizTextTemplates, ARI_QUIZ_TT_SUCCESSFULPRINT, 0, ''));
				?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.FailedPrintTemplate'); ?> :</td>
			<td align="left">
				<?php
					echo JHTML::_('select.genericlist', $textTemplateList, 'zTextTemplate[' . ARI_QUIZ_TT_FAILEDPRINT . ']', 'id="ddlFailedPrintTemplate" class="text_area"', 'TemplateId', 'TemplateName', JArrayHelper::getValue($quizTextTemplates, ARI_QUIZ_TT_FAILEDPRINT, 0, ''));
				?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.SucTemplate'); ?> :</td>
			<td align="left">
				<?php
					echo JHTML::_('select.genericlist', $textTemplateList, 'zTextTemplate[' . ARI_QUIZ_TT_SUCCESSFUL . ']', 'id="ddlSuccessfulTemplate" class="text_area"', 'TemplateId', 'TemplateName', JArrayHelper::getValue($quizTextTemplates, ARI_QUIZ_TT_SUCCESSFUL, 0, ''));
				?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.FailedTemplate'); ?> :</td>
			<td align="left">
				<?php
					echo JHTML::_('select.genericlist', $textTemplateList, 'zTextTemplate[' . ARI_QUIZ_TT_FAILED . ']', 'id="ddlFailedTemplate" class="text_area"', 'TemplateId', 'TemplateName', JArrayHelper::getValue($quizTextTemplates, ARI_QUIZ_TT_FAILED, 0, ''));
				?>
			</td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<th colspan="2"><?php AriQuizWebHelper::displayResValue('Label.AdditionalSettings'); ?></th>
		</tr>
	</tbody>
	<tbody id="tbExtraQuizSettings">
		<tr valign="top">
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.SendResultTo'); ?> :</td>
			<td>
				<table width="100%" cellpadding="1" cellspacing="1">
					<tr>
						<td style="width: 1%; white-space: nowrap;">
							<?php AriQuizWebHelper::displayResValue('Label.Email'); ?> :
						</td>
						<td>
							<input type="text" class="text_area" size="100" name="zQuiz[AdminEmail]" id="tbxAdminEmail" value="<?php echo $quiz->AdminEmail; ?>"/>
						</td>
					</tr>
					<tr>
						<td style="width: 1%; white-space: nowrap;">
							<?php AriQuizWebHelper::displayResValue('Label.Template'); ?> :
						</td>
						<td>
							<?php
								echo JHTML::_('select.genericlist', $textTemplateList, 'zTextTemplate[' . ARI_QUIZ_ADMIN_MAIL . ']', 'id="ddlAdminEmailTemplate" class="text_area"', 'TemplateId', 'TemplateName', JArrayHelper::getValue($quizTextTemplates, ARI_QUIZ_ADMIN_MAIL, 0, ''));
							?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Template'); ?> :</td>
			<td>
				<?php
					echo JHTML::_('select.genericlist', $cssTemplates, 'zQuiz[CssTemplateId]', 'id="ddlCssTemplate" class="text_area"', 'FileId', 'ShortDescription', $quiz->CssTemplateId);
				?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Skip'); ?> :</td>
			<td align="left"><input type="checkbox" name="zQuiz[CanSkip]" <?php if ($quiz->CanSkip > 0) echo 'checked'; ?> value="1" />
				<?php echo JHTML::_('tooltip', AriQuizWebHelper::getResValue('Tooltip.QuizCanSkip'), AriQuizWebHelper::getResValue('Label.Tooltip')); ?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.RandomQuestion'); ?> :</td>
			<td align="left"><input type="checkbox" name="zQuiz[RandomQuestion]" <?php if ($quiz->RandomQuestion > 0) echo 'checked'; ?> value="1" />
				<?php echo JHTML::_('tooltip', AriQuizWebHelper::getResValue('Tooltip.QuizRandomQuestion'), AriQuizWebHelper::getResValue('Label.Tooltip')); ?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.LagTime'); ?> :</td>
			<td align="left"><input type="text" class="text_area" name="zQuiz[LagTime]" id="tbxLagTime" value="<?php echo $quiz->LagTime; ?>"></td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.AttemptCount'); ?> :</td>
			<td align="left"><input type="text" class="text_area" name="zQuiz[AttemptCount]" id="tbxAttemptCount" value="<?php echo $quiz->AttemptCount; ?>"></td>
		</tr>
	</tbody>
</table>
<input type="hidden" name="quizId" value="<?php echo $quizId; ?>" />
<script type="text/javascript">
	aris.validators.validatorManager.addValidator(
		new aris.validators.requiredValidator('tbxQuizName',
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.NameRequired'); ?>'}));

	aris.validators.validatorManager.addValidator(
		new aris.validators.customValidator('tbxQuizName',
			function(val)
			{
				var isValid = true;
				if (typeof(Ajax) != "undefined")
					new Ajax('index.php?option=<?php echo $option; ?>&task=ajax.checkQuizName&quizId=<?php echo $quizId; ?>&name=' + encodeURIComponent(val.getValue()), 
						{
							async : false,
							onSuccess: function(response) 
							{
								isValid = (response == 'true');
							}
						}).request();

				return isValid;
			},
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.NameNotUnique'); ?>'}));

	aris.validators.validatorManager.addValidator(
		new aris.validators.regexpValidator('tbxAdminEmail', /^(([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5}){1,25})+([;.](([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5}){1,25})+)*$/gi, 
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.EmailIncorrect'); ?>'}));

	aris.validators.validatorManager.addValidator(
		new aris.validators.rangeValidator('tbxTotalTime', 0, null, aris.validators.rangeValidatorType.int, 
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.TotalTime'); ?>'}));

	aris.validators.validatorManager.addValidator(
		new aris.validators.rangeValidator('tbxQuestionTime', 0, null, aris.validators.rangeValidatorType.int, 
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.QuestionTime'); ?>'}));

	aris.validators.validatorManager.addValidator(
		new aris.validators.rangeValidator('tbxQuestionCount', 0, null, aris.validators.rangeValidatorType.int, 
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.QuestionCount'); ?>'}));
			
	aris.validators.validatorManager.addValidator(
		new aris.validators.rangeValidator('tbxLagTime', 0, null, aris.validators.rangeValidatorType.int, 
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.LagTime'); ?>'}));
			
	aris.validators.validatorManager.addValidator(
		new aris.validators.rangeValidator('tbxAttemptCount', 0, null, aris.validators.rangeValidatorType.int, 
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.AttemptCount'); ?>'}));
			
	aris.validators.validatorManager.addValidator(
		new aris.validators.rangeValidator('tbxPassedScore', 0, 100, aris.validators.rangeValidatorType.int, 
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.PassedScore'); ?>'}));
	

	<?php echo J16 ? 'Joomla.submitbutton' : 'submitbutton'; ?> = function(pressbutton)
	{
		if (pressbutton == 'quiz_add$save' || pressbutton == 'quiz_add$apply')
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
<input type="hidden" name="act" value="<?php echo $act;?>" />
<input type="hidden" name="task" value="quiz_add$save" />
</form>
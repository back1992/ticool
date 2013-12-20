<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$option = $processPage->getVar('option');
	$task = 'qtemplate_add';
	$templateId = $processPage->getVar('templateId');
	$questionTypeId = $processPage->getVar('questionTypeId');
	$template = $processPage->getVar('template');
	$questionTypeList = $processPage->getVar('questionTypeList');
	$className = $processPage->getVar('className');
	$specificQuestion = $processPage->getVar('specificQuestion');
	$questionData = $processPage->getVar('questionData');
	
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/yahoo-dom-event.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/validators.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/ari.dom.js');
?>

<table class="adminheading">
	<tr>
		<th><?php AriQuizWebHelper::displayResValue('Title.QuestionTemplate'); ?>: <?php AriQuizWebHelper::displayResValue($templateId ? 'Label.UpdateItem' : 'Label.AddItem'); ?></th>
	</tr>
</table>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminform" style="width: 100%;">
	<tbody>
		<tr>
			<th colspan="2"><?php AriQuizWebHelper::displayResValue('Label.MainSettings'); ?></th>
		</tr>
	</tbody>
	<tbody id="tbQTemplateSettings">
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Name'); ?> :</td>
			<td align="left"><input type="text" id="tbxTemplateName" name="zQuiz[TemplateName]" class="text_area" size="70" value="<?php AriQuizWebHelper::displayDbValue($template->TemplateName); ?>" /></td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.QuestionType'); ?> :</td>
			<td align="left">
				<?php echo JHTML::_('select.genericlist', $questionTypeList, 'questionTypeId', "class='text_area' onchange='submitbutton(\"$task\")'", 'QuestionTypeId', 'QuestionType', $questionTypeId); ?>
			</td>
		</tr>
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.DisableQueValidation'); ?> :</td>
			<td align="left"><input type="checkbox" id="chkDisableValidation" name="zQuiz[DisableValidation]" <?php if ($template->DisableValidation) echo 'checked'; ?> value="1" /></td>
		</tr>
	</tbody>
	<tbody>
		<tr>
			<th colspan="2"><?php AriQuizWebHelper::displayResValue('Label.AdditionalSettings'); ?></th>
		</tr>
	</tbody>
	<tbody id="tbQuestionSettings">
		<tr>
			<td colspan="2">
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
		new aris.validators.requiredValidator('tbxTemplateName',
			{validationGroups : ['QTemplateValGroup'], errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.NameRequired'); ?>'}));

	<?php echo J16 ? 'Joomla.submitbutton' : 'submitbutton'; ?> = function(pressbutton)
	{
		if (pressbutton == 'qtemplate_add$save' || pressbutton == 'qtemplate_add$apply')
		{
			var disableVal = aris.DOM.$('chkDisableValidation').checked;
			if (!aris.validators.alertSummaryValidators.validate(disableVal ? ['QTemplateValGroup'] : null))
			{
				return;
			}
		}
		
		<?php echo J16 ? 'Joomla.submitform' : 'submitform'; ?>(pressbutton);
	}
</script>
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="<?php echo $task; ?>" />
<input type="hidden" name="templateId" value="<?php echo $templateId; ?>" />
</form>
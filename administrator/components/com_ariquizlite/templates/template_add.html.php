<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$option = $processPage->getVar('option');
	$fileId = $processPage->getVar('fileId');
	$file = $processPage->getVar('file');
	
		
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
	<tbody id="tbTempSettings">
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Name'); ?> :</td>
			<td align="left"><input type="text" class="text_area" id="tbxTemplateName" name="zTemplate[ShortDescription]" size="70" maxlength="200" value="<?php AriQuizWebHelper::displayDbValue($file->ShortDescription); ?>"></td>
		</tr>
		<tr valign="top">
			<td align="left"><?php AriQuizWebHelper::displayResValue('Title.CSSTemplate'); ?> :</td>
			<td align="left">
				<textarea class="text_area" id="tbxTemplate" name="zTemplate[Content]" style="width: 99%; height: 300px;"><?php AriQuizWebHelper::displayDbValue($file->Content); ?></textarea>
			</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
	aris.validators.validatorManager.addValidator(
		new aris.validators.requiredValidator('tbxTemplateName',
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.NameRequired'); ?>'}));
			
	aris.validators.validatorManager.addValidator(
		new aris.validators.customValidator('tbxTemplate',
			function(val)
			{
				var isValid = true;
				var value = val.getValue();
				isValid = (value && value.replace(/^\s+|\s+$/g, '').length > 0);

				return isValid;
			},
			{emptyValidate : true, errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.TemplateRequired'); ?>'}));

	<?php echo J16 ? 'Joomla.submitbutton' : 'submitbutton'; ?> = function(pressbutton)
	{
		if (pressbutton == 'template_add$save' || pressbutton == 'template_add$apply')
		{
			if (!aris.validators.alertSummaryValidators.validate())
			{
				return;
			}
		}

		<?php echo J16 ? 'Joomla.submitform' : 'submitform'; ?>(pressbutton);
	}
</script>
<input type="hidden" name="fileId" value="<?php echo $fileId; ?>" />
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="template_add" />
</form>
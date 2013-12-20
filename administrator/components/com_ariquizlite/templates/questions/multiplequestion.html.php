<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	AriKernel::import('Web.JSON.JSONHelper');
	
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/multiplierControls.js');
?>

<table id="tblQueContainer" style="width: 100%;" cellpadding="0" cellspacing="0">
	<tr>
		<th style="width: 1%; text-align: center;"><a href="javascript:void(0);" onclick="aris.controls.multiplierControls.addItem('tblQueContainer'); return false;">+</a></th>
		<th style="width: 1%; text-align: center;"><?php AriQuizWebHelper::displayResValue('Label.Correct'); ?></th>
		<th style="text-align: center;"><?php AriQuizWebHelper::displayResValue('Label.Answer'); ?></th>
		<th style="width: 5%; text-align: center;"><?php AriQuizWebHelper::displayResValue('Label.Actions'); ?></th>
	</tr>
	<tr id="trQueTemplate">
		<td>&nbsp;</td>
		<td style="text-align: center;"><input type="checkbox" name="cbCorrect" id="cbCorrect" value="true" /></td>
		<td>
			<input type="text" id="tbxAnswer" name="tbxAnswer" class="text_area" style="width: 99%;" />
			<input type="hidden" id="hidQueId" name="hidQueId" />
		</td>
		<td style="text-align: center; white-space: nowrap;">
			<a href="javascript:void(0);" onclick="if (confirm('<?php AriQuizWebHelper::displayResValue('Warning.QuestionAnswerRemove'); ?>')) aris.controls.multiplierControls.removeItem(aris.controls.multiplierControls.getCurrentTemplateItemId(this, 'trQueTemplate')); return false;"><?php echo JHTML::image('administrator/components/com_ariquizlite/images/publish_x.png', 'Remove', array('border' => 0)); ?></a>
			<a href="javascript:void(0);" onclick="aris.controls.multiplierControls.moveUpItem(this, 'trQueTemplate'); return false;"><?php echo JHTML::image('administrator/components/com_ariquizlite/images/uparrow.png', 'Up', array('border' => 0)); ?></a>
			<a href="javascript:void(0);" onclick="aris.controls.multiplierControls.moveDownItem(this, 'trQueTemplate', 'tblQueContainer'); return false;"><?php echo JHTML::image('administrator/components/com_ariquizlite/images/downarrow.png', 'Down', array('border' => 0)); ?></a>
		</td>
	</tr>
	<tr>
		<td colspan="4" align="left"><?php AriQuizWebHelper::displayResValue('Text.EmptyAnswerIgnored'); ?></td>
	</tr>
</table>
<script type="text/javascript">
	aris.controls.multiplierControls.init('trQueTemplate', 'tblQueContainer', 3, <?php echo AriJSONHelper::encode($specificQuestion->getDataFromXml($questionData, false)); ?>);
	
	aris.validators.validatorManager.addValidator(
		new aris.validators.customValidator(null,
			function(val)
			{
				var isValid = true;
				var isSetCorrect = false;
				var isNotEmpty = false;

				var templates = aris.DOM.getChildElementsByAttribute('tblQueContainer', aris.controls.multiplierControls.originalIdAttr, 'trQueTemplate');
				var templateCnt = templates ? templates.length : 0; 
				if (templateCnt > 0)
				{
					for (var i = 0; i < templateCnt; i++)
					{
						var template = templates[i];
						var cbCorrect = aris.DOM.getChildElementByAttribute(template, aris.controls.multiplierControls.originalIdAttr, 'cbCorrect');
						if (cbCorrect && cbCorrect.checked)
						{
							isSetCorrect = true;						
							var tbxAnswer = aris.DOM.getChildElementByAttribute(template, aris.controls.multiplierControls.originalIdAttr, 'tbxAnswer');
							var value = tbxAnswer.value;
							if (value && value.replace(/^\s+|\s+$/g, '').length > 0)
							{
								isNotEmpty = true;
								break;
							}
						}
					}
					
					if (!isSetCorrect)
					{
						this.errorMessage = aris.element.getNormalizeValue("<?php AriQuizWebHelper::displayResValue('Validator.QuestionNotCorrect'); ?>");
						isValid = false;
					}
					else if (!isNotEmpty)
					{
						this.errorMessage = aris.element.getNormalizeValue("<?php AriQuizWebHelper::displayResValue('Validator.QuestionNotAnswer'); ?>");
						isValid = false;
					}
				}
				else
				{
					this.errorMessage = aris.element.getNormalizeValue("<?php AriQuizWebHelper::displayResValue('Validator.QuestionNotAnswer'); ?>");
					isValid = false;
				}
			
				return isValid;
			},
			{emptyValidate : true, errorMessage : "<?php AriQuizWebHelper::displayResValue('Validator.QuestionNotAnswer'); ?>"}));
</script>
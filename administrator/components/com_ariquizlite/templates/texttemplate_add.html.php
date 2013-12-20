<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$option = $processPage->getVar('option');
	$params = $processPage->getVar('params');
	$templateId = $processPage->getVar('templateId');
	$template = $processPage->getVar('template');
	
	jimport( 'joomla.html.editor' );
	$editor = &JFactory::getEditor();
	
	
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/yahoo-dom-event.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/validators.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/ari.dom.js');
?>

<?php JHTML::_('behavior.tooltip'); ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminform" style="width: 100%;">
	<tbody>
		<tr>
			<th colspan="3"><?php AriQuizWebHelper::displayResValue('Label.MainSettings'); ?></th>
		</tr>
	</tbody>
	<tbody id="tbGTempSettings">
		<tr>
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Name'); ?> :</td>
			<td align="left"><input type="text" class="text_area" id="tbxTemplateName" name="zTemplate[TemplateName]" size="70" maxlength="200" value="<?php AriQuizWebHelper::displayDbValue($template->TemplateName); ?>"></td>
		</tr>
		<tr valign="top">
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Text'); ?> :</td>
			<td align="left">
			<?php
				echo $editor->display('zTemplate[Value]', $template->Value, '100%;', '250', '60', '20' ) ; 
			?>
			</td>
			<td>
				<?php AriQuizWebHelper::displayResValue('Label.Params'); ?> :<br />
				<?php
					if (!empty($params))
					{
						$tooltipText = AriQuizWebHelper::getResValue('Label.Tooltip');
						foreach ($params as $param)
						{
				?>
						<a href="javascript:void(0);" onclick="addParamToText('<?php echo sprintf('{$%s}', $param->ParamName); ?>'); return false;">{$<?php AriQuizWebHelper::displayDbValue($param->ParamName); ?>}</a>
						<?php echo JHTML::_('tooltip', $param->ParamDescription, $tooltipText); ?><br />
				<?php
						}
					}
				?>
			</td>
		</tr>
	</tbody>
</table>
<input type="hidden" name="quizId" value="<?php echo $quizId; ?>" />
<script type="text/javascript">
	function addParamToText(paramName)
	{
		if (typeof(tinyMCE) != 'undefined' && tinyMCE.execCommand)
		{
			tinyMCE.execCommand('mceInsertContent', false, paramName);
		}
	}

	aris.validators.validatorManager.addValidator(
		new aris.validators.requiredValidator('tbxTemplateName',
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.NameRequired'); ?>'}));
			
	aris.validators.validatorManager.addValidator(
		new aris.validators.customValidator('zTemplate[Value]',
			function(val)
			{
				var isValid = true;
				<?php echo $editor->save('zTemplate[Value]') ; ?>
				var value = val.getValue();
				isValid = (value && value.replace(/^\s+|\s+$/g, '').length > 0);

				return isValid;
			},
			{emptyValidate : true, errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.TextRequired'); ?>'}));

	<?php echo J16 ? 'Joomla.submitbutton' : 'submitbutton'; ?> = function(pressbutton)
	{
		if (pressbutton == 'texttemplate_add$save' || pressbutton == 'texttemplate_add$apply')
		{
			if (!aris.validators.alertSummaryValidators.validate())
			{
				return;
			}
		}
		
		<?php echo J16 ? 'Joomla.submitform' : 'submitform'; ?>(pressbutton);
	}
</script>
<input type="hidden" name="templateId" value="<?php echo $templateId; ?>" />
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="" />
</form>
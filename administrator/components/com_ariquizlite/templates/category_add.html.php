<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');

	$option = $processPage->getVar('option');
	$categoryId = $processPage->getVar('categoryId');
	$category = $processPage->getVar('category');
	$task = $processPage->getVar('task');
	
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
			<td align="left"><?php AriQuizWebHelper::displayResValue('Label.Name'); ?> :</td>
			<td align="left"><input type="text" class="text_area" id="tbxCategoryName" name="zCategory[CategoryName]" size="70" maxlength="200" value="<?php AriQuizWebHelper::displayDbValue($category->CategoryName); ?>"></td>
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
		new aris.validators.customValidator('tbxCategoryName',
			function(val)
			{
				var isValid = true;
				if (typeof(Ajax) != "undefined")
					new Ajax('index.php?option=<?php echo $option; ?>&task=ajax.checkCategoryName&categoryId=<?php echo $category->CategoryId; ?>&name=' + encodeURIComponent(val.getValue()), 
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

	<?php echo J16 ? 'Joomla.submitbutton' : 'submitbutton'; ?> = function(pressbutton)
	{
		if (pressbutton == 'category_add$save' || pressbutton == 'category_add$apply')
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
<input type="hidden" name="categoryId" value="<?php echo $categoryId;?>" />
<input type="hidden" name="task" value="category_add$save" />
</form>
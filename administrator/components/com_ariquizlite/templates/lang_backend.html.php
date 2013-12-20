<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$templateList = $processPage->getVar('templateList');
	$option = $processPage->getVar('option');
	$defaultLang = $processPage->getVar('defaultLang');
	$currentTask = $processPage->getVar('currentTask');
	$addTask = $processPage->getVar('addTask');
	
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/yahoo-dom-event.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/dragdrop-min.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/container-min.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/validators.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/ari.dom.js');
	
	$doc->addStyleSheet(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/container.css');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th class="title" width="20"><?php echo JText::_( 'Num' ); ?></th>
			<th class="title" width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($templateList); ?>);"/></th>
			<th class="title"><?php AriQuizWebHelper::displayResValue('Label.Template'); ?></th>
			<th width="5%"><?php AriQuizWebHelper::displayResValue('Label.Default'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	if (!empty($templateList)):
		$i = 0;
		foreach ($templateList as $template):
	?>
		<tr>
			<td align="center"><?php echo ($i + 1); ?></td>
			<td align="center"><?php echo JHTML::_('grid.id', $i, $template->FileId, false, 'fileId'); ?></td>
			<td align="left"><a href="index.php?option=<?php echo $option; ?>&hidemainmenu=1&task=<?php echo $addTask; ?>&fileId=<?php echo $template->FileId; ?>"><?php AriQuizWebHelper::displayDbValue($template->ShortDescription); ?></a></td>
			<td align="center" nowrap="nowrap"><input type="radio" name="rbLangDefault" value="<?php echo $template->FileId; ?>"<?php if ($template->FileId == $defaultLang) echo ' checked' ?> /></td>
		</tr>
	<?php
			++$i;
		endforeach;
	endif;
	?>
	</tbody>
</table>

<div id="panelImport">   
	<div class="hd"><?php AriQuizWebHelper::displayResValue('Toolbar.Import'); ?></div>  
	<div class="bd">
		<table border="0" cellspacing="1" cellpadding="1">
			<tr>
				<td style="white-space: nowrap;"><?php AriQuizWebHelper::displayResValue('Label.Name'); ?> :</td>
				<td><input type="text" id="tbxLangName" name="zLang[ShortDescription]" class="text_area" size="45" /></td>
			</tr>
			<tr>
				<td style="white-space: nowrap;"><?php AriQuizWebHelper::displayResValue('Label.File'); ?> :</td>
				<td><input type="file" id="fileLang" name="fileLang" class="text_area" size="45" /></td>
			</tr>
			<tr>
				<td colspan="2" align="right">
					<input type="button" class="button" onclick="submitbutton('<?php echo $currentTask . '$import'; ?>'); return false;" value="<?php AriQuizWebHelper::displayResValue('Toolbar.Import'); ?>" />
				</td>
			</tr>
		</table>
	</div>  
	<div class="ft"></div>  
</div>
<input type="hidden" name="hidemainmenu" value="0" />
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="<?php echo $currentTask; ?>" />
<input type="hidden" name="boxchecked" value="0" />
</form>
<script type="text/javascript">
	YAHOO.namespace("ari.container");
	YAHOO.ari.container.panelImport = new YAHOO.widget.Panel("panelImport", 
		{ width:"400px", visible:false, constraintoviewport:true, modal:true, fixedcenter: true});   
	YAHOO.ari.container.panelImport.render();
	
	aris.validators.validatorManager.addValidator(
		new aris.validators.requiredValidator('tbxLangName',
			{validationGroups : ['import'], errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.NameRequired'); ?>'}));   
			
	aris.validators.validatorManager.addValidator(
		new aris.validators.requiredValidator('fileLang',
			{validationGroups : ['import'], errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.FileRequired'); ?>'}));
			
	aris.validators.validatorManager.addValidator(
		new aris.validators.customValidator('fileLang',
			function(val)
			{
				var isValid = true;

				var ext = aris.file.getExtension(val.getValue());
				isValid = (ext && ext.toLowerCase() == 'xml');

				return isValid;
			},
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.FileIncorrectFormat'); ?>'}));

	<?php echo J16 ? 'Joomla.submitbutton' : 'submitbutton'; ?> = function(pressbutton)
	{
		if (pressbutton == 'import')
		{
			YAHOO.ari.container.panelImport.show();
			return ;
		}
		else if (pressbutton == '<?php echo $currentTask . '$import'; ?>')
		{
			if (!aris.validators.alertSummaryValidators.validate('import'))
			{
				return;
			}
		}

		<?php echo J16 ? 'Joomla.submitform' : 'submitform'; ?>(pressbutton);
	}
</script>
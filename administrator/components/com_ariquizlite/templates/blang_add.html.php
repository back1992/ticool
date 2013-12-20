<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$option = $processPage->getVar('option');
	$fileId = $processPage->getVar('fileId');
	$file = $processPage->getVar('file');
	$res = $processPage->getVar('res');
	$groups = $processPage->getVar('groups');
	$currentTask = $processPage->getVar('currentTask');
	
	jimport( 'joomla.html.editor' );
	$editor = &JFactory::getEditor();
	
	$doc = JFactory::getDocument();
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/yahoo-dom-event.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/element-beta-min.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/tabview-min.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/validators.js');
	$doc->addScript(JURI::root(true) . '/administrator/components/' . $option . '/js/ari.dom.js');
	
	$doc->addStyleSheet(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/tabview-core.css');
	$doc->addStyleSheet(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/tabview.css');
	$doc->addStyleSheet(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/border_tabs.css');
	$doc->addStyleSheet(JURI::root(true) . '/administrator/components/' . $option . '/js/yui/skin-sam.css');
?>

<?php JHTML::_('behavior.tooltip'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminform table table-striped" style="width: 100%;">
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
			<td colspan="2">
				<div id="langTabContainer" class="yui-navset yui-navset-top"> 
					<ul class="yui-nav">
						<?php
							$i = 0;
							foreach ($groups as $group)
							{
						?>
						<li<?php if ($i == 0) { ?> title="active" class="selected"<?php } ?>><a href="#resTab<?php echo $i; ?>"><em><?php echo $group; ?></em></a></li> 
						<?php
								++$i;
							}
						?> 
					</ul> 
					<div class="yui-content">
						<?php
							$i = 0;
							foreach ($groups as $group)
							{
						?> 
						<div style="display: none;" id="resTab<?php echo $i; ?>">
							<table class="adminlist" cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
								<tr>
									<th><?php AriQuizWebHelper::displayResValue('Label.Description'); ?></th>
									<th><?php AriQuizWebHelper::displayResValue('Label.Message'); ?></th>
								</tr>
							<?php
								$resItemList = $res[$group];
								$rowNum = 0;
								foreach ($resItemList as $resItem)
								{
									$itemId = $resItem['id'];
							?>
								<tr class="<?php echo 'row' . ($rowNum % 2); ?>" valign="top">
									<td style="width: 20%; white-space: nowrap;">
										<input id="tbxResDescr[<?php echo $itemId; ?>]" name="tbxResDescr[<?php echo $itemId; ?>]" type="text" class="text_area" value="<?php AriQuizWebHelper::displayDbValue($resItem['description']); ?>" style="width: 99%;" />
									</td>
									<td>
									<?php
										if ($resItem['type'] == 'WYSIWYG')
										{
											echo $editor->display('tbxResMessage[' . $itemId . ']', $resItem['message'], '100%;', '250', '60', '20' ) ;
										}
										else
										{
									?>
									<input id="tbxResMessage[<?php echo $itemId; ?>]" name="tbxResMessage[<?php echo $itemId; ?>]" type="text" class="text_area" value="<?php AriQuizWebHelper::displayDbValue($resItem['message']); ?>" style="width: 99%;" />
									<?php
										}
									?>
									</td>
								</tr>
							<?php
									++$rowNum;
								}
							?>
							</table>
						</div> 
						<?php
								++$i;
							}
						?> 
					</div> 
				</div>
			</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
	aris.validators.validatorManager.addValidator(
		new aris.validators.requiredValidator('tbxTemplateName',
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.NameRequired'); ?>'}));

	var tabView = new YAHOO.widget.TabView('langTabContainer');
	<?php echo J16 ? 'Joomla.submitbutton' : 'submitbutton'; ?> = function(pressbutton)
	{
		if (pressbutton == '<?php echo $currentTask; ?>$save' || pressbutton == '<?php echo $currentTask; ?>$apply')
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
<input type="hidden" name="task" value="<?php echo $currentTask; ?>" />
</form>
<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$result = $processPage->getVar('result');
	$resultText = $processPage->getVar('resultText');
	$option = $processPage->getVar('option');
	$ticketId = $processPage->getVar('ticketId');
	$infoMsg = $processPage->getVar('infoMsg');
	$printVisible = $processPage->getVar('printVisible');
	$emailVisible = $processPage->getVar('emailVisible');
?>
<form method="post" action="" style="margin: 4px 4px 4px 4px;">
	<?php 
		if (!empty($infoMsg))
		{ 
	?>
		<h3 align="center"><?php echo $infoMsg; ?></h3>
	<?php
		}
	?>
	<?php 
		if ($emailVisible)
		{ 
	?>
	<input type="submit" name="ariEvent[email]" class="button" value="<?php AriQuizWebHelper::displayResValue('Label.Email'); ?>"  />
	<?php
		}
	?>
	<?php 
		if ($printVisible)
		{ 
	?>
	<input type="button" class="button" value="<?php AriQuizWebHelper::displayResValue('Label.Print'); ?>" onclick="window.open('index.php?option=<?php echo $option; ?>&task=quiz_finished$print&ticketId=<?php echo $ticketId; ?>&tmpl=component','blank');" />
	<?php
		}
	?>
	<br/><br/>
	<?php AriQuizWebHelper::displayDbValue($resultText, false); ?>
	<br/><br/>
	<?php 
		if ($emailVisible)
		{ 
	?>
	<input type="submit" name="ariEvent[email]" class="button" value="<?php AriQuizWebHelper::displayResValue('Label.Email'); ?>"  />
	<?php
		}
	?>
	<?php 
		if ($printVisible)
		{ 
	?>
	<input type="button" class="button" value="<?php AriQuizWebHelper::displayResValue('Label.Print'); ?>" onclick="window.open('index.php?option=<?php echo $option; ?>&task=quiz_finished$print&ticketId=<?php echo $ticketId; ?>&tmpl=component','blank');" />
	<?php
		}
	?>
	<input type="hidden" name="task" value="quiz_finished" />
	<input type="hidden" name="ticketId" value="<?php echo $ticketId; ?>" />
	<input type="hidden" name="option" value="<?php echo $option; ?>" />
</form>
<br/>
<div style="text-align: center;">
Developed by <a href="http://www.ari-soft.com" target="_blank" title="ARI Soft">ARI Soft</a>.
</div>
<br/>
<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
	
	$arisI18N = $processPage->getVar('arisI18N');
	$mid = $processPage->getVar('mid');
	$rurl = $processPage->getVar('rurl');
	$Itemid = $processPage->getVar('Itemid');
?>

<form action="<?php echo $rurl; ?>" method="post">
<?php AriQuizWebHelper::displayResValue($mid); ?>
<br /><br />
<input type="submit" value="<?php AriQuizWebHelper::displayResValue('Label.Continue'); ?>" class="button" />
<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
</form>
<br/>
<div style="text-align: center;">
Developed by <a href="http://www.ari-soft.com" target="_blank" title="ARI Soft">ARI Soft</a>.
</div>
<br/>
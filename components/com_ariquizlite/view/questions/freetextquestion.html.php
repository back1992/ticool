<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
?>

<input type="text" id="tbxAnswer" name="tbxAnswer" class="ariQuizFreeText" /><br />
<script type="text/javascript">
	aris.validators.validatorManager.addValidator(
		new aris.validators.requiredValidator('tbxAnswer',
			{errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.QuestionEmptyAnswer'); ?>'}));
</script>
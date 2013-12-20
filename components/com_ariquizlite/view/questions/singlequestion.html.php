<?php
	defined('ARI_FRAMEWORK_LOADED') or die('Direct Access to this location is not allowed.');
?>

<table id="tblQueContainer" class="ariQuizAnswersContainer">
<?php
$i = 0;
foreach ($questionData as $dataItem)
{
?>
	<tr>
		<td class="ariQuizQuestionLeft ariAnswerChoice"><label for="sa<?php echo $dataItem['hidQueId']; ?>"><?php AriQuizWebHelper::displayResValue('Label.Choice'); ?> <?php echo (++$i); ?></label>&nbsp;<input type="radio" id="sa<?php echo $dataItem['hidQueId']; ?>" name="selectedAnswer" value="<?php echo $dataItem['hidQueId']; ?>" /></td>
		<td class="ariAnswer"><?php echo $dataItem['tbxAnswer']; ?></td>
	</tr>
<?php
}
?>
</table>
<script type="text/javascript">
	aris.validators.validatorManager.addValidator(
		new aris.validators.customValidator(null,
			function(val)
			{
				var isValid = true;
				
				var rbSelectedList = aris.DOM.getChildElementsByAttribute('tblQueContainer', 'name', 'selectedAnswer');
				if (rbSelectedList && rbSelectedList.length > 0)
				{
					isValid = false;
					for (var i = 0; i < rbSelectedList.length; i++)
					{
						if (rbSelectedList[i].checked)
						{
							isValid = true;
							break;
						}
					}					
				}
		
				return isValid;
			},
			{emptyValidate : true, errorMessage : '<?php AriQuizWebHelper::displayResValue('Validator.QuestionNotSelected'); ?>'}));
</script>
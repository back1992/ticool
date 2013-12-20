<?php
defined('_JEXEC') or die('Restricted access');

$basePath = JPATH_SITE . '/components/modules/';
$adminBasePath = JPATH_SITE . '/administrator/components/com_ariquizlite/';

if (@file_exists($adminBasePath)):

require_once ($adminBasePath . 'utils/constants.php');
require_once ($adminBasePath . 'kernel/class.AriKernel.php');

AriKernel::import('Controllers.ResultController');
AriKernel::import('Web.Utils.QuizWebHelper');
AriKernel::import('I18N.I18N');

$count = intval($params->get('count', 5));
if ($count < 0) $count = 5;
$measureUnit = $params->get('pointUnit', 'percent');
$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$label = $params->get('label', 'Last Results');

$resultController = new AriQuizResultController();
$results = $resultController->call('getLastResults', $count);
?>
<?php
	if (!empty($results))
	{
?>
	<table style="width: 100%; font-size: 100%;" class="aqmodtable<?php echo $moduleclass_sfx; ?>">
		<tr>
			<th colspan="3"><?php echo $label; ?></th>
		</tr>
<?php
		foreach ($results as $result)
		{
?>
		<tr>
			<td class="aqmodquiz<?php echo $moduleclass_sfx; ?>"><?php AriQuizWebHelper::displayDbValue($result->QuizName); ?></td>
			<td class="aqmoduser<?php echo $moduleclass_sfx; ?>"><?php AriQuizWebHelper::displayDbValue($result->UserName); ?></td>
			<td class="aqmodpoint<?php echo $moduleclass_sfx; ?>" style="width: 1%; white-space: nowrap;"><?php echo $measureUnit == 'point' ? $result->UserScore : sprintf('%.2f %%', $result->PercentScore); ?></td>
		</tr>	
<?php
		}
?>
	</table>
<?php
	}
endif;
?>
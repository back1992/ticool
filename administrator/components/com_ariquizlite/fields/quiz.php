<?php
defined('_JEXEC') or die( 'Restricted access' );

$basePath = dirname(__FILE__) . '/../';

require_once ($basePath . 'utils/constants.php');
require_once ($basePath . 'kernel/class.AriKernel.php');

AriKernel::import('Controllers.QuizController');
AriKernel::import('Controllers.ResultController');
AriKernel::import('Controllers.TextTemplateController');
AriKernel::import('Controllers.LicenseController');
AriKernel::import('Web.TaskManager');
AriKernel::import('Web.Controls.MultiplierControls');
AriKernel::import('Web.AdminQuizPageBase');
AriKernel::import('Web.UserQuizPageBase');
AriKernel::import('Date.Date');
AriKernel::import('Text.Text');
AriKernel::import('I18N.I18N');
AriKernel::import('Security.Security');
AriKernel::import('Entity.EntityFactory2');
AriKernel::import('Web.Utils.QuizWebHelper');
AriKernel::import('Web.Utils.Util');

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldQuiz extends JFormField
{
	protected $type = 'Quiz';

	function getInput()
	{
		return $this->fetchElement($this->element['name'], $this->value, $this->element, $this->name);
	}
	
	function fetchElement($name, $value, &$node, $control_name)
	{
		$qc = new AriQuizController();
		$quizzes = $qc->getQuizList(
			null,
			1
		);
		
		$ctrlId = str_replace(array('[', ']'), array('_', ''), $control_name);

		return JHTML::_(
			'select.genericlist', 
			$quizzes, 
			$control_name, 
			'class="inputbox"', 
			'QuizId', 
			'QuizName', 
			$value,
			$ctrlId);
	}
}

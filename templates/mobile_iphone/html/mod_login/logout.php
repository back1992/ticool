<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version		1.2.8
 * @license		GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright	(C) 2008-2013 Kuneri Ltd.
 * @date		November 2013
 */

// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" name="login" id="form-login">
<?php if ($params->get('greeting')) : ?>
	<ul>
	<li>
	<?php if ($params->get('name')) : {
		echo JText::sprintf( 'MOD_LOGIN_HINAME', $user->get('name') );
	} else : {
		echo JText::sprintf( 'MOD_LOGIN_HINAME', $user->get('username') );
	} endif; ?>
	</li>
	</ul>
<?php endif; ?>
	<div class="loginButtonWrapper">
		<input type="submit" name="Submit" class="button whiteButton loginButton" value="<?php echo JText::_( 'JLOGOUT'); ?>" />
	</div>
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.logout" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
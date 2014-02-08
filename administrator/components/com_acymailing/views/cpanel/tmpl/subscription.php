<?php
/**
 * @package	Acymailing for Joomla!
 * @version	4.0.1
 * @author	acyba.com
 * @copyright	(C) 2009-2012 ACYBA S.A.RL. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="page-subscription">
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'SUBSCRIPTION' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
				<?php echo acymailing_tooltip(JText::_('ALLOW_VISITOR_DESC'), JText::_('ALLOW_VISITOR'), '', JText::_('ALLOW_VISITOR')); ?>
				</td>
				<td>
					<?php echo $this->elements->allow_visitor; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing_tooltip(JText::_('REQUIRE_CONFIRM_DESC'), JText::_('REQUIRE_CONFIRM'), '', JText::_('REQUIRE_CONFIRM')); ?>
				</td>
				<td>
					<?php echo $this->elements->require_confirmation; ?>
					<?php echo $this->elements->editConfEmail; ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo acymailing_tooltip(JText::_('AUTO_SUBSCRIBE_DESC'), JText::_('AUTO_SUBSCRIBE'), '', JText::_('AUTO_SUBSCRIBE')); ?>
				</td>
				<td>
					<input class="inputbox" id="configautosub" name="config[autosub]" type="text" style="width:100px" value="<?php echo $this->escape($this->config->get('autosub','None')); ?>">
					<a class="modal" id="linkconfigautosub" title="<?php echo JText::_('SELECT_LISTS'); ?>"  href="index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=chooselist&amp;task=autosub&amp;values=<?php echo $this->config->get('autosub','None'); ?>&amp;control=config" rel="{handler: 'iframe', size: {x: 650, y: 375}}"><button class="btn" onclick="return false"><?php echo JText::_('SELECT'); ?></button></a>
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing_tooltip(JText::_('ALLOW_MODIFICATION_DESC'), JText::_('ALLOW_MODIFICATION'), '', JText::_('ALLOW_MODIFICATION')); ?>
				</td>
				<td>
					<?php echo $this->elements->allow_modif; ?>
					<?php echo $this->elements->editModifEmail; ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('GENERATE_NAME'); ?>
				</td>
				<td>
					<?php echo JHTML::_('acyselect.booleanlist', "config[generate_name]" , '',$this->config->get('generate_name',1) ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'NOTIFICATIONS' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key">
				<?php echo acymailing_tooltip(JText::_('NOTIF_CREATE_DESC'), JText::_('NOTIF_CREATE'), '', JText::_('NOTIF_CREATE')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_created]" style="width:200px" value="<?php echo $this->escape($this->config->get('notification_created')); ?>">
					<?php echo $this->elements->edit_notification_created; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing_tooltip(JText::_('NOTIF_UNSUB_DESC'), JText::_('NOTIF_UNSUB'), '', JText::_('NOTIF_UNSUB')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_unsub]" style="width:200px" value="<?php echo $this->escape($this->config->get('notification_unsub')); ?>">
					<?php echo $this->elements->edit_notification_unsub; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing_tooltip(JText::_('NOTIF_UNSUBALL_DESC'), JText::_('NOTIF_UNSUBALL'), '', JText::_('NOTIF_UNSUBALL')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_unsuball]" style="width:200px" value="<?php echo $this->escape($this->config->get('notification_unsuball')); ?>">
					<?php echo $this->elements->edit_notification_unsuball; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing_tooltip(JText::_('NOTIF_REFUSE_DESC'), JText::_('NOTIF_REFUSE'), '', JText::_('NOTIF_REFUSE')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_refuse]" style="width:200px" value="<?php echo $this->escape($this->config->get('notification_refuse')); ?>">
					<?php echo $this->elements->edit_notification_refuse; ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'REDIRECTIONS' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key">
				<?php echo acymailing_tooltip(JText::_('REDIRECTION_SUB_DESC').'<br/><br/><i>'.JText::_('REDIRECTION_NOT_MODULE').'</i>', JText::_('REDIRECTION_SUB'), '', JText::_('REDIRECTION_SUB')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="sub_redirect" name="config[sub_redirect]" style="width:250px" value="<?php echo $this->escape($this->config->get('sub_redirect')); ?>">
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing_tooltip(JText::_('REDIRECTION_MODIF_DESC').'<br/><br/><i>'.JText::_('REDIRECTION_NOT_MODULE').'</i>', JText::_('REDIRECTION_MODIF'), '', JText::_('REDIRECTION_MODIF')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="modif_redirect" name="config[modif_redirect]" style="width:250px" value="<?php echo $this->escape($this->config->get('modif_redirect')); ?>">
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing_tooltip(JText::_('REDIRECTION_CONFIRM_DESC'), JText::_('REDIRECTION_CONFIRM'), '', JText::_('REDIRECTION_CONFIRM')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="confirm_redirect" name="config[confirm_redirect]" style="width:250px" value="<?php echo $this->escape($this->config->get('confirm_redirect')); ?>">
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing_tooltip(JText::_('REDIRECTION_UNSUB_DESC'), JText::_('REDIRECTION_UNSUB'), '', JText::_('REDIRECTION_UNSUB')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="unsub_redirect" name="config[unsub_redirect]" style="width:250px" value="<?php echo $this->escape($this->config->get('unsub_redirect')); ?>">
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing_tooltip(JText::_('REDIRECTION_MODULE_DESC'), JText::_('REDIRECTION_MODULE'), '', JText::_('REDIRECTION_MODULE')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="module_redirect" name="config[module_redirect]" style="width:250px" value="<?php echo $this->escape($this->config->get('module_redirect')); ?>">
				</td>
			</tr>
		</table>
	</fieldset>
</div>

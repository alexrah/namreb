<?php
/**
* @package		Komento
* @copyright	Copyright (C) 2012 Stack Ideas Private Limited. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Komento is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Restricted access');
$componentHelper = Komento::getHelper( 'components' );
if( $componentHelper->isInstalled( 'com_rscomments' ) ) {

$components	= Komento::getModel( 'migrators', true )->getMigrator( 'rscomments' )->getUniqueComponents();
$selection = array();

foreach ( $components as $component )
{
	$selection[] = JHtml::_('select.option', $component, JText::_( 'COM_KOMENTO_' . strtoupper( $component ) ) );
}

$componentSelect = JHTML::_( 'select.genericlist' , $selection , 'components' , 'multiple="multiple" size="10" style="height: auto !important;"' , 'value' , 'text' );
?>

<div id="migrator-rscomments" migrator-type="rscomments" migration-type="component" class="noshow migratorTable row-fluid">
	<div class="span12">
		<div class="span6">
			<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_KOMENTO_MIGRATORS_LAYOUT_MAIN' ); ?></legend>
				<table class="migrator-options admintable">
					<tr>
						<td class="key"><span><?php echo JText::_( 'COM_KOMENTO_MIGRATORS_SELECT_COMPONENTS' ); ?></span></td>
						<td><?php echo $componentSelect; ?></td>
					</tr>
					<tr>
						<td class="key"><span><?php echo JText::_( 'COM_KOMENTO_MIGRATORS_SELECT_PUBLISHING_STATE' ); ?></span></td>
						<td>
							<select id="publishingState">
								<option value="inherit"><?php echo JText::_( 'COM_KOMENTO_MIGRATORS_PUBLISHING_STATE_INHERIT' ); ?></option>
								<option value="1"><?php echo JText::_( 'COM_KOMENTO_MIGRATORS_PUBLISHING_STATE_PUBLISHED' ); ?></option>
								<option value="0"><?php echo JText::_( 'COM_KOMENTO_MIGRATORS_PUBLISHING_STATE_UNPUBLISHED' ); ?></option>
							</select>
						</td>
					</tr>
				</table>

				<a href="javascript:void(0);" class="migrateButton button"><?php echo JText::_( 'COM_KOMENTO_MIGRATORS_START_MIGRATE' ); ?></a>
			</fieldset>
		</div>

		<div class="migratorProgress span6">
			<?php echo $this->loadTemplate( 'progress' ); ?>
		</div>
	</div>
</div>
<?php } else { ?>
<div id="migrator-rscomments" class="noshow adminlist row-fluid">
	<div class="span12">
		<div class="span6"><?php echo JText::_( 'COM_KOMENTO_RSCOMMENTS_NOT_INSTALLED' ); ?></div>
		<div class="span6"></div>
	</div>
</div>
<?php } ?>

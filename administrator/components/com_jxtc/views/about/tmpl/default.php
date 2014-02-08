<?php
/***********************************************************************************
************************************************************************************
***                                                                              ***
***   XTC Template Framework helper   1.2.0                                      ***
***                                                                              ***
***   Copyright (c) 2010-2012 Monev Software LLC,  All Rights Reserved           ***
***                                                                              ***
***   This program is free software; you can redistribute it and/or modify       ***
***   it under the terms of the GNU General Public License as published by       ***
***   the Free Software Foundation; either version 2 of the License, or          ***
***   (at your option) any later version.                                        ***
***                                                                              ***
***   This program is distributed in the hope that it will be useful,            ***
***   but WITHOUT ANY WARRANTY; without even the implied warranty of             ***
***   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              ***
***   GNU General Public License for more details.                               ***
***                                                                              ***
***   You should have received a copy of the GNU General Public License          ***
***   along with this program; if not, write to the Free Software                ***
***   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA   ***
***                                                                              ***
***   See COPYRIGHT.php for more information.                                    ***
***   See LICENSE.php for more information.                                      ***
***                                                                              ***
***   www.joomlaxtc.com                                                          ***
***                                                                              ***
************************************************************************************
***********************************************************************************/

defined('_JEXEC') or die;

JHTML::_('behavior.tooltip');

$xmlFile = JPATH_COMPONENT.'/jxtc.xml';
$xml = simplexml_load_file( $xmlFile );

JToolBarHelper::title( JText::_( 'JoomlaXTC XTC Framework Helper' ) );
?>
<div id="jxtc">
	<fieldset>
		<h2>JoomlaXTC XTC Framework Helper</h2>
		<br/>
		<?php echo JText::_('Version:'); ?><b><?php echo $xml->version; ?></b></td></tr>
		<br/><br/>
		<?php echo $xml->copyright; ?>
		<br/><br/>
		<a href="index.php?option=com_templates"><?php echo JText::_('JXTC_TEMPLATE_MANAGER'); ?></a>
	</fieldset>
</div>

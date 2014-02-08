<?php
/*
	JoomlaXTC Contact Wall

	version 1.5.0

	Copyright (C) 2008-2012 Monev Software LLC.	All Rights Reserved.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

	THIS LICENSE IS NOT EXTENSIVE TO ACCOMPANYING FILES UNLESS NOTED.

	See COPYRIGHT.php for more information.
	See LICENSE.php for more information.

	Monev Software LLC
	www.joomlaxtc.com
*/

defined( '_JEXEC' ) or die;

jimport( 'joomla.html.parameter' );


//Core calls
$live_site = JURI::base();
$doc = JFactory::getDocument();
$moduleDir = 'mod_jxtc_contactwall';
$db = JFactory::getDBO();

require_once (JPATH_SITE.'/components/com_contact/helpers/route.php');
// Include the syndicate functions only once
require_once 'helper.php' ;

//Parameters
$catid = $params->get('category_id');
$linked = $params->get('linked', 0);
$image = $params->get('image', 0);
$sortfield = $params->get('sortfield', 0);
$sortorder = $params->get('sortorder', 1);
$root = $params->get('root', 1);

$template	= $params->get('template','');
$moduletemplate = trim( $params->get('modulehtml','{mainarea}'));
$itemtemplate = trim( $params->get('html','{description}'));
$columns = $params->get('columns',1);
$rows	= $params->get('rows', 1);
$pages = $params->get('pages', 1);

		$moreclone = $params->get('moreclone', 0);
		$moreqty = $params->get('moreqty', 0);
		$morecols	= trim( $params->get('morecols',1));
		$morelegend	= trim($params->get('moretext', ''));
		$morelegendcolor	= $params->get('morergb','cccccc');
		$moretemplate	= $params->get('moretemplate', '');

$maxtext	= $params->get('maxtext', '');
$maxtextsuf	= $params->get('maxtextsuf', '...');

$maxmisc	= $params->get('maxmisc', '');
$maxmiscsuf	= $params->get('maxmiscsuf', '...');

if ($template && $template != -1) {
    $moduletemplate=file_get_contents(JPATH_ROOT.'/modules/'.$moduleDir.'/templates/'.$template.'/module.html');
    $itemtemplate=file_get_contents(JPATH_ROOT.'/modules/'.$moduleDir.'/templates/'.$template.'/element.html');
		$moretemplate=file_get_contents(JPATH_ROOT.'/modules/'.$moduleDir.'/templates/'.$template.'/more.html');
    if (file_exists(JPATH_ROOT.'/modules/'.$moduleDir.'/templates/'.$template.'/template.css')) {
        $doc->addStyleSheet($live_site.'modules/'.$moduleDir.'/templates/'.$template.'/template.css','text/css');
    }
}

// Build Query
$varaux = $columns*$rows*$pages;
$varaux += $moreqty;

$items = mod_jxtc_contactwallHelper::getData( $catid, $varaux, $sortfield, $sortorder, $linked, $image);

if (count($items) == 0) return;// Return if empty
$cloneditems = $items;

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));

// Build More Area

if ($moreclone) { $items = $cloneditems; }
$moreareahtml='';
if (count($items) > 0) {
	if ($morelegend) {
		$moreareahtml .= '<a style="color:#'.$morelegendcolor.'">'.$morelegend.'</a><br/>';
	}
	$moreareahtml .= '<table class="jnp_more" border="0" cellpadding="0" cellspacing="0">';
	$c=1;$cnt = 0;
	foreach ( $items as $item ) {
		if ($cnt++ > $moreqty) { continue; }
		if ($c==1) {
			$moreareahtml .= '<tr>';
		}
		$itemhtml = $moretemplate;
		require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
		$moreareahtml .= '<td>'.$itemhtml.'</td>';
		$c++;
		if ($c > $morecols) {
			$moreareahtml .= '</tr>';
			$c=1;
		}
	}
	if ($c > 1) $moreareahtml .= '</tr>';
	$moreareahtml .= '</table>';
}

$modulehtml = str_replace( '{morearea}', $moreareahtml, $modulehtml );

JPluginHelper::importPlugin('content');
$contentconfig = JComponentHelper::getParams('com_content');
$dispatcher = JDispatcher::getInstance();
$item = new stdClass();
$item->text = $modulehtml;
$results = $dispatcher->trigger('onContentPrepare', array ('com_content.article', &$item, &$contentconfig, 0 ));
$modulehtml = $item->text;

echo '<div id="'.$jxtc.'">'.$modulehtml.'</div>';

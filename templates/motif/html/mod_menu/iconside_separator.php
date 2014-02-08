<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$title = $item->anchor_title ? 'title="'.$item->anchor_title.'" ' : '';
if ($item->menu_image) {
		$item->params->get('menu_text', 1 ) ?
		$linktype = '<img src="'.$item->menu_image.'" alt="'.cleanXmenu($item->title).'" /><span class="image-title">'.cleanXmenu($item->title).'</span> ' :
		$linktype = '<img src="'.$item->menu_image.'" alt="'.cleanXmenu($item->title).'" />';
}
else { $linktype = parseXmenu($item->title);
}

?><span class="separator"><?php echo $title; ?><?php echo $linktype; ?></span>

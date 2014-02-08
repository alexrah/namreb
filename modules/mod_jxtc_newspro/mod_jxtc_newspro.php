<?php
/*
	JoomlaXTC Deluxe News Pro

	version 3.41.0

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


if (!function_exists('npMakeLink')) {
	function npMakeLink($link,$label,$target) {
		$label = ($label) ? $label : $link;
		switch ($target) {
			case 1: // open in a new window
				$html = '<a href="'.htmlspecialchars($link).'" target="_blank" rel="nofollow">'.htmlspecialchars($label).'</a>';
				break;
			case 2: // open in a popup window
				$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=600';
				$html = "<a href=\"".htmlspecialchars($link)."\" onclick=\"window.open(this.href,'targetWindow','".$attribs."');return false;\">".htmlspecialchars($label).'</a>';
				break;
			case 3: // open in a modal window
				JHtml::_('behavior.modal', 'a.modal');
				$html = '<a class="modal" href="'.htmlspecialchars($link).'" rel="{handler:\'iframe\',size:{x:600,y:600}}">'.htmlspecialchars($label).'</a>';
				break;
			default: // open in parent window
				$html = '<a href="'.htmlspecialchars($link).'" rel="nofollow">'.htmlspecialchars($label).'</a>';
				break;
		}
		return $html;
	}
}

//Core calls
$live_site = JURI::base();
$doc = JFactory::getDocument();
$db = JFactory::getDBO();
$moduleDir = 'mod_jxtc_newspro';

$user = JFactory::getUser();
$contentconfig = JComponentHelper::getParams('com_content');

require_once (JPATH_SITE.'/components/com_content/helpers/route.php');

//Core Vars

$userid = $user->get('id');
$accesslevel = !$contentconfig->get('show_noauth');
$nullDate = $db->getNullDate();
$date = JFactory::getDate();
$now = $date->toSQL();

//Parameters
$artid = trim($params->get('artid', ''));
$filteraccess = $params->get('filteraccess', 0);
$compat = $params->get('compat', 0);
$catid = $params->get('catid',0);

$usecurrentcat = $params->get('usecurrentcat', 1);
$authorid = (array) $params->get('authorid', 0);
$includefrontpage = $params->get('includefrontpage', 1);
$group = $params->get('group', 0);
$sortorder = $params->get('sortorder', 3);
$order = $params->get('order', 3);
$rows = $params->get('rows', 1);
$columns = $params->get('columns', 1);
$pages = $params->get('pages', 1);
$template = $params->get('template', '');
$moduletemplate = trim($params->get('modulehtml', '{mainarea}'));
$itemtemplate = trim($params->get('html', '{intro}'));
$mainmaxtitle = $params->get('maxtitle', '');
$mainmaxtitlesuf = $params->get('maxtitlesuf', '...');
$mainmaxintro = $params->get('maxintro', '');
$mainmaxintrosuf = $params->get('maxintrosuf', '...');
$mainmaxtext = $params->get('maxtext', '');
$mainmaxtextsuf = $params->get('maxtextsuf', '...');
$maintextbrk = $params->get('textbrk', '');
$dateformat = trim($params->get('dateformat', 'Y-m-d'));
$moreclone = $params->get('moreclone', 0);
$morepos = $params->get('morepos', 'b');
$moreqty = $params->get('moreqty', 0);
$morecols = trim($params->get('morecols', 1));
$morelegend = trim($params->get('moretext', ''));
$morelegendcolor = $params->get('morergb', 'cccccc');
$moretemplate = $params->get('morehtml', '{title}');
$moremaxtitle = trim($params->get('moretitle'));
$moremaxtitlesuf = $params->get('moretitlesuf', '...');
$moremaxintro = trim($params->get('moreintro'));
$moremaxintrosuf = $params->get('moreintrosuf', '...');
$moremaxtext = trim($params->get('moremaxtext'));
$moremaxtextsuf = $params->get('moremaxtextsuf', '...');
$moretextbrk = $params->get('moretextbrk', '');
$enablerl = $params->get('enablerl', 0);

if ($template && $template != -1) {
    $moduletemplate = file_get_contents(JPATH_ROOT.'/modules/mod_jxtc_newspro/templates/'.$template.'/module.html');
    $itemtemplate = file_get_contents(JPATH_ROOT.'/modules/mod_jxtc_newspro/templates/'.$template.'/element.html');
    $moretemplate = file_get_contents(JPATH_ROOT.'/modules/mod_jxtc_newspro/templates/'.$template.'/more.html');
    if (file_exists(JPATH_ROOT.'/modules/mod_jxtc_newspro/templates/'.$template.'/template.css')) {
        $doc->addStyleSheet($live_site . 'modules/mod_jxtc_newspro/templates/' . $template . '/template.css', 'text/css');
    }
}

// Build Query
if ($usecurrentcat == 1) {
    $option = JRequest::getCmd('option');
    $view = JRequest::getCmd('view');
    if ($option == 'com_content' and $view == "category") {
        $catid = JRequest::getCmd('id');
    }
}

$query = 'SELECT a.id, a.access,a.introtext,a.fulltext, a.title,UNIX_TIMESTAMP(a.created) as created,UNIX_TIMESTAMP(a.modified) as modified, a.catid, a.created_by, a.created_by_alias, a.hits, a.alias, a.images, a.urls,
	cc.title as cat_title, cc.params as cat_params, cc.description as cat_description, cc.alias as cat_alias,
	u.name as author, u.username as authorid,
	CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,
	CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug
	FROM #__content AS a';
if ($includefrontpage == '0') {
    $query .= ' LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id';
}
$query .= ' INNER JOIN #__categories AS cc ON cc.id = a.catid
	LEFT JOIN #__users AS u ON u.id = a.created_by';
if ($includefrontpage == '2') {
    $query .= ', #__content_frontpage AS f ';
}
$query .= ' WHERE a.state = 1 ';
if ($includefrontpage == '2') {
    $query .= ' AND f.content_id = a.id ';
}
$query .= 'AND ( a.publish_up = ' . $db->Quote($nullDate) . ' OR a.publish_up <= ' . $db->Quote($now) . ' )
	AND ( a.publish_down = ' . $db->Quote($nullDate) . ' OR a.publish_down >= ' . $db->Quote($now) . ' )
	AND (cc.published = 1 OR cc.published IS NULL)';

if ($accesslevel && $filteraccess) {
    $groups = implode(',', $user->getAuthorisedViewLevels());
    $query .= ' AND a.access IN (' . $groups . ')';
}
if ($artid) {
    $articles = explode(',', $artid);
    JArrayHelper::toInteger($articles);
    $query .= ' AND a.id in (' . join(',', $articles) . ') ';
} else {
  if ($catid) {
    if (is_array($catid)) {
      if ($catid[0] != 0) {
				$query .= ' AND (cc.id='.join(' OR cc.id=', $catid).')';
      }
    }
    else {
			$query .= ' AND (cc.id = ' . $catid . ')';
    }
  }
}
if ($includefrontpage == '0') {
    $query .= ' AND f.content_id IS NULL ';
}

if ($authorid[0] != 0) {
    $query .= ' AND created_by in (' . join(',', $authorid) . ')';
}

if ($group == 1) {
    $query .= ' GROUP BY a.created_by';
}
$query .= ' ORDER BY ';

$aux = ($order == '0') ? ' ASC ' : ' DESC ';

switch ($sortorder) {
    case 0: // creation
        $query .= 'a.created'.$aux;
        break;
    case 1: // modified
        $query .= 'a.modified'.$aux;
        break;
    case 2: // hits
        $query .= 'a.hits'.$aux;
        break;
    case 3: // joomla order
        $query .= 'a.ordering'.$aux;
        break;
    case 5: // Category Title
        $query .= 'cc.title'.$aux;
        break;
    case 6: // Article Title
        $query .= 'a.title'.$aux;
        break;
    case 7:
        $query .= 'RAND()';
        break;
}

// echo nl2br(str_replace('#__','jos_',$query));

$mainqty = $columns * $rows * $pages;
$db->setQuery($query, 0, $mainqty + $moreqty);
$items = $db->loadObjectList();
$cloneditems = $items;
if (count($items) == 0) return; // Return if empty

$rowmaxintro = $mainmaxintro;
$rowmaxintrosuf = $mainmaxintrosuf;
$rowmaxtitle = $mainmaxtitle;
$rowmaxtitlesuf = $mainmaxtitlesuf;
$rowmaxtext = $mainmaxtext;
$rowmaxtextsuf = $mainmaxtextsuf;
$rowtextbrk = $maintextbrk;

// Check for RL support
if ($enablerl && ( stripos($mainareahtml,'{readinglist}')!==false || stripos($moreareahtml,'{readinglist}')!==false)) {
	jimport( 'joomla.plugin.helper' );
	if (JpluginHelper::isEnabled('content','jxtcreadinglist')) {
		echo 'IS ENABLED';
	}
}
else $enablerl = false;

// Check for RL support
$enablerl = false;
if (stripos($itemtemplate,'{readinglist}')!==false || stripos($moretemplate,'{readinglist}')!==false) {
	jimport( 'joomla.plugin.helper' );
	$enablerl = JpluginHelper::isEnabled('content','jxtcreadinglist');
}

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));

// Build More Area

if ($moreclone) {
    $items = $cloneditems;
}
$moreareahtml = '';
if (count($items) > 0) {
    $rowmaxintro = $moremaxintro;
    $rowmaxtitle = $moremaxtitle;
    $rowmaxtext = $moremaxtext;
    $rowmaxintrosuf = $moremaxintrosuf;
    $rowmaxtitlesuf = $moremaxtitlesuf;
    $rowmaxtextsuf = $moremaxtextsuf;
    $rowtextbrk = $moretextbrk;
    if ($morelegend) {
        $moreareahtml .= '<a style="color:#' . $morelegendcolor . '">' . $morelegend . '</a><br/>';
    }
    $moreareahtml .= '<table class="jnp_more" border="0" cellpadding="0" cellspacing="0">';
    $c = 1;
    $cnt = 0;
    foreach ($items as $item) {
        if ($cnt++ > $moreqty) {
            continue;
        }
        if ($c == 1) {
            $moreareahtml .= '<tr>';
        }
        $itemhtml = $moretemplate;
        require JModuleHelper::getLayoutPath($module->module, 'default_parse');
        $moreareahtml .= '<td>' . $itemhtml . '</td>';
        $c++;
        if ($c > $morecols) {
            $moreareahtml .= '</tr>';
            $c = 1;
        }
    }
    if ($c > 1)
        $moreareahtml .= '</tr>';
    $moreareahtml .= '</table>';
}

$modulehtml = str_replace('{morearea}', $moreareahtml, $modulehtml);

JPluginHelper::importPlugin('content');
$contentconfig = JComponentHelper::getParams('com_content');
$dispatcher = JDispatcher::getInstance();
$item = new stdClass();
$item->text = $modulehtml;
$results = $dispatcher->trigger('onContentPrepare', array ('com_content.article', &$item, &$contentconfig, 0 ));
$modulehtml = $item->text;

echo '<div id="' . $jxtc . '">' . $modulehtml . '</div>';
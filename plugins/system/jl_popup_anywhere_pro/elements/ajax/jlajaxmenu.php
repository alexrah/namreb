<?php 
/**
 * @version		$Id: $
 * @author		Mr.LongAnh
 * @package		Joomla!
 * @subpackage	Plugin
 * @copyright	Copyright (C) 2008 - 2011 by Codextension. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL version 3
 */

// Check to ensure this file is included in Joomla!
// Set flag that this is a parent file
define( '_JEXEC', 1 );
define( 'JPATH_BASE', realpath(dirname(__FILE__).'/../../../../..' ));
define( 'DS', DIRECTORY_SEPARATOR );
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );


// Mark afterLoad in the profiler.
JDEBUG ? $_PROFILER->mark('afterLoad') : null;

// Instantiate the application.
$app = JFactory::getApplication('administrator');

$jlang = JFactory::getLanguage();
$jlang->load('plg_system_jl_popup_anywhere_pro', JPATH_ADMINISTRATOR, null, true);

$menutype	=  JRequest::getVar('menutype','0');
$name		=  JRequest::getVar('jlname','');

if( !$menutype ){
	echo JText::_('PLG_JL_POPUP_ANYWHERE_PRO_ABOVE_MENU');exit;
}
	
	

$db = &JFactory::getDBO();

$articles			= array();
//$articles[0]->id	= '0';
//$articles[0]->title	= JText::_('PLG_JL_POPUP_ANYWHERE_PRO_SELECT_MENU_ITEMS');

//a.client_id=1=>menutype = menu=>display in menu admin
//a.client_id=0=>menutype = mainmenu,usermenu...=>display in com_menu
$arrmenutype = explode(',', $menutype);
$remenutype = array();
if( !empty($arrmenutype) && is_array($arrmenutype) ){
	foreach( $arrmenutype as $arr ){
		if( $arr ){
			$query = "SELECT t.title,t.id FROM `#__menu_types` AS t WHERE t.menutype='".$arr."'";
			$db->setQuery( $query );
			$t_menu = $db->loadObject();
			
			$query = "SELECT a.id,a.title,a.level,ag.title AS access_level FROM `#__menu` AS a LEFT JOIN #__viewlevels AS ag ON ag.id = a.access WHERE a.id > 1 AND a.client_id = 0 AND a.published = 1 AND a.menutype='".$arr."' ORDER BY a.lft asc";
			$db->setQuery( $query );
			$menus = $db->loadObjectList();

			if( count($menus) ){
				$render_menugroup = new stdClass();
				$render_menugroup = JHTML::_('select.optgroup',$t_menu->title, 'id', 'title');
				array_push($articles, $render_menugroup);
				foreach($menus as $item){
					$check = new stdClass();
					$check->id=$item->id;
					$check->title=str_repeat('&nbsp;&nbsp;', $item->level-1).str_repeat('|&mdash;', $item->level-1).'&nbsp;'.$item->title;
					array_push($articles, $check);
				}
				$render_menugroup = JHTML::_('select.optgroup',$t_menu->title, 'id', 'title');
				array_push($articles, $render_menugroup);
			}
		}
	}
}
echo $out	= JHTML::_('select.genericlist',  $articles, $name.'[]', 'class="inputbox jlmenu" style="width:98%;" multiple="multiple" size="10"', 'id', 'title');
?>
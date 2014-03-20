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
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');

class JFormFieldJLMenu extends JFormField {
	protected $type = 'jlmenu';

	protected function getInput(){
		$name	= $this->name;
		$value	= $this->value;
		$id		= $this->id;
		$menutype = '';
		if( isset($this->form->getValue('params')->menutype) ){
			$menutype = $this->form->getValue('params')->menutype;
		}
		$document = JFactory::getDocument();
		
		if( !$menutype ){
			// call ajax
			$out = '<div id="jlitems">'.JText::_('PLG_JL_POPUP_ANYWHERE_PRO_LOADING').'</div><div class="clear"></div>';
			$document->addScriptDeclaration('
				jQuery(document).ready(function(){
					jlloadAjax();
					jQuery("select.jlajax").mouseup(function() {
						jlloadAjax();
					});
					jQuery("select.jlajax").parent("li").append("<div class=\"clr\"></div>");
				});
				function jlloadAjax(){
					//var menuname = jQuery("select.jlajax option:selected").val();
					var menuname = "";
					jQuery("select.jlajax option:selected").each(function () {
							menuname += jQuery(this).val() + ",";
					});
					jQuery.ajax({
						  url: "../plugins/system/jl_popup_anywhere_pro/elements/ajax/jlajaxmenu.php",
						  type: "POST",
						  data: { menutype: menuname,jlname:"'.$this->name.'"},
						  error: function ( jqXHR, textStatus, errorThrown ) {
							 jQuery("#jlitems").html("'.JText::_('PLG_JL_POPUP_ANYWHERE_PRO_ERROR_AJAX_MENU_TYPE').'");
						  }		
					}).done(function( html ) {
						  jQuery("#jlitems").html(html);
					});
				}
			');
		}else{
			$document->addScriptDeclaration('
				jQuery(document).ready(function(){
					jQuery("select.jlajax").mouseup(function() {
						jlloadAjax();
					});
					jQuery("select.jlajax").parent("li").append("<div class=\"clr\"></div>");
				});
				function jlloadAjax(){
					//var menuname = jQuery("select.jlajax option:selected").val();
					var menuname = "";
					jQuery("select.jlajax option:selected").each(function () {
							menuname += jQuery(this).val() + ",";
					});
					jQuery.ajax({
						  url: "../plugins/system/jl_popup_anywhere_pro/elements/ajax/jlajaxmenu.php",
						  type: "POST",
						  data: { menutype: menuname,jlname:"'.$this->name.'"},
						  beforeSend: function ( xhr ) {
							 jQuery("#jlitems").html("'.JText::_('PLG_JL_POPUP_ANYWHERE_PRO_LOADING').'");
						  },
						  error: function ( jqXHR, textStatus, errorThrown ) {
							 jQuery("#jlitems").html("'.JText::_('PLG_JL_POPUP_ANYWHERE_PRO_ERROR_AJAX_MENU_TYPE').'");
						  }		
					}).done(function( html ) {
						  jQuery("#jlitems").html(html);
					});
				}
			');
			$out = '<div id="jlitems">';
			$db = &JFactory::getDBO();

			$articles			= array();
			//$articles[0]->id	= '0';
			//$articles[0]->title	= JText::_('PLG_JL_POPUP_ANYWHERE_PRO_SELECT_MENU_ITEMS');
			
			//a.client_id=1=>menutype = menu=>display in menu admin
			//a.client_id=0=>menutype = mainmenu,usermenu...=>display in com_menu
			if( !empty($menutype) && is_array($menutype) ){
				foreach( $menutype as $arr ){
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
			$out	.= JHTML::_('select.genericlist',  $articles, $name.'[]', 'class="inputbox jlmenu" style="width:98%;" multiple="multiple" size="10"', 'id', 'title',$value);
			$out	.= '</div>';
			$out	.= '<div class="clear"></div>';
		}
		return $out;
	}
}
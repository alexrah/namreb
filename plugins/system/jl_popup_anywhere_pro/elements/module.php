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

class JFormFieldModule extends JFormField {
	protected $type = 'module';

	protected function getInput(){
		$name	= $this->name;
		$value	= $this->value;
		$id		= $this->id;
		$out	= '';
			
		
		$db = &JFactory::getDBO();

		$modules			= array();
		$modules[0]->id	= '0';
		$modules[0]->title	= JText::_('PLG_JL_POPUP_ANYWHERE_PRO_SELECT_MODULES');

		
		
		$query = "SELECT m.id,m.title FROM `#__modules` AS m WHERE m.client_id='0' AND m.published='1' ORDER BY m.id,m.title,m.published ASC";
		$db->setQuery( $query );
		$listmodules = $db->loadObjectList();
		
		if( count($listmodules) ){
			foreach($listmodules as $item){
				$check = new stdClass();
				$check->id=$item->id;
				$check->title=$item->title;
				array_push($modules, $check);
			}
		}
		$out	.= JHTML::_('select.genericlist',  $modules, $name, 'class="inputbox" style="width:98%;"', 'id', 'title',$value);
		$out	.= '<div class="clear"></div>';
		return $out;
	}
}
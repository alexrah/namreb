<?php
/**
 * @version     1.0.0
 * @package     com_popup_anywhere
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      mrlonganh <contact@codextension.com> - http://codextension.com
 */

defined('_JEXEC') or die;
JHTML::_('behavior.framework');

$jlang = JFactory::getLanguage();
$jlang->load('plg_system_jl_popup_anywhere_pro', JPATH_SITE, null, true);

$id = JRequest::getVar('id','0');

if( !$id ){
	echo JText::_('Load module failed!');exit;
}
jimport('joomla.application.module.helper');
jimport('joomla.filesystem.file');
jimport('joomla.application.component.model');

$module = JTable::getInstance('module');
$module->load($id);

if($module){
	//load languagle for module
	$jlang->load($module->module, JPATH_SITE, null, true);
	$module->user = JFactory::getUser()->id;	
	echo JModuleHelper::renderModule($module);
}
?>
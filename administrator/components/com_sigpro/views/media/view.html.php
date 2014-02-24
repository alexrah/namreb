<?php
/**
 * @version		$Id: view.html.php 2725 2013-04-06 17:05:49Z joomlaworks $
 * @package		Simple Image Gallery Pro
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

class SigProViewMedia extends SigProView
{

	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		//$mainframe->enqueueMessage(JText::_('COM_SIGPRO_MEDIA_MANAGER_INFO'));
		parent::display($tpl);
	}

}

<?php
/**
 * @version		$Id: sigpro.php 2725 2013-04-06 17:05:49Z joomlaworks $
 * @package		Simple Image Gallery Pro
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

// Check user permissions
if (version_compare(JVERSION, '2.5', 'ge'))
{
	$user = JFactory::getUser();
	if (!$user->authorise('core.manage', 'com_sigpro'))
	{
		JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
		$mainframe = JFactory::getApplication();
		$mainframe->redirect('index.php');
	}
}

// Load the helper and initialize
JLoader::register('SigProHelper', JPATH_COMPONENT.'/helper.php');
SigProHelper::initialize();

// Bootstrap
$view = JRequest::getCmd('view', 'galleries');
if (JFile::exists(JPATH_COMPONENT.'/controllers/'.$view.'.php'))
{
	JRequest::setVar('view', $view);
	require_once JPATH_COMPONENT.'/controllers/'.$view.'.php';
	$class = 'SigProController'.ucfirst($view);
	$controller = new $class();
	$controller->execute(JRequest::getWord('task'));
	$controller->redirect();
}
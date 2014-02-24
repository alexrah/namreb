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

// Get application
$application = JFactory::getApplication();

// Check user is logged in
$user = JFactory::getUser();
if ($user->guest)
{
	JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
	$application->redirect('index.php');
}

// Load admin language
$language = JFactory::getLanguage();
$language->load('com_sigpro', JPATH_ADMINISTRATOR);

// Load the helper and initialize
JLoader::register('SigProHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helper.php');
SigProHelper::initialize();

// Add model path
if (version_compare(JVERSION, '3.0', 'ge'))
{
	JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/models');

}
else
{
	JModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/models');

}

// Check some variables for security reasons
$view = JRequest::getCmd('view', 'galleries');
{
	if ($view == 'media' || $view == 'info' || $view == 'settings')
	{
		JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
		$application->redirect('index.php');
	}
}
$type = JRequest::getCmd('type');
if ($type != 'site' && $type != 'k2')
{
	JRequest::setVar('type', 'site');
}

// Bootstrap
if (JFile::exists(JPATH_COMPONENT_ADMINISTRATOR.'/controllers/'.$view.'.php'))
{
	JRequest::setVar('view', $view);
	require_once JPATH_COMPONENT_ADMINISTRATOR.'/controllers/'.$view.'.php';
	$class = 'SigProController'.ucfirst($view);
	$controller = new $class();
	$controller->addViewPath(JPATH_COMPONENT_ADMINISTRATOR.'/views');
	$controller->execute(JRequest::getWord('task'));
	$controller->redirect();
}

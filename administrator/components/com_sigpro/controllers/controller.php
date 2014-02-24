<?php
/**
 * @version		$Id: controller.php 3159 2013-06-03 16:33:48Z lefteris.kavadas $
 * @package		Simple Image Gallery Pro
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.controller');

if (version_compare(JVERSION, '3.0', 'ge'))
{
	class SigProController extends JControllerLegacy
	{
		public function display($cachable = false, $urlparams = array())
		{
			parent::display($cachable, $urlparams);
		}
		
		public function execute($task)
		{
			JLoader::register('SigProHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helper.php');
			if (SigProHelper::checkPermissions($task))
			{
				parent::execute($task);
			}
			else
			{
				return JError::raiseError(403, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
			}
		}

		public function setRedirect($url, $msg = null, $type = null)
		{
			$galleryType = JRequest::getCmd('type', 'site');
			$tmpl = JRequest::getCmd('tmpl', 'index');
			$url .= '&type='.$galleryType.'&tmpl='.$tmpl;
			$editorName = JRequest::getCmd('editorName');
			if ($editorName)
			{
				$url .= '&editorName='.$editorName;
			}
			$template = JRequest::getCmd('template');
			if ($template)
			{
				$url .= '&template='.$template;
			}
			$language = JRequest::getCmd('sigLang');
			if ($language)
			{
				$url .= '&sigLang='.$language;
			}
			parent::setRedirect($url, $msg, $type);
		}

	}

}
elseif (version_compare(JVERSION, '2.5', 'ge'))
{
	class SigProController extends JController
	{
		public function display($cachable = false, $urlparams = false)
		{
			parent::display($cachable, $urlparams);
		}

		public function execute($task)
		{
			JLoader::register('SigProHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helper.php');
			if (SigProHelper::checkPermissions($task))
			{
				parent::execute($task);
			}
			else
			{
				return JError::raiseError(403, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
			}
		}

		public function setRedirect($url, $msg = null, $type = null)
		{
			$galleryType = JRequest::getCmd('type', 'site');
			$tmpl = JRequest::getCmd('tmpl', 'index');
			$url .= '&type='.$galleryType.'&tmpl='.$tmpl;
			$editorName = JRequest::getCmd('editorName');
			if ($editorName)
			{
				$url .= '&editorName='.$editorName;
			}
			$template = JRequest::getCmd('template');
			if ($template)
			{
				$url .= '&template='.$template;
			}
			$language = JRequest::getCmd('sigLang');
			if ($language)
			{
				$url .= '&sigLang='.$language;
			}
			parent::setRedirect($url, $msg, $type);
		}

	}

}
else
{
	class SigProController extends JController
	{
		public function display($cachable = false)
		{
			parent::display($cachable);
		}

		public function setRedirect($url, $msg = null, $type = null)
		{
			$galleryType = JRequest::getCmd('type', 'site');
			$tmpl = JRequest::getCmd('tmpl', 'index');
			$url .= '&type='.$galleryType.'&tmpl='.$tmpl;
			$editorName = JRequest::getCmd('editorName');
			if ($editorName)
			{
				$url .= '&editorName='.$editorName;
			}
			$template = JRequest::getCmd('template');
			if ($template)
			{
				$url .= '&template='.$template;
			}
			$language = JRequest::getCmd('sigLang');
			if ($language)
			{
				$url .= '&sigLang='.$language;
			}
			parent::setRedirect($url, $msg, $type);
		}

	}

}

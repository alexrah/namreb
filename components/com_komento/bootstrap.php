<?php

jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.path' );
jimport( 'joomla.html.parameter' );
jimport( 'joomla.access.access' );

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'constants.php' );
require_once( KOMENTO_HELPERS . DIRECTORY_SEPARATOR . 'version.php' );
require_once( KOMENTO_HELPERS . DIRECTORY_SEPARATOR . 'document.php' );
require_once( KOMENTO_HELPERS . DIRECTORY_SEPARATOR . 'helper.php' );
require_once( KOMENTO_HELPERS . DIRECTORY_SEPARATOR . 'router.php' );
require_once( KOMENTO_CLASSES . DIRECTORY_SEPARATOR . 'comment.php' );

// Load language here
// initially language is loaded in content plugin
// for custom integration that doesn't go through plugin, language is not loaded
// hence, language should be loaded in bootstrap

// Always force load English first as fallback
// JFactory::getLanguage()->load( 'com_komento', JPATH_ROOT, 'en-GB', false, false );

// Load selected language
JFactory::getLanguage()->load( 'com_komento', JPATH_ROOT );

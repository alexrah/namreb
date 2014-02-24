<?php
/**
 * @version		$Id: popup.php 2829 2013-04-12 14:20:40Z joomlaworks $
 * @package		Simple Image Gallery Pro
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$relName = 'swipebox';
$extraClass = 'swipebox';

$stylesheets = array('source/swipebox.css');
$stylesheetDeclarations = array();
$scripts = array('source/jquery.swipebox.min.js');

if(!defined('PE_SWIPEBOX_LOADED')){
	define('PE_SWIPEBOX_LOADED', true);
	$scriptDeclarations = array('
		jQuery.noConflict();
		jQuery(function($) {
			$("a.swipebox").swipebox();
		});
	');
} else {
	$scriptDeclarations = array();
}

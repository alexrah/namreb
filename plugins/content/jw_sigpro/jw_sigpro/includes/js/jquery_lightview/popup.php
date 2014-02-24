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

$relName = 'lightview';
$extraClass = 'lightview';
$customLinkAttributes = 'data-lightview-group="'.$gal_id.'"';

$stylesheets = array('css/lightview/lightview.css');
$stylesheetDeclarations = array();
$scripts = array(
	'js/spinners/spinners.min.js',
	'js/lightview/lightview.js'
);
$scriptDeclarations = array();

if(!defined('PE_LIGHTVIEW_LOADED')){
	define('PE_LIGHTVIEW_LOADED', true);
	$legacyHeadIncludes = '<!--[if lt IE 9]><script type="text/javascript" src="'.$popupPath.'/js/excanvas/excanvas.js"></script><![endif]-->';
} else {
	$legacyHeadIncludes = '';
}

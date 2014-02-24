<?php
/**
 * @version		$Id: popup.php 2824 2013-04-11 20:02:57Z joomlaworks $
 * @package		Simple Image Gallery Pro
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$relName = 'fresco';
$extraClass = 'fresco';
$customLinkAttributes = 'data-fresco-group="'.$gal_id.'"';

$stylesheets = array('css/fresco/fresco.css');
$stylesheetDeclarations = array();
$scripts = array(
	'js/fresco/fresco.js'
);
$scriptDeclarations = array();

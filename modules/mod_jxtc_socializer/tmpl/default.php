<?php
/*
	JoomlaXTC Socializer module

	version 1.0.0
	
	Copyright (C) 2012  Monev Software LLC.	All Rights Reserved.
	
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	
	THIS LICENSE MIGHT NOT APPLY TO OTHER FILES CONTAINED IN THE SAME PACKAGE.
	
	See COPYRIGHT.php for more information.
	See LICENSE.php for more information.
	
	Monev Software LLC
	www.joomlaxtc.com
*/

defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root(true).'/modules/mod_jxtc_socializer/style.css');

$set = $params->get('set', 's');

switch ($set) {
	case 's':
		echo '<div class="jxtcsocial socialbar"><ul class="ss">';
		break;
	case 't':
		echo '<div class="jxtcsocial socialbar_transparent borderless"><ul class="ss">';
		break;
	case 'm':
		echo '<div class="jxtcsocial socialbar_mini borderless"><ul class="ssm">';
		break;
}

$services = array("digg","dribbble","facebook","flickr","forrst","googleplus","html5","icloud","lastfm","linkedin","myspace","paypal",
									"picasa","pinterest","reddit","rss","skype","stumbleupon","tumblr","twitter","vimeo","wordpress","yahoo","youtube");

foreach ($services as $service) {
	if ($params->get($service, 0)) {
		$link = trim($params->get($service.'_link'));
		if ($link) {
			echo '<li class="'.$service.'"><a href="'.$link.'">'.$service.'</a></li>';
		}
	}
}

echo '</div>';
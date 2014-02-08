<?php
/*
	JoomlaXTC Contact Wall

	version 1.5.0

	Copyright (C) 2008-2012 Monev Software LLC.	All Rights Reserved.

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

	THIS LICENSE IS NOT EXTENSIVE TO ACCOMPANYING FILES UNLESS NOTED.

	See COPYRIGHT.php for more information.
	See LICENSE.php for more information.

	Monev Software LLC
	www.joomlaxtc.com
*/

defined( '_JEXEC' ) or die;

jimport( 'joomla.html.parameter' );


$fulltext=strip_tags($item->description);
if (!empty($maxtext))
	$fulltext = Jstring::trim(Jstring::substr($fulltext,0,$maxtext)).$maxtextsuf;

$misc=strip_tags($item->misc);
if (!empty($maxmisc))
	$misc = Jstring::trim(Jstring::substr($misc,0,$maxmisc)).$maxmiscsuf;

$categoryurl = ContactHelperRoute::getCategoryRoute($item->catid);
$contacturl = JRoute::_(ContactHelperRoute::getContactRoute($item->id, $item->catid));
//$contacturl = JRoute::_('index.php?option=com_contact&view=contact&id='.$item->slug.'&catid='.$item->catid.$itemid);

//$categoryimageurl = $live_site.'images/stories/'.$item->catimage;
$contactimageurl = $live_site.'/'.$item->image;

//$categoryimage = '<img src="'.$categoryimageurl.'" alt="'.$item->title.'" />';
$contactimage = '<img src="'.$contactimageurl.'" alt="'.$item->contactname.'" />';

$itemhtml = str_replace( '{categoryurl}', $categoryurl, $itemhtml );
$itemhtml = str_replace( '{contacturl}', $contacturl, $itemhtml );
$itemhtml = str_replace( '{rawdescription}', $item->description, $itemhtml );
$itemhtml = str_replace( '{description}', $fulltext, $itemhtml );
$itemhtml = str_replace( '{categorytitle}', $item->title, $itemhtml );
$itemhtml = str_replace( '{contactname}', $item->contactname, $itemhtml );
$itemhtml = str_replace( '{contactalias}', $item->contactalias, $itemhtml );
$itemhtml = str_replace( '{position}', $item->con_position, $itemhtml );
$itemhtml = str_replace( '{address}', $item->address, $itemhtml );
$itemhtml = str_replace( '{suburb}', $item->suburb, $itemhtml );
$itemhtml = str_replace( '{state}', $item->state, $itemhtml );
$itemhtml = str_replace( '{country}', $item->country, $itemhtml );
$itemhtml = str_replace( '{postcode}', $item->postcode, $itemhtml );
$itemhtml = str_replace( '{telephone}', $item->telephone, $itemhtml );
$itemhtml = str_replace( '{fax}', $item->fax, $itemhtml );
$itemhtml = str_replace( '{rawmiscellanous}', $item->misc, $itemhtml );
$itemhtml = str_replace( '{miscellanous}', $misc, $itemhtml );
$itemhtml = str_replace( '{email_to}', $item->email_to, $itemhtml );
$itemhtml = str_replace( '{mobile}', $item->mobile, $itemhtml );
$itemhtml = str_replace( '{webpage}', $item->webpage, $itemhtml );
$itemhtml = str_replace( '{username}', $item->username, $itemhtml );
$itemhtml = str_replace( '{useralias}', $item->useralias, $itemhtml );
//$itemhtml = str_replace( '{categoryimageurl}', $categoryimageurl, $itemhtml );
//$itemhtml = str_replace( '{categoryimage}', $categoryimage, $itemhtml );
$itemhtml = str_replace( '{contactimageurl}', $contactimageurl, $itemhtml );
$itemhtml = str_replace( '{contactimage}', $contactimage, $itemhtml );
$itemhtml = str_replace( '{index}', $index, $itemhtml  );

?>
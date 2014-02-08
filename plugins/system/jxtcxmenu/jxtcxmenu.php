<?php
/*
	JoomlaXTC X-Menu plugin

	version 1.6.2
	
	Copyright (C) 2009-2012  Monev Software LLC.	All Rights Reserved.
	
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
	
	See COPYRIGHT.php for more information.
	See LICENSE.php for more information.
	
	Monev Software LLC
	www.joomlaxtc.com
*/

defined('JPATH_BASE') or die;

jimport('joomla.plugin.plugin');

class plgSystemjxtcxmenu extends JPlugin {

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	function onAfterInitialise() {
		$app = JFactory::getApplication();
		if ($app->isAdmin()) return;		// Not on frontend

		if (file_exists(JPATH_ROOT.'/plugins/system/jxtcxmenu/xmenu.css')) {
			$live_site = JURI::base();
			$doc = JFactory::getDocument();
			$doc->addStyleSheet($live_site.'plugins/system/jxtcxmenu/xmenu.css','text/css');
		}
	}

	function onAfterRender() {

		$app = JFactory::getApplication();
		if ($app->isAdmin()) return;		// Not on frontend

		$ulid = $this->params->get('ulid');
		$keepdropline = $this->params->get('keepdropline',0);

		$bodyBuffer = JResponse::getBody();

		if ($ulid ) { // work on a tag
			$ulids = trim($ulid) == '*' ? array('<body') : explode(',',$ulid);
			
			foreach ($ulids as $ulid) {
				// identify opening and closing tags
				if (($ulidpos=strpos($bodyBuffer,$ulid)) === false) continue; // No tag found
				$tagini = strrpos($bodyBuffer,'<',((strlen($bodyBuffer)-$ulidpos)*-1));
				$tag = substr($bodyBuffer,$tagini+1,strpos($bodyBuffer,'>',$tagini)-$tagini-1);
				$hold=explode(' ',$tag);
				$starttag = '<'.$hold[0];
				$endtag = '</'.$hold[0];

				if (($tagfin=strpos($bodyBuffer,$endtag,$ulidpos)) === false) continue; // No end tag found
				$tagfin += strlen($endtag);
		
				// get work buffer

				while (substr_count(substr($bodyBuffer,$tagini,$tagfin-$tagini),$starttag) != substr_count(substr($bodyBuffer,$tagini,$tagfin-$tagini),$endtag)) {
					if (($tagfin=strpos($bodyBuffer,$endtag,$tagfin+1)) === false) { continue 2; }
					$tagfin += strlen($endtag);
				}

				$temp = substr($bodyBuffer,$tagini,$tagfin-$tagini); // get work substring from buffer

				// Do module positions
				while (($ini = strpos($temp,"{xm ")) !== false) {
		
					$fin = strpos($temp,"}",$ini);
					$parms=substr($temp,$ini+4,$fin-$ini-4);
					list($position,$width,$height)=explode(',',$parms);
					if (!empty($position)) {
						settype($width,'integer');
						settype($height,'integer');
						$width = $width==0 ? '' : 'width:'.$width.'px;';
						$height = $height==0 ? '' : 'height:'.$height.'px;';
						$modules = JModuleHelper::getModules($position);
						$attribs = array('style'=>'xhtml');
						$positionhtml = "<div class=\"xmenu_position $position\" style=\"$width $height\">";
						foreach ($modules as $module) {
							$positionhtml .= JModuleHelper::renderModule( $module, $attribs );
						}
						$positionhtml .= "</div>";
					}
					else {
						$positionhtml = '';
					}
					$temp = substr_replace($temp,$positionhtml,$ini,$fin-$ini+1);
				}
		
				// Do double lines
				$temp = str_replace( '{xm}', '<span class=\'xmenu\'>', $temp );
				$temp = str_replace( '{/xm}', '</span>', $temp );
		
				$bodyBuffer = substr_replace($bodyBuffer,$temp,$tagini,$tagfin-$tagini); // Update buffer with changes
			}
		}

		// Cleanup any remaining tags from buffer (eg HEAD and menu titles)

		while (($ini = strpos($bodyBuffer,"{xm ")) !== false) { // Clean module positions
			$fin = strpos($bodyBuffer,"}",$ini);
			$bodyBuffer = substr_replace($bodyBuffer,'',$ini,$fin-$ini+1);
		}
	
		if ($keepdropline) {	// make droplines as simple text
			$bodyBuffer = str_replace( '{xm}', ' ', $bodyBuffer );
			$bodyBuffer = str_replace( '{/xm}', ' ', $bodyBuffer );
		}
		else {	// (Delete droplines)
			while (($ini = strpos($bodyBuffer,"{xm}")) !== false) {
				if (($fin = strpos($bodyBuffer,"{/xm}",$ini)) !== false) {
					$bodyBuffer = substr_replace($bodyBuffer,'',$ini,$fin-$ini+5);
				}
			}
		}

		JResponse::setBody($bodyBuffer); // Update document buffer
	}
}
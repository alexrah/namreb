<?php
/*
	JoomlaXTC Virtuemart Video Player Plugin

	version 1.1.2
	
	Copyright (C) 2009,2010,2011 Monev Software LLC.	All Rights Reserved.
	
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

jimport('joomla.plugin.plugin');

class plgContentjxtcxtream extends JPlugin {

	function onContentPrepare( $context, &$article, &$params, $limitstart ) {
  	global $app;

		$boole=array('false','true');
		$live_site = JURI::base();
		$folder = $this->params->get('folder');
		$width = $this->params->get('width',200);
		$height = $this->params->get('height',150);
		$wmode = $this->params->get('wmode','transparent');
		$bgcolor = $this->params->get('bgcolor','#FFFFFF');
		$autoplay = $this->params->get('autoplay','1');
		$fullscreen = $this->params->get('fullscreen','1');

		while (($ini = strpos($article->text,"{video")) !== false) {
			$fin = strpos($article->text,"}",$ini);
			$filter=trim(substr($article->text,$ini+6,$fin-$ini-6));
			$vparms=explode(',',$filter);
			$type = (isset($vparms[0]) ? $vparms[0] : '');
			$file = (isset($vparms[1]) ? $vparms[1] : '');
			$fwidth = isset($vparms[2]) && !empty($vparms[2]) ? $vparms[2] : $width;
			$fheight = isset($vparms[3]) && !empty($vparms[3]) ? $vparms[3] : $height;
			$fautoplay = isset($vparms[4]) && !empty($vparms[4]) ? $vparms[4] : $autoplay;
			$fbgcolor = isset($vparms[5]) && !empty($vparms[5]) ? $vparms[5] : $bgcolor;
			$fwmode = isset($vparms[6]) && !empty($vparms[6]) ? $vparms[6] : $wmode;
			$extraflashvars = isset($vparms[7]) ? $vparms[7] : '';
			switch ($type) {
				case 'file':  // beta
					$file_url = empty($folder) ? $live_site.'media/'.$file : $live_site."media/$folder/".$file;
					$player = $live_site.'plugins/content/plugin_jxtc_xtream/playerLite.swf';
					$player_parms = '';
					$flashvars = 'vidWidth='.$width.'&vidHeight='.$height.'&vidPath='.urlencode($file_url).'&autoPlay='.$boole[$fautoplay].'&watermark=hide&seekbar=hide';
					$object_parms = '';
					$embed_parms = '';
				break;
				case 'youtube':
					$player = 'http://www.youtube.com/v/'.$file;
					$player_parms = '&autoplay='.$fautoplay;
					$flashvars = '';
					$object_parms = '';
					$embed_parms = '';
				break;
				case 'yahoo':
					$player = 'http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf';
					$player_parms = '';
					$flashvars = 'id=15326624&autoplay='.$fautoplay;
					$object_parms = '';
					$embed_parms = '';
				break;
				case 'google':
					$player = 'http://video.google.com/googleplayer.swf?docid='.$file;
					$player_parms = '&autoPlay='.$boole[$fautoplay];
					$flashvars = '';
					$object_parms = '';
					$embed_parms = '';
				break;
				case 'metacafe':
					$player = 'http://www.metacafe.com/fplayer/'.$file;
					$player_parms = '';
					$flashvars = '&playerVars=autoPlay='. (($fautoplay == 1) ? 'yes' : 'no');
					$object_parms = '';
					$embed_parms = '';
				break;
				case 'vimeo':
					$player = 'http://vimeo.com/moogaloop.swf?clip_id='.$file;
					$player_parms = '&amp;show_title=1&amp;show_byline=1&amp;fullscreen='.$fullscreen.'&amp;autoplay='.$fautoplay;
					$flashvars = '';
					$object_parms = '';
					$embed_parms = '';
				break;
			}
			$data = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" width="'.$fwidth.'" height="'.$fheight.'">
		 <param name=movie value="'.$player.$player_parms.'">
		 <param name=quality value="high" />
		 <param name=bgcolor value="'.$fbgcolor.'" />
		 <param name="play" value="'.$fautoplay.'" />
		 <param name="autoplay" value="'.$fautoplay.'" />
		 <param name="wmode" value="'.$fwmode.'" />
		 <param name="allowFullScreen" value="'.$boole[$fullscreen].'" />
		 <param name="AllowScriptAccess" value="always" />'.$object_parms;
		 if ($flashvars) {
		 	$data .= '<param name="FlashVars" value="'.$flashvars.$extraflashvars.'" />';
		 }
		 $data .= '<embed src="'.$player.$player_parms
		 .'" width="'.$fwidth
		 .'" height="'.$fheight
		 .'" quality="high" bgcolor="'.$fbgcolor
		 .'" wmode="'.$fwmode
		 .'" play="'.$fautoplay
		 .'" autoplay="'.$fautoplay
		 .'" allowFullScreen="'.$boole[$fullscreen]
		 .'" AllowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" '.$embed_parms;
			if ($flashvars || $extraflashvars) {
				$data .= ' FlashVars="'.$flashvars.$extraflashvars.'"';
			}
			$data .= '></embed></object>';
			$article->text = substr_replace($article->text,$data,$ini,$fin-$ini+1);
		}
	}
}
?>
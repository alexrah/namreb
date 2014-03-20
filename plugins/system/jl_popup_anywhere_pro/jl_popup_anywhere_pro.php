<?php
/**
 * @version		$Id: $
 * @author		Mr.LongAnh
 * @package		Joomla!
 * @subpackage	Plugin
 * @copyright	Copyright (C) 2008 - 2011 by Codextension. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL version 3
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgSystemJL_Popup_Anywhere_Pro extends JPlugin{

	function plgSystemJL_Popup_Anywhere_Pro(& $subject, $config){
		parent::__construct($subject, $config);
	}
	function onContentPrepareForm( $form, $data )	{
		if($form->getName()=='com_menus.item'){
                        $jlang = JFactory::getLanguage();
                        $jlang->load('plg_system_jl_popup_anywhere_pro', JPATH_ADMINISTRATOR, null, true);
			JForm::addFormPath( dirname(__FILE__).DS."admin".DS."menu" );
			$form->loadFile('params', false);
		}
	}	
	
	function onAfterDispatch(){
		
		$mainframe = JFactory::getApplication();
		
		if($mainframe->isAdmin()) {
			return;
		}
		
		$allpage	= $this->params->get('allpage','1');
		$preview = JRequest::getVar('jl_popup_anywhere_pro_preview',false);
		if( !$allpage && !$preview ){
			// check list menu items
			$menuid = $this->params->get('menuid','0');
			$Itemid = JRequest::getInt('Itemid','0');
			$lang = JFactory::getLanguage()->getTag();
			if( !$menuid || !$Itemid || !$this->checkMenuAlias($Itemid, $menuid) ){
				return;
			}
			$this->checkPopupAnywhereMenuItem($Itemid);
		}
		$popupWidth = intval($this->params->get('popupwidth','500'));
		$popupHeight = intval($this->params->get('popupheight','500'));
		
		$x = '';
		if (trim($popupWidth)!=''){
			$x = "x: ".intval($popupWidth).' ';
		}
		
		$y = '';
		if (trim($popupHeight)!=''){
			$y = "y: ".intval($popupHeight);
		}
		
		$modaltype = intval($this->params->get('modaltype',0));
		if ($modaltype==0){
			$modaltype='iframe';
		}else if ($modaltype==1){
			$modaltype='url';
		}else{
			$modaltype='image';
		}
		
		$selectpopup = $this->params->get('selectpopup','0');
		$image = $this->params->get('image','');
		if( $selectpopup=='1' ){ // image
			$link = $image;
			$modaltype = 'image';
		}else if($selectpopup=='2'){//module
			$listmodule = $this->params->get('listmodule','0');
			if( !$listmodule ){
				return;
			}
			$link = 'index.php?option=com_popup_anywhere&tmpl=component&id='.$listmodule;
		}else{ // article/url
			// auto convert modaltype from image to iframe
			if( $modaltype=='image' ){
				$modaltype = 'iframe';
			}
			$link = $this->params->get('link','');
			if (trim($link=='')){
				return;
			}
			/*CHECK POPUP IN IFRAME*/
				if( strpos($link,"?") && !strpos($link,"popupiniframe") ){
					$link =$link."&popupiniframe=true";
				}else{
					if( !strpos($link,"popupiniframe") ){
						$link =$link."?popupiniframe=true";
					}
				}
				$popupiniframe = JRequest::getVar("popupiniframe","0");
				if( $popupiniframe ){
					return;
				}
			/*CHECK POPUP IN IFRAME*/
		}
		
		
		$addUrl = $this->params->get('addurl',1);
		if ($addUrl==1&&strtolower(substr($link,0,7))!='http://'){
			$link=JURI::base().$link;
		}
		
		$type = intval($this->params->get('type',0));
		$cookietime = intval($this->params->get('cookietime',0));
		
		$session =&JFactory::getSession();
		
		//check endless
		
		$par = array();
		
		
		$session =&JFactory::getSession();
		
		if ($preview=='true'){
			$preview = true;
			$session->set('modaldone','true','plg_jl_popup_anywhere_pro');
			setcookie('modaldone', 'true',time() + 300*3600,'/');
		}else{			
			$preview = false;
		}
		
		
		$time = $cookietime;		
		if ($cookietime>0){
			$time = time()+($cookietime*3600);
		}
		
		if (!$preview){
			if ($type==0){			
				$value = $session->get('modaldone',null,'plg_jl_popup_anywhere_pro');
				$session->set('modaldone','true','plg_jl_popup_anywhere_pro');
				setcookie('modaldone', 'false',$time,'/'); 
				if ($value=='true'){
					return;
				}
			}else if ($type==1){
				if (isset($_COOKIE['modaldone'])){
					$value = $_COOKIE['modaldone'];
				}else{
					$value = 'false';
				}
				setcookie('modaldone', 'true',$time,'/');
				if ($value=='true'){
					return;
				}
			}else{
				
			}
		}
		
		
		//add pop up Javascript to document
		
		//JHTML::_('behavior.mootools');
		jHTML::_('behavior.modal');

		$sizeStr = '';
		if ($popupWidth>0&&$popupHeight==0){
			$sizeStr = ", size: {".$x."}";
		}elseif ($popupWidth==0&&$popupHeight>0){
			$sizeStr = ", size: {".$y."}";
		}elseif ($popupWidth>0&&$popupHeight>0){
			$sizeStr = ", size: {".$x.", ".$y."}";
		}
		
		$linkofimage = $this->params->get('linkofimage','');
		
		$noconflict = intval($this->params->get('enableNoConflict',0));
		
		$nc = '$';
		if ($noconflict==1){
			$nc = 'document.id';
		}
		
		
		$closeafter = intval($this->params->get('closeafterseconds',0));
                $loadafter  = intval($this->params->get('loadafterseconds',0));
		$jsCloseString = '';
                $jsLoadString   = '';
		if ($closeafter>0){
			$jsCloseString = 'setTimeout(\'popupAnywherecloseWindow();\','.($closeafter*1000).');';
		}
        if ($loadafter>0){
			$jsLoadString = $loadafter*1000;
		}
		
		$jsimagelink = '';
		
		$document =& JFactory::getDocument();
		
		if ( trim($linkofimage)!='' && $modaltype=='image' ){
			$linktarget = intval($this->params->get('linktarget',0));
			$locationJs = '';
			switch($linktarget){
				case 0:
					$locationJs = "parent.window.location.href='".$linkofimage."'";
				break;
				case 1:
					$locationJs = "document.forms['popupAnywhereForm'].submit();";
				break;
				case 2:
					$locationJs = "location.href='".$linkofimage."'";
				break;
			}
			
			if ($modaltype=='iframe'){
				$jsimagelink = "$$('.sbox-content-iframe iframe').setProperty('id','popupanywhereIframeId');IframeOnClick.track(".$nc."('popupanywhereIframeId'), function() { ".$locationJs.";});";
				$document->addScript( JURI::base().'plugins/system/jl_popup_anywhere_pro/js/iframetracker.js' );
			}else{
				$jsimagelink = $nc."('sbox-content').addEvent('click', function(){".$locationJs.";});";
			}
			
		}
		
		// if mootool < 1.2 , use $$ => noproblem , but use $ problem with jQuery
		// if mootool >=1.2 , use $$ => noproblem , but use $ problem with jQuery , and have to use document.id(only support mootool 1.2)
		
		$noscroll = '';
		if (intval($this->params->get('scrollbar',1))==0&&$modaltype!='image'){		
			if ($modaltype=='iframe'){
				$noscroll = '$$(\'.sbox-content-iframe iframe\').setProperty(\'scrolling\',\'no\');';
			}elseif($modaltype=='url'){
				$noscroll = $nc.'(\'sbox-content\').setStyle(\'overflow\',\'hidden\');';
			}
		}
		$nobutton = '';
		if (intval($this->params->get('closebutton',1))==0){
			$nobutton = $nc.'(\'sbox-btn-close\').setStyle(\'display\',\'none\');';
		}
		$onUpdate = '';
		if ($jsCloseString!=''||$jsimagelink!=''||$noscroll!=''||$nobutton!=''){
			$onloadwrapper = '{placeholder}';
			if ($modaltype=='iframe'){
				$onloadwrapper = "$$('.sbox-content-iframe iframe').addEvent('load', function(){{placeholder};});"; 
			}
			/*if ($modaltype=='url'){
				$onloadwrapper = $nc."('page').addEvent('domready', function(){{placeholder};});"; 
			}
			if ($modaltype=='image'){
				$onloadwrapper = "$$('#sbox-content img').setProperty('onload', 'alert(\\\'yes\\\')');"; 
			}*/
			$addOnLoad = str_replace('{placeholder}',$jsCloseString.$jsimagelink,$onloadwrapper);
			$onUpdate = ", onUpdate:function(){".$noscroll.$nobutton.$addOnLoad."}";
		}
		
		$globals = '';
		
		if ($jsCloseString!=''){
			$globals = 'function popupAnywherecloseWindow(){
				SqueezeBox.close();
			}
			';
		}
		$options = "handler: '".$modaltype."'".$sizeStr.$onUpdate;
		
		
		$js = "
	SqueezeBox.open('".$link."',{".$options."});";
		
		
		
		$document->addScriptDeclaration($this->_wrapJS($js,true,$globals,$jsLoadString));
		
		$cursor = $this->params->get('cursor','');
		if (trim($cursor)!=''){
			if ($modaltype!='iframe'){
				$document->addStyleDeclaration('#sbox-window{cursor:'.$cursor.' !important}', 'text/css');
			}
		}
		
		$template = $this->params->get('template','');
		
		if ($preview){
			$session->set('modaldone','true','plg_jl_popup_anywhere_pro');
			setcookie('modaldone', 'true', time() + (100 * 60 * 60),JURI::base(),'/');
		}
		
		if (trim($template)==''||$template==null||intval($template)==-1){
			return;
		}
		$document->addStyleSheet( JURI::root().'plugins/system/jl_popup_anywhere_pro/css/'.$template, 'text/css', null, array() );
	}
	
	/**
	 * Wraps Javascript
	 *
	 * @param string $js main Javascript
	 * @param bool $onload add mootools window.addEvent('load'...
	 * @param string $globals if $onload true -> $globals will be inserted between script tag and mootools window.addEvent
	 * @return string wrapped javascript
	 */

	function _wrapJS($js, $onload=false, $globals='',$jsLoadString=''){
		if ($onload==false) return $js;
		// remove iframe with ie fix flash file video
			$ie = "$('sbox-overlay').addEvent('click', function(){ $$('#sbox-window #sbox-content iframe').dispose(); }); $('sbox-btn-close').addEvent('click', function(){ $$('#sbox-window #sbox-content iframe').dispose(); });";
                        if( !$jsLoadString ){
                            return $globals.'
                            window.addEvent("load", function() {'.$js.$ie.'
                            });';
                        }else{
                            return $globals.'
                                window.addEvent("load", function() {setTimeout("'.trim($js).$ie.'",'.$jsLoadString.');
                            });';
                        }
	}
	function checkMenuAlias( $Itemid, $menuid ){
		if( in_array($Itemid, $menuid) ){
			return true;
		}else{
			$menu = JSite::getMenu();
			$boll = false;
			if( !empty($menuid) ){
				$i=0;
				foreach( $menuid as $m ){
					$menuitem = $menu->getItem($m);
					if( !count($menuitem) || $menuitem->type!='alias' || !$menuitem->params->get('aliasoptions') ){
						$i++;
						continue;
					}
					$menuid[$i] = $menuitem->params->get('aliasoptions');
					
					if( in_array($Itemid, $menuid) ){
						$boll = true;
						break;
					}
					$i++;
				}
			}
			return $boll;
		}
	}
	function checkPopupAnywhereMenuItem($Itemid){
            // check param popup anywhere in menu item
            $menu = JSite::getMenu();
            $menuitem = $menu->getItem($Itemid);
            $boll_overide = false;
            if( $menuitem->params->get('selectpopup')=='0' && trim($menuitem->params->get('link')) ){
                $this->params->set('selectpopup',$menuitem->params->get('selectpopup'));
                $this->params->set('link',trim($menuitem->params->get('link')));
                $boll_overide = true;
            }else if( $menuitem->params->get('selectpopup')=='1' && trim($menuitem->params->get('image')) ){
                $this->params->set('selectpopup',$menuitem->params->get('selectpopup'));
                $this->params->set('image',trim($menuitem->params->get('image')));
                $boll_overide = true;
            }else if( $menuitem->params->get('selectpopup')=='2' && $menuitem->params->get('listmodule') ){
                $this->params->set('selectpopup',$menuitem->params->get('selectpopup'));
                $this->params->set('listmodule',trim($menuitem->params->get('listmodule')));
                $boll_overide = true;
            }
            if( $boll_overide == true ){
                // overide popup anywhere from menu item
                if( $menuitem->params->get('popupwidth') ){
                    $this->params->set('popupwidth',$menuitem->params->get('popupwidth'));
                }
                if( $menuitem->params->get('popupheight') ){
                    $this->params->set('popupheight',$menuitem->params->get('popupheight'));
                }
                if( $menuitem->params->get('template')!='-1' ){
                    $this->params->set('template',$menuitem->params->get('template'));
                }
                $this->params->set('cookietime',$menuitem->params->get('cookietime'));
                $this->params->set('type',$menuitem->params->get('type'));
                $this->params->set('modaltype',$menuitem->params->get('modaltype'));
                $this->params->set('closeafterseconds',$menuitem->params->get('closeafterseconds'));
                $this->params->set('loadafterseconds',$menuitem->params->get('loadafterseconds'));
                $this->params->set('linkofimage',$menuitem->params->get('linkofimage'));
                $this->params->set('linktarget',$menuitem->params->get('linktarget'));
                $this->params->set('cursor',$menuitem->params->get('cursor'));
                $this->params->set('closebutton',$menuitem->params->get('closebutton'));
                $this->params->set('scrollbar',$menuitem->params->get('scrollbar'));
            }
        }
	function onAfterRender(){
		$mainframe = JFactory::getApplication();
		if($mainframe->isAdmin()) {
			return;
		}
		
		$linkofimage = $this->params->get('linkofimage','');
		$linktarget = intval($this->params->get('linktarget',0));
		if ($linktarget==1&&trim($linkofimage)!=''){
			$html = JResponse::getBody();
			$html = str_replace('</body>','<form action="'.$linkofimage.'" target="_blank" name="popupAnywhereForm"></form></body>',$html);
			$html = JResponse::setBody($html);
		}
	}
}



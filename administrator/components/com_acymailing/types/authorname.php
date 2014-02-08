<?php
/**
 * @package	Acymailing for Joomla!
 * @version	4.0.1
 * @author	acyba.com
 * @copyright	(C) 2009-2012 ACYBA S.A.RL. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class authornameType{
	var $onclick = "updateTag();";
	function authornameType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', "|author",JText::_('JOOMEXT_YES'));
		$this->values[] = JHTML::_('select.option', "",JText::_('JOOMEXT_NO'));

	}

	function display($map,$value){
		return JHTML::_('acyselect.radiolist', $this->values, $map , 'size="1" onclick="'.$this->onclick.'"', 'value', 'text', (string) $value);
	}

}

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

class contentfilterType{
	var $onclick = 'updateTag();';
	function contentfilterType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', "",JText::_('ACY_ALL'));
		$this->values[] = JHTML::_('select.option', "|filter:created",JText::_('ONLY_NEW_CREATED'));
		$this->values[] = JHTML::_('select.option', "|filter:modify",JText::_('ONLY_NEW_MODIFIED'));

	}

	function display($map,$value){
		return JHTML::_('select.genericlist', $this->values, $map , 'size="1" onchange="'.$this->onclick.'" style="max-width:200px;"', 'value', 'text', (string) $value);
	}

}

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
jimport( 'joomla.html.parameter' );

class acymailingView extends JView{

}

class acymailingControllerCompat extends JController{

}

function acymailing_loadResultArray(&$db){
	return $db->loadResultArray();
}

function acymailing_loadMootools(){
	JHTML::_('behavior.mootools');
}

function acymailing_getColumns($table){
	$db = JFactory::getDBO();
	$allfields = $db->getTableFields($table);
	return reset($allfields);
}

function acymailing_getEscaped($value, $extra = false) {
	$db = JFactory::getDBO();
	return $db->getEscaped($value, $extra);
}

function acymailing_getFormToken() {
	return JUtility::getToken();
}

JHTML::_('select.booleanlist','acymailing');
class acyParameter extends JParameter{}
class JHtmlAcyselect extends JHTMLSelect{}

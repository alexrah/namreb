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

class StatsController extends acymailingController{

	var $aclCat = 'statistics';

	function detaillisting(){
		if(!$this->isAllowed('statistics','manage')) return;
		JRequest::setVar( 'layout', 'detaillisting'  );
		return parent::display();
	}

	function unsubscribed(){
		if(!$this->isAllowed('statistics','manage')) return;
		JRequest::setVar( 'layout', 'unsubscribed'  );
		return parent::display();
	}

	function forward(){
		if(!$this->isAllowed('statistics','manage')) return;
		JRequest::setVar( 'layout', 'forward'  );
		return parent::display();
	}

	function unsubchart(){
		if(!$this->isAllowed('statistics','manage')) return;
		JRequest::setVar( 'layout', 'unsubchart'  );
		return parent::display();
	}


	function remove(){
		if(!$this->isAllowed('statistics','delete')) return;
		JRequest::checkToken() or die( 'Invalid Token' );

		$cids = JRequest::getVar( 'cid', array(), '', 'array' );

		$class = acymailing_get('class.stats');
		$num = $class->delete($cids);

		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS',$num), 'message');

		return $this->listing();
	}

	function export(){
		$selectedMail = JRequest::getInt('filter_mail',0);
		$selectedStatus = JRequest::getString('filter_status','');

		$filters = array();
		if(!empty($selectedMail)) $filters[] = 'a.mailid = '.$selectedMail;
		if(!empty($selectedStatus)){
			if($selectedStatus == 'bounce') $filters[] = 'a.bounce > 0';
			elseif($selectedStatus == 'open') $filters[] = 'a.open > 0';
			elseif($selectedStatus == 'notopen') $filters[] = 'a.open < 1';
			elseif($selectedStatus == 'failed') $filters[] = 'a.fail > 0';
		}

		$query = 'SELECT `subid` FROM `#__acymailing_userstats` as a ';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (',$filters).')';

		$currentSession = JFactory::getSession();
		$currentSession->set('acyexportquery',$query);

		$this->setRedirect(acymailing_completeLink('data&task=export&sessionquery=1',false,true));
	}

}

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
	$db = JFactory::getDBO();
	$db->setQuery('SELECT count(*) FROM '.acymailing_table('communicator_subscribers',false));
	$resultUsers = $db->loadResult();

	echo JText::sprintf('USERS_IN_COMP',$resultUsers,'Communicator');

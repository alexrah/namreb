<?php
/**
* @package		Komento
* @copyright	Copyright (C) 2012 Stack Ideas Private Limited. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Komento is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Restricted access');

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'parent.php' );

class KomentoTableCaptcha extends KomentoParentTable
{
	public $id			= null;
	public $response	= null;
	public $created		= null;

	/**
	 * Constructor for this class.
	 *
	 * @return
	 * @param object $db
	 */
	public function __construct(& $db )
	{
		parent::__construct( '#__komento_captcha' , 'id' , $db );
	}

	/**
	 * Verify response
	 * @param	$response	The response code given.
	 * @return	boolean		True on success, false otherwise.
	 **/
	public function verify( $response )
	{
		return $this->response == $response;
	}

	/**
	 * Delete the outdated entries.
	 */
	public function clear()
	{
	    $db = Komento::getDBO();
	    $date 	= Komento::getDate();

	    $query  = 'DELETE FROM `#__komento_captcha` WHERE `created` <= DATE_SUB( ' . $db->Quote( $date->toMySQL() ) . ', INTERVAL 12 HOUR )';
	    $db->setQuery($query);
	    $db->query();

	    return true;
	}
}

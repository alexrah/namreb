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

// No direct access
defined('_JEXEC') or die('Restricted access');

// Always load abstract class by uncommenting the following line
// require_once( JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_komento' . DIRECTORY_SEPARATOR . 'komento_plugins' . DIRECTORY_SEPARATOR .'abstract.php' );

// Load component dependent files
// require_once( your component's files );

abstract class KomentoExtension
{
	const APIVERSION = '1.3';

	/**
	 * The extension name
	 * @var string
	 */
	public $component = null;

	/**
	 * The main article object
	 * @var mixed
	 */
	public $_item = null;

	/**
	 * Article properties mapping
	 *
	 * @var    array
	 */
	public $_map = array(
		// not needed with custom getContentId()
		'id'			=> 'id',

		// not needed with custom getContentTitle()
		'title'			=> 'title',

		// not needed with custom getContentHits()
		'hits'			=> 'hits',

		// not needed with custom getAuthorId()
		'created_by'	=> 'created_by',

		// not needed with custom getCategoryId()
		'catid'			=> 'catid',

		// not needed with custom getContentPermalink()
		'permalink'		=> 'permalink'
		);

	// START: ABSTRACT FUNCTIONS
	// NECESSARY COMPONENT CORE FUNCTIONS

	/**
	 * Method to load a plugin object by content id number
	 *
	 * @access	public
	 *
	 * @return	object	Instance of this class
	 */
	abstract public function load( $cid );

	/**
	 * Method to get content's ID based on category filter
	 *
	 * @access	public
	 *
	 * @param	string/array $categories Category Ids
	 * @return	array	The IDs of the article
	 */
	abstract public function getContentIds( $categories = '' );

	/**
	 * Method to get a list of categories
	 *
	 * @access	public
	 *
	 * @return	array	array of category object with id, title, level, parent_id as key
	 */
	abstract public function getCategories();

	/**
	 * Method to check if the current view is listing view
	 *
	 * @access	public
	 *
	 * @return	boolean	True if it is listing view
	 */
	abstract public function isListingView();

	/**
	 * Method to check if the current view is entry view
	 *
	 * @access	public
	 *
	 * @return	boolean	True if it is entry view
	 */
	abstract public function isEntryView();

	/**
	 * Method to append the comment to the article
	 *
	 * @access	public
	 *
	 * @param	object	$article	The article object
	 * @param	string	$html		The comment in HTML
	 * @param	string	$view		The current view
	 * @param	array	$options	Parameter key
	 *
	 * @return	void
	 */
	abstract public function onExecute( &$article, $html, $view, $options = array() );

	// END: ABSTRACT FUNCTIONS



	// EXTENDED FUNCTIONS. NOT NECESSARY.
	/**
	 * Initialize the plugin
	 */
	public function __construct( $component )
	{
		$this->component	= $component;
	}

	/**
	 * Method to get the name of the current API version number
	 *
	 * @access	public
	 *
	 * @return	string	The version number
	 */
	public function getAPIVersion()
	{
		return self::APIVERSION;
	}

	/**
	 * Method to get the name of current component
	 *
	 * @access	public
	 *
	 * @return	string	This component's name
	 */
	public function getComponentName()
	{
		return $this->component;
	}

	/**
	 * Method to prepare a proper link
	 *
	 * @access	public
	 *
	 * @param	string	$link		The unprocessed link
	 * @param	array	$params		Parameter key
	 *
	 */
	public function prepareLink( $link )
	{
		$link = JRoute::_( $link );

		// remove relatiave path if exist
		$relpath = JURI::root( true );
		if( $relpath != '' && strpos( $link, $relpath ) === 0 )
		{
			$link = substr( $link, strlen( $relpath ) );
		}

		// backend or frontend, remove administrator from link
		if( strpos( $link, '/administrator/' ) === 0 )
		{
			$link = substr( $link, 14 );
		}

		$link = rtrim( JURI::root(), '/' ) . '/' . ltrim( $link, '/' );

		return $link;
	}

	/**
	 * Method to get allowed trigger to run Komento
	 *
	 * @access	public
	 *
	 * @return	boolean/string	true if no trigger check is needed, string if specified a specific trigger
	 */

	public function getEventTrigger()
	{
		return true;
	}

	/**
	 * Method to get content's ID
	 *
	 * @access	public
	 *
	 * @return	integer	The ID of the article
	 */
	public function getContentId()
	{
		return $this->_item->{$this->_map['id']};
	}

	/**
	 * Method to get content's title
	 *
	 * @access	public
	 *
	 * @return	string	The title of the article
	 */
	public function getContentTitle()
	{
		return $this->_item->{$this->_map['title']};
	}

	/**
	 * Method to get content's hits count
	 *
	 * @access	public
	 *
	 * @return	string	The hits count of the article
	 */
	public function getContentHits()
	{
		return $this->_item->{$this->_map['hits']};
	}

	/**
	 * Method to get content's permalink
	 *
	 * @access	public
	 *
	 * @return	string	The permalik tho the article
	 */
	public function getContentPermalink()
	{
		return $this->_item->{$this->_map['permalink']};
	}

	/**
	 * Method to get article's category ID.
	 * If category is not applicable, return true
	 *
	 * @access	public
	 *
	 * @return	Integer	Category ID
	 */
	public function getCategoryId()
	{
		return $this->_item->{$this->_map['catid']};
	}

	/**
	 * Method to get author's ID
	 *
	 * @access	public
	 *
	 * @return	integer	The ID of the article's creator
	 */
	public function getAuthorId()
	{
		return $this->_item->{$this->_map['created_by']};
	}

	/**
	 * Method to get author's display name
	 *
	 * @access	public
	 *
	 * @return	string	The name of the article's creator
	 */
	public function getAuthorName()
	{
		return JFactory::getUser( $this->getAuthorId() )->name;
	}

	/**
	 * Method to get author's avatar
	 *
	 * @access	public
	 *
	 * @return	string	The avatar of the article's creator
	 */
	public function getAuthorAvatar()
	{
		return '';
	}

	/**
	 * Method to get custom anchor link to work with comment section jump
	 *
	 * @access	public
	 *
	 * @return	string	The anchor id of the comment section.
	 */
	public function getCommentAnchorId()
	{
		return '';
	}

	/**
	 * Prepare the data if necessary before the checking
	 *
	 * @access	public
	 *
	 * @param	string	$eventTrigger	The event trigger
	 * @param	string	$context		Context
	 * @param	object	$article		The article
	 * @param	array	$params			Parameter key
	 * @param	array	$page			Parameter key
	 * @param	array	$options		Parameter key
	 *
	 * @return	boolean	True if success
	 */
	public function onBeforeLoad( $eventTrigger, $context, &$article, &$params, &$page, &$options )
	{
		return true;
	}

	/**
	 * After the loading the content article with id
	 *
	 * @access	public
	 *
	 * @param	string	$eventTrigger	The event trigger
	 * @param	string	$context		Context
	 * @param	object	$article		The article
	 * @param	array	$params			Parameter key
	 * @param	array	$page			Parameter key
	 * @param	array	$options		Parameter key
	 *
	 * @return	boolean	True if success
	 */
	public function onAfterLoad( $eventTrigger, $context, &$article, &$params, &$page, &$options )
	{
		return true;
	}

	/**
	 * Roll back passed by reference
	 *
	 * @access	public
	 *
	 * @param	string	$eventTrigger	The event trigger
	 * @param	string	$context		Context
	 * @param	object	$article		The article
	 * @param	array	$params			Parameter key
	 * @param	array	$page			Parameter key
	 * @param	array	$options		Parameter key
	 *
	 * @return	boolean	True if success
	 */
	public function onRollBack( $eventTrigger, $context, &$article, &$params, &$page, &$options )
	{
		return true;
	}
}

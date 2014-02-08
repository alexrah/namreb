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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

if(!defined('DS')) {
	define('DS',DIRECTORY_SEPARATOR);
}

class KomentoInstaller
{
	private $jinstaller		= null;
	private $manifest		= null;
	private $messages		= array();
	private $db				= null;
	private $installPath	= null;
	private $joomlaVersion	= null;

	public function __construct( JInstaller $jinstaller )
	{
		$this->db			= KomentoDBHelper::getDBO();
		$this->jinstaller	= $jinstaller;
		$this->manifest		= $this->jinstaller->getManifest();
		$this->installPath	= $this->jinstaller->getPath('source');
		$this->joomlaVersion= $this->getJoomlaVersion();
		$this->komentoComponentId = $this->getKomentoComponentId();
	}

	public function execute()
	{
		if( !$this->checkTables() )
		{
			$this->setMessage( 'Warning : ' . $this->db->getErrorMsg() );
			return false;
		}

		if( !$this->checkDB() )
		{
			$this->setMessage( 'Warning : The system encounter an error when it tries to update the database. Please kindly update the database manually.', 'warning' );
		}

		if( !$this->checkKonfig() )
		{
			$this->setMessage( 'Warning : The system encounter an error when it tries to create default konfig. Please kindly configure Komento manually.', 'warning' );
		}

		if( !$this->checkConfig() )
		{
			$this->setMessage( 'Warning : The system encounter an error when it tries to create default config. Please kindly configure Komento manually.', 'warning' );
		}

		if( !$this->checkACL() )
		{
			$this->setMessage( 'Warning : The system encounter an error when it tries to create default ACL settings. Please kindly configure ACL manually.', 'warning' );
		}

		if( !$this->checkMenu() )
		{
			$this->setMessage( 'Warning : The system encounter an error when it tries to update the menu item. Please kindly update the menu item manually.', 'warning' );
		}

		$this->checkAdminMenu();

		if( !$this->checkPlugins() )
		{
			$this->setMessage( 'Warning : The system encounter an error when it tries to install the user plugin. Please kindly install the plugin manually.', 'warning' );
		}

		if( !$this->checkMedia() )
		{
			$this->setMessage( 'Warning: The system could not copy files to Media folder. Please kindly check the media folder permission.', 'warning' );
		}

		if( !$this->checkModules() )
		{
			$this->setMessage( 'Warning : The system encounter an error when it tries to install the modules. Please kindly install the modules manually.', 'warning' );
		}

		$this->setMessage( 'Success : Installation Completed. Thank you for choosing Komento.', 'info' );

	}

	/**
	 * We only support PHP 5 and above
	 */
	public static function checkPHP()
	{
		$phpVersion = floatval(phpversion());

		return ( $phpVersion >= 5 );
	}

	private function checkTables()
	{
		$check = new KomentoDatabaseUpdate();
		return $check->create();
	}

	/**
	 * From time to time, any DB changes will be sync here
	 */
	private function checkDB()
	{
		$check = new KomentoDatabaseUpdate();
		return $check->update();
	}

	/**
	 * Make sure there's at least a default entry in configuration table
	 */
	private function checkKonfig()
	{
		$query	= 'SELECT COUNT(*) FROM ' . $this->db->nameQuote( '#__komento_configs' )
				. ' WHERE ' . $this->db->nameQuote( 'component' ) . ' = ' . $this->db->quote( 'com_komento' );

		$this->db->setQuery( $query );

		if( !$this->db->loadResult() )
		{
			$file		= JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_komento' . DIRECTORY_SEPARATOR . 'konfiguration.ini';

			$registry	= JRegistry::getInstance( 'komento' );

			// Do not save environmental values
			$data	= JFile::read($file);
			$data	= str_ireplace('foundry_environment="development"', '', $data);
			$data	= str_ireplace('komento_environment="development"', '', $data);
			$data	= str_ireplace('foundry_environment="production"', '', $data);
			$data	= str_ireplace('komento_environment="production"', '', $data);

			require_once( JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_komento' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'helper.php' );
			// $registry = Komento::_( 'loadRegistry', 'konfig', $data );
			$registry = Komento::getRegistry( $data );

			$obj		= new stdClass();
			$obj->component	= 'com_komento';
			$obj->params	= $registry->toString( 'INI' );

			return $this->db->insertObject( '#__komento_configs', $obj );
		}

		return true;
	}

	private function checkConfig()
	{
		$query	= 'SELECT COUNT(*) FROM ' . $this->db->nameQuote( '#__komento_configs' )
				. ' WHERE ' . $this->db->nameQuote( 'component' ) . ' = ' . $this->db->quote( 'com_content' );

		$this->db->setQuery( $query );

		if( !$this->db->loadResult() )
		{
			$file		= JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_komento' . DIRECTORY_SEPARATOR . 'configuration.ini';
			// $registry	= JRegistry::getInstance( 'config' );
			// $registry->loadFile( $file, 'INI' );
			$registry = Komento::getRegistry( JFile::read( $file ) );

			$registry->set( 'enable_komento', 1 );

			// Escape regex strings to avoid slashes get stripped off during fresh installation
			$registry->set( 'email_regex', addslashes( $registry->get( 'email_regex' ) ) );
			$registry->set( 'website_regex', addslashes( $registry->get( 'website_regex' ) ) );

			$obj		= new stdClass();
			$obj->component	= 'com_content';
			$obj->params	= $registry->toString( 'INI' );

			return $this->db->insertObject( '#__komento_configs', $obj );
		}

		return true;
	}

	/**
	 * Create default ACL settings
	 */
	private function checkACL()
	{
		$query	= 'SELECT COUNT(*) FROM ' . $this->db->nameQuote( '#__komento_acl' );
		$this->db->setQuery( $query );

		if( !$this->db->loadResult() )
		{
			// create default for each existing usergroup
			$db = KomentoDBHelper::getDBO();

			if( $this->getJoomlaVersion() >= '1.6' )
			{
				$query = 'SELECT a.id, a.title AS `name`, COUNT(DISTINCT b.id) AS level';
				$query .= ' , GROUP_CONCAT(b.id SEPARATOR \',\') AS parents';
				$query .= ' FROM #__usergroups AS a';
				$query .= ' LEFT JOIN `#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt';
			}
			else
			{
				$query	= 'SELECT `id`, `name`, 0 as `level` FROM ' . $db->nameQuote('#__core_acl_aro_groups');
			}

			// condition
			$where  = array();

			// we need to filter out the ROOT and USER dummy records.
			if($this->getJoomlaVersion() < '1.6')
			{
				$where[] = '`id` > 17 AND `id` < 26 OR `id` = 29';
			}

			$where = ( count( $where ) ? ' WHERE ' .implode( ' AND ', $where ) : '' );

			$query  .= $where;

			// grouping and ordering
			if( $this->getJoomlaVersion() >= '1.6' )
			{
				$query	.= ' GROUP BY a.id';
				$query	.= ' ORDER BY a.lft ASC';
			}
			else
			{
				$query 	.= ' ORDER BY id';
			}

			$db->setQuery( $query );
			$userGroups = $db->loadObjectList();

			$userGroupIDs	= array();

			foreach ($userGroups as $userGroup) {
				$userGroupIDs[] = $userGroup->id;
			}


			$db			= KomentoDBHelper::getDBO();
			$query		= 'SELECT `cid` FROM `#__komento_acl` WHERE `component` = '.$db->quote( 'com_content' ). ' AND `type` = \'usergroup\'';
			$db->setQuery( $query );
			$current	= $db->loadResultArray();

			foreach ($userGroupIDs as $userGroupID) {
				if ( !is_array($current) || !in_array($userGroupID, $current))
				{
					$rules = '';

					$query = 'INSERT INTO `#__komento_acl` ( `cid`, `component`, `type` , `rules` ) VALUES ( '.$db->quote($userGroupID).','.$db->quote('com_content').','.$db->quote('usergroup').','.$db->quote($rules).')';
					$db->setQuery( $query );
					$db->query();
				}
			}

			$queries = array();

			/* default id mapping
			name					j1.5	j1.6
			Public					29		1
			Registered				18		2
			Author					19		3
			Editor					20		4
			Publisher				21		5
			Manager					23		6
			Administrator			24		7
			Super Administrator		25		8
			*/

			// update default value to default joomla usergroup for >j1,6
			if( $this->getJoomlaVersion() >= '1.6' )
			{
				// Public
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"0",
						"section":"features"
					}]\' WHERE `cid` = 1 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Manager
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"0",
						"section":"features"
					}]\' WHERE `cid` = 6 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Administrator
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"1",
						"section":"features"
					}]\' WHERE `cid` = 7 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Registered
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"0",
						"section":"features"
					}]\' WHERE `cid` = 2 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Author
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"0",
						"section":"features"
					}]\' WHERE `cid` = 3 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Editor
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"0",
						"section":"features"
					}]\' WHERE `cid` = 4 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Publisher
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"0",
						"section":"features"
					}]\' WHERE `cid` = 5 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Super Administrator
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"1",
						"section":"features"
					}]\' WHERE `cid` = 8 AND `component` = \'com_content\' AND `type` = \'usergroup\'';
			}
			else
			{
				// Public
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"0",
						"section":"features"
					}]\' WHERE `cid` = 29 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Manager
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"0",
						"section":"features"
					}]\' WHERE `cid` = 23 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Administrator
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"1",
						"section":"features"
					}]\' WHERE `cid` = 24 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Registered
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"0",
						"section":"features"
					}]\' WHERE `cid` = 18 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Author
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"0",
						"section":"features"
					}]\' WHERE `cid` = 19 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Editor
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"0",
						"section":"features"
					}]\' WHERE `cid` = 20 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Publisher
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"0",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"0",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"0",
						"section":"features"
					}]\' WHERE `cid` = 21 AND `component` = \'com_content\' AND `type` = \'usergroup\'';

				// Super Administrator
				$queries[] = 'UPDATE `#__komento_acl` SET `rules` = \'[
					{
						"name":"read_comment",
						"title":"COM_KOMENTO_ACL_READCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_stickies",
						"title":"COM_KOMENTO_ACL_READSTICKIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"read_lovies",
						"title":"COM_KOMENTO_ACL_READLOVIES",
						"value":"1",
						"section":"comment"
					},{
						"name":"add_comment",
						"title":"COM_KOMENTO_ACL_ADDCOMMENT",
						"value":"1","section":"comment"
					},{
						"name":"edit_own_comment",
						"title":"COM_KOMENTO_ACL_EDITOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_own_comment",
						"title":"COM_KOMENTO_ACL_DELOWNCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_edit_comment",
						"title":"COM_KOMENTO_ACL_AUTHOREDITCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_delete_comment",
						"title":"COM_KOMENTO_ACL_AUTHORDELCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_publish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"author_unpublish_comment",
						"title":"COM_KOMENTO_ACL_AUTHORUNPUBCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"edit_all_comment",
						"title":"COM_KOMENTO_ACL_EDITALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"delete_all_comment",
						"title":"COM_KOMENTO_ACL_DELALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"publish_all_comment",
						"title":"COM_KOMENTO_ACL_PUBALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"unpublish_all_comment",
						"title":"COM_KOMENTO_ACL_UNPUBALLCOMMENT",
						"value":"1",
						"section":"comment"
					},{
						"name":"like_comment",
						"title":"COM_KOMENTO_ACL_LIKECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"report_comment",
						"title":"COM_KOMENTO_ACL_REPORTCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"share_comment",
						"title":"COM_KOMENTO_ACL_SHARECOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"reply_comment",
						"title":"COM_KOMENTO_ACL_REPLYCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"stick_comment",
						"title":"COM_KOMENTO_ACL_STICKCOMMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"upload_attachment",
						"title":"COM_KOMENTO_ACL_UPLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"download_attachment",
						"title":"COM_KOMENTO_ACL_DOWNLOADATTACHMENT",
						"value":"1",
						"section":"features"
					},{
						"name":"delete_attachment",
						"title":"COM_KOMENTO_ACL_DELETEATTACHMENT",
						"value":"1",
						"section":"features"
					}]\' WHERE `cid` = 25 AND `component` = \'com_content\' AND `type` = \'usergroup\'';
			}

			$db = KomentoDBHelper::getDBO();

			foreach ($queries as $query) {
				$db->setQuery( $query );
				$db->query();
			}
		}

		return true;
	}

	/**
	 * Make sure the menu items are correct, create if non.
	 */
	private function checkMenu()
	{
		// At the moment we skip frontend's menu
		return true;

		if ($this->komentoComponentId)
			return true;

		$mainMenutype = $this->getJoomlaDefaultMenutype();

		// Let's see if the menu item exists or not
		if( $this->joomlaVersion >= '1.6' )
		{
			$query	= 'SELECT COUNT(*) FROM ' . $this->db->nameQuote( '#__menu' )
					. ' WHERE ' . $this->db->nameQuote( 'link' ) . ' LIKE ' .  $this->db->Quote( '%option=com_komento%' )
					. ' AND `client_id`=' . $this->db->Quote( '0' )
					. ' AND `type`=' . $this->db->Quote( 'component' );
		} else {
			$query	= 'SELECT COUNT(*) FROM ' . $this->db->nameQuote( '#__menu' )
					. ' WHERE ' . $this->db->nameQuote( 'link' ) . ' LIKE ' .  $this->db->Quote( '%option=com_komento%' );
		}

		$this->db->setQuery( $query );

		// Update or create menu item
		if( $menuExists = $this->db->loadResult() )
		{
			if( $this->joomlaVersion >= '1.6' )
			{
				$query 	= 'UPDATE ' . $this->db->nameQuote( '#__menu' )
					. ' SET `component_id` = ' . $this->db->Quote( $this->komentoComponentId )
					. ' WHERE `link` LIKE ' . $this->db->Quote('%option=com_komento%')
					. ' AND `type` = ' . $this->db->Quote( 'component' )
					. ' AND `client_id` = ' . $this->db->Quote( '0' );
			}
			else
			{
				$query 	= 'UPDATE ' . $this->db->nameQuote( '#__menu' )
					. ' SET `componentid` = ' . $this->db->Quote( $this->komentoComponentId )
					. ' WHERE `link` LIKE ' . $this->db->Quote('%option=com_komento%');
			}

			$this->db->setQuery( $query );
			$this->db->query();
		}
		else
		{
			$query 	= 'SELECT ' . $this->db->nameQuote( 'ordering' )
					. ' FROM ' . $this->db->nameQuote( '#__menu' )
					. ' ORDER BY ' . $this->db->nameQuote( 'ordering' ) . ' DESC LIMIT 1';
			$this->db->setQuery( $query );
			$order 	= $this->db->loadResult() + 1;

			// hardcode the ordering
			$order = 99999;

			$table = JTable::getInstance( 'Menu', 'JTable' );

			if( $this->joomlaVersion >= '1.6' )
			{
				$table->menutype		= $mainMenutype;
				$table->title 			= 'Komento';
				$table->alias 			= 'Komento';
				$table->path 			= 'komento';
				$table->link 			= 'index.php?option=com_komento';
				$table->type 			= 'component';
				$table->published 		= '1';
				$table->parent_id 		= '1';
				$table->component_id	= $this->komentoComponentId;
				$table->ordering 		= $order;
				$table->client_id 		= '0';
				$table->language 		= '*';
				$table->setLocation('1', 'last-child');

			} else {

				$table->menutype	= $mainMenutype;
				$table->name		= 'Komento';
				$table->alias		= 'Komento';
				$table->link		= 'index.php?option=com_komento';
				$table->type		= 'component';
				$table->published	= '1';
				$table->parent		= '0';
				$table->componentid	= $this->komentoComponentId;
				$table->sublivel	= '';
				$table->ordering	= $order;
			}

			return $table->store();
		}
	}

	private function getKomentoComponentId()
	{
		if( $this->joomlaVersion >= '1.6' )
		{
			$query 	= 'SELECT ' . $this->db->nameQuote( 'extension_id' )
				. ' FROM ' . $this->db->nameQuote( '#__extensions' )
				. ' WHERE `element`=' . $this->db->Quote( 'com_komento' )
				. ' AND `type`=' . $this->db->Quote( 'component' );
		}
		else
		{
			$query 	= 'SELECT ' . $this->db->nameQuote( 'id' )
				. ' FROM ' . $this->db->nameQuote( '#__components' )
				. ' WHERE `option`=' . $this->db->Quote( 'com_komento' )
				. ' AND `parent`=' . $this->db->Quote( '0');
		}

		$this->db->setQuery( $query );

		return $this->db->loadResult();
	}

	private function getJoomlaDefaultMenutype()
	{
		$query	= 'SELECT `menutype` FROM ' . $this->db->nameQuote( '#__menu' )
				. ' WHERE ' . $this->db->nameQuote( 'home' ) . ' = ' . $this->db->quote( '1' );
		$this->db->setQuery( $query );

		return $this->db->loadResult();
	}

	/**
	 * There might be issues with the admin menu
	 */
	private function checkAdminMenu()
	{
		if( $this->joomlaVersion >= '1.6' && $this->komentoComponentId )
		{
			$query	= 'UPDATE '. $this->db->nameQuote( '#__menu' )
					. ' SET ' . $this->db->nameQuote( 'component_id' ) . ' = ' . $this->db->quote( $this->komentoComponentId )
					. ' WHERE ' . $this->db->nameQuote( 'client_id' ) . ' = ' . $this->db->quote( 1 )
					. ' AND ' . $this->db->nameQuote( 'title' ) . ' LIKE ' . $this->db->quote( 'com_komento%' )
					. ' AND ' . $this->db->nameQuote( 'component_id' ) . ' != ' . $this->komentoComponentId;
			$this->db->setQuery( $query );
			$this->db->query();
		}
	}

	/**
	 * Install default plugins
	 */
	private function checkPlugins()
	{
		$result = array();

		if( $this->joomlaVersion >= '3.0' )
		{
			$plugins = $this->manifest->plugins;

			if( $plugins instanceof SimpleXMLElement && count( $plugins ) )
			{
				foreach( $plugins->plugin as $plugin )
				{
					$plgDir = $this->installPath.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$plugin->attributes()->plugin;

					if( JFolder::exists($plgDir) )
					{
						$jinstaller = new JInstaller;
						$result[]	= $jinstaller->install($plgDir);

						$type = (string) $jinstaller->manifest->attributes()->type;

						if (count($jinstaller->manifest->files->children()))
						{
							foreach ($jinstaller->manifest->files->children() as $file)
							{
								if ((string) $file->attributes()->$type)
								{
									$element = (string) $file->attributes()->$type;
									break;
								}
							}
						}

						$query	= ' UPDATE `#__extensions` SET `enabled` = ' . $this->db->quote( 1 )
								. ' WHERE `element` = ' . $this->db->quote( $element )
								. ' AND `folder` = ' . $this->db->quote( $plugin->attributes()->group )
								. ' AND `type` = ' . $this->db->quote( 'plugin' );
						$this->db->setQuery( $query );
						$result[] = $this->db->query();
					}
				}
			}
		}
		elseif( $this->joomlaVersion > '1.5' && $this->joomlaVersion < '3.0' )
		{
			//$plugins = $this->manifest->xpath('plugins/plugin');
			$plugins = $this->manifest->plugins;

			if( $plugins instanceof JXMLElement && count($plugins) )
			{
				foreach ($plugins->plugin as $plugin)
				{
					$plgDir = $this->installPath.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$plugin->getAttribute('plugin');

					if( JFolder::exists($plgDir) )
					{
						$jinstaller = new JInstaller;
						$result[]	= $jinstaller->install($plgDir);

						$type = (string) $jinstaller->manifest->attributes()->type;

						if (count($jinstaller->manifest->files->children()))
						{
							foreach ($jinstaller->manifest->files->children() as $file)
							{
								if ((string) $file->attributes()->$type)
								{
									$element = (string) $file->attributes()->$type;
									break;
								}
							}
						}

						$query	= ' UPDATE `#__extensions` SET `enabled` = ' . $this->db->quote( 1 )
								. ' WHERE `element` = ' . $this->db->quote( $element )
								. ' AND `folder` = ' . $this->db->quote( $jinstaller->manifest->getAttribute('group') )
								. ' AND `type` = ' . $this->db->quote( 'plugin' );
						$this->db->setQuery( $query );
						$result[] = $this->db->query();
					}
				}
			}
		}
		else
		{
			$plugins = $this->jinstaller->_adapters['component']->manifest->getElementByPath('plugins');

			if( $plugins instanceof JSimpleXMLElement && count($plugins->children()) )
			{
				foreach ($plugins->children() as $plugin)
				{
					$plgDir = $this->installPath.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$plugin->attributes('plugin');

					if( JFolder::exists($plgDir) )
					{
						$jinstaller = new JInstaller;
						$result[]	= $jinstaller->install($plgDir);

						$type = $jinstaller->_adapters['plugin']->manifest->attributes('type');

						// Set the installation path
						$element = $jinstaller->_adapters['plugin']->manifest->getElementByPath('files');
						if (is_a($element, 'JSimpleXMLElement') && count($element->children())) {
							$files = $element->children();
							foreach ($files as $file) {
								if ($file->attributes($type)) {
									$element = $file->attributes($type);
									break;
								}
							}
						}

						$query	= 'UPDATE `#__plugins` SET `published` = ' . $this->db->quote( 1 )
								. ' WHERE `element` = ' . $this->db->quote( $element )
								. ' AND `folder` = ' . $this->db->quote( $plugin->attributes('group') );
						$this->db->setQuery($query);
						$this->db->query();
					}
				}
			}
		}

		foreach ($result as $value)
		{
			if( !$value )
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Install default modules
	 */
	private function checkModules()
	{
		$result = array();

		if( $this->joomlaVersion >= '3.0' )
		{
			$modules = $this->manifest->modules;

			if( $modules instanceof SimpleXMLElement && count( $modules ) )
			{
				foreach( $modules->module as $module )
				{
					$modDir = $this->installPath.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$module->attributes()->module;

					if( JFolder::exists($modDir) )
					{
						$jinstaller = new JInstaller;
						$result[]	= $jinstaller->install($modDir);
					}
				}
			}
		}
		elseif( $this->joomlaVersion > '1.5' && $this->joomlaVersion < '3.0' )
		{
			$modules = $this->manifest->modules;

			if( $modules instanceof JXMLElement && count($modules) )
			{
				foreach ($modules->module as $module)
				{
					$modDir = $this->installPath.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$module->getAttribute('module');

					if( JFolder::exists($modDir) )
					{
						$jinstaller = new JInstaller;
						$result[]	= $jinstaller->install($modDir);
					}
				}
			}
		}
		else
		{
			$modules = $this->jinstaller->_adapters['component']->manifest->getElementByPath('modules');

			if( $modules instanceof JSimpleXMLElement && count($modules->children()) )
			{
				foreach ($modules->children() as $module)
				{
					$modDir = $this->installPath.'/modules/'.$module->attributes('module');

					if( JFolder::exists($modDir) )
					{
						$jinstaller = new JInstaller;
						$result[]	= $jinstaller->install($modDir);
					}
				}
			}
		}

		foreach ($result as $value)
		{
			if( !$value )
			{
				return false;
			}
		}

		return true;
	}

	private function extract( $archivename, $extractdir )
	{
		$archivename= JPath::clean( $archivename );
		$extractdir	= JPath::clean( $extractdir );

		return JArchive::extract( $archivename, $extractdir );
	}

	/**
	 * Install the foundry folder
	 */
	private function checkMedia()
	{
		// Copy media/com_komento
		// Overwrite all
		$mediaSource	= $this->installPath . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_komento';
		$mediaDestina	= JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_komento';

		if( !JFolder::copy($mediaSource, $mediaDestina, '', true) )
		{
			return false;
		}


		// Copy media/foundry
		// Overwrite only if version is newer
		$mediaSource	= $this->installPath . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'foundry';
		$mediaDestina	= JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'foundry';
		$overwrite		= false;
		$incomingVersion = '';
		$installedVersion = '';

		if(! JFolder::exists( $mediaDestina ) )
		{
			// foundry folder not found. just copy foundry folde without need to check.
			if (! JFolder::copy($mediaSource, $mediaDestina, '', true) )
			{
				return false;
			}

			return true;
		}

		// We don't have a a constant of Foundry's version, so we'll
		// find the folder name as the version number. We assumed there's
		// only ONE folder in foundry that come with the installer.
		$folder	= JFolder::folders($mediaSource);

		if(	!($incomingVersion = (string) JFile::read( $mediaSource . DIRECTORY_SEPARATOR . $folder[0] . DIRECTORY_SEPARATOR . 'version' )) )
		{
			// can't read the version number
			return false;
		}

		if( !JFile::exists($mediaDestina . DIRECTORY_SEPARATOR . $folder[0] . DIRECTORY_SEPARATOR . 'version')
			|| !($installedVersion = (string) JFile::read( $mediaDestina . DIRECTORY_SEPARATOR . $folder[0] . DIRECTORY_SEPARATOR . 'version' )) )
		{
			// foundry version not exists or need upgrade
			$overwrite = true;
		}

		$incomingVersion	= preg_replace('/[^a-zA-Z0-9\.]/i', '', $incomingVersion);
		$installedVersion	= preg_replace('/[^a-zA-Z0-9\.]/i', '', $installedVersion);

		if( $overwrite || version_compare($incomingVersion, $installedVersion) > 0 )
		{
			if( !JFolder::copy($mediaSource . DIRECTORY_SEPARATOR . $folder[0], $mediaDestina . DIRECTORY_SEPARATOR . $folder[0], '', true) )
			{
				return false;
			}
		}

		return true;
	}

	private function getJoomlaVersion()
	{
		$jVerArr	= explode('.', JVERSION);
		$jVersion	= $jVerArr[0] . '.' . $jVerArr[1];

		return $jVersion;
	}

	private function setMessage( $msg, $type )
	{
		$this->messages[] = array( 'type' => strtolower($type), 'message' => $msg );
	}

	public function getMessages()
	{
		return $this->messages;
	}
}


class KomentoDatabaseUpdate
{
	protected $db	= null;

	public function __construct()
	{
		$this->db	= KomentoDBHelper::getDBO();
	}

	public function create()
	{
		if( !$this->isTableExists( '#__komento_comments') )
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__komento_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `component` varchar(255) NOT NULL,
  `cid` bigint(20) unsigned NOT NULL,
  `comment` text,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT '',
  `url` varchar(255) DEFAULT '',
  `ip` varchar(255) DEFAULT '',
  `created_by` bigint(20) unsigned DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) unsigned DEFAULT '0',
  `modified` datetime DEFAULT '0000-00-00 00:00:00',
  `deleted_by` bigint(20) unsigned DEFAULT '0',
  `deleted` datetime DEFAULT '0000-00-00 00:00:00',
  `flag` tinyint(1) DEFAULT '0',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `publish_up` datetime DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime DEFAULT '0000-00-00 00:00:00',
  `sticked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sent` tinyint(1) DEFAULT '0',
  `parent_id` int(11) unsigned DEFAULT '0',
  `lft` int(11) unsigned NOT NULL DEFAULT '0',
  `rgt` int(11) unsigned NOT NULL DEFAULT '0',
  `depth` int(11) unsigned NOT NULL DEFAULT '0',
  `latitude` VARCHAR(255) NULL,
  `longitude` VARCHAR(255) NULL,
  `address` TEXT NULL,
  PRIMARY KEY (`id`),
  KEY `komento_component` (`component`),
  KEY `komento_cid` (`cid`),
  KEY `komento_parent_id` (`parent_id`),
  KEY `komento_lft` (`lft`),
  KEY `komento_rgt` (`rgt`),
  KEY `komento_frontend` (`component`, `cid`, `published`),
  KEY `komento_frontend_threaded` (`component`, `cid`, `published`, `id`, `lft`),
  KEY `komento_backend` (`parent_id`, `component`, `cid`, `created`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		if( !$this->isTableExists( '#__komento_configs' ) )
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__komento_configs` (
  `component` varchar(255) NOT NULL,
  `params` text NOT NULL
) DEFAULT CHARSET=utf8;";
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		if( !$this->isTableExists( '#__komento_acl' ) )
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__komento_acl` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cid` varchar(255) NOT NULL,
  `component` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `rules` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `komento_acl_content_type` (`type`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		if( !$this->isTableExists( '#__komento_actions' ) )
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__komento_actions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `comment_id` bigint(20) unsigned NOT NULL,
  `action_by` bigint(20) unsigned NOT NULL DEFAULT 0,
  `actioned` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `komento_actions` (`type`, `comment_id`, `action_by`),
  KEY `komento_actions_comment_id` (`comment_id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		if( !$this->isTableExists( '#__komento_activities' ) )
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__komento_activities` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `comment_id` bigint(20) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		if( !$this->isTableExists( '#__komento_captcha' ) )
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__komento_captcha` (
  `id` int(11) NOT NULL auto_increment,
  `response` varchar(5) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		if( !$this->isTableExists( '#__komento_mailq' ) )
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__komento_mailq` (
  `id` int(11) NOT NULL auto_increment,
  `mailfrom` varchar(255) NULL,
  `fromname` varchar(255) NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  `created` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `komento_mailq_status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		if( !$this->isTableExists( '#__komento_subscription' ) )
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__komento_subscription` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `component` varchar(255) NOT NULL,
  `cid` bigint(20) unsigned NOT NULL,
  `userid` bigint(20) unsigned NOT NULL DEFAULT 0,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created` DATETIME  NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `komento_subscription` (`type`, `component`, `cid`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		if( !$this->isTableExists( '#__komento_hashkeys' ) )
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__komento_hashkeys` (
  `id` bigint(11) NOT NULL auto_increment,
  `uid` bigint(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `key` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `uid` (`uid`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		if( !$this->isTableExists( '#__komento_uploads' ) )
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__komento_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NULL,
  `filename` text NOT NULL,
  `hashname` text NOT NULL,
  `path` text NULL,
  `created` datetime NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT '0',
  `published` tinyint(1) NOT NULL,
  `mime` text NOT NULL,
  `size` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		if( !$this->isTableExists( '#__komento_ipfilter' ) )
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__komento_ipfilter` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `component` varchar(255) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `rules` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `komento_ipfilter` (`component`, `ip`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		return true;
	}

	public function update()
	{
		// Reset and Alter Activities Table
		// Added in #[3c2d4f952a2bac28bb5da5aaa6d11e8576a3a2db], 18 April 2012
		if( $this->isColumnExists( '#__komento_activities', 'title' ) )
		{
			$query = 'ALTER TABLE `#__komento_activities` DROP COLUMN `title`';
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}
		if( $this->isColumnExists( '#__komento_activities', 'url' ) )
		{
			$query = 'ALTER TABLE `#__komento_activities` DROP COLUMN `url`';
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}
		if( $this->isColumnExists( '#__komento_activities', 'component' ) )
		{
			$query = 'ALTER TABLE `#__komento_activities` DROP COLUMN `component`';
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}
		if( $this->isColumnExists( '#__komento_activities', 'cid' ) )
		{
			$query = 'DELETE FROM `#__komento_activities`';
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;

			$query = 'ALTER TABLE `#__komento_activities` DROP COLUMN `cid`';
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}
		if( !$this->isColumnExists( '#__komento_activities', 'comment_id' ) )
		{
			$query = 'ALTER TABLE  `#__komento_activities` ADD `comment_id` BIGINT(20) NOT NULL AFTER `type`';
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		// Fix reports menu link
		// Added in #[87179de3ebc4c226470d1d8f6d83e35daaa715c6], 16 May 2012
		if( $this->getJoomlaVersion() >= '1.6' )
		{
			$query = 'UPDATE `#__menu` SET `link` = ' . $this->db->quote( 'index.php?option=com_komento&view=reports' ) . ' WHERE `title` = ' . $this->db->quote( 'COM_KOMENTO_MENU_REPORTS' );
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}
		else
		{
			$query = 'UPDATE `#__components` SET `admin_menu_link` = ' . $this->db->quote( 'option=com_komento&view=reports' ) . ' WHERE `name` = ' . $this->db->quote( 'COM_KOMENTO_MENU_REPORTS' );
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		// Add published column to subscription table
		// Added in #[04b86b4c3feb30bda179a83873e7dcf165dfa668], 23 May 2012
		if( !$this->isColumnExists( '#__komento_subscription', 'published' ) )
		{
			$query = 'ALTER TABLE  `#__komento_subscription` ADD `published` tinyint(1) NOT NULL DEFAULT 0 AFTER `created`';
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		// Add hashkeys table
		// Added in #[7daebcea665a143118dc8a6b3b88ee7b03f6b3a7], 19 June 2012
		if( !$this->isTableExists( '#__komento_hashkeys' ) )
		{
			$query = 'CREATE TABLE IF NOT EXISTS `#__komento_hashkeys` (
				`id` bigint(11) NOT NULL auto_increment,
				`uid` bigint(11) NOT NULL,
				`type` varchar(255) NOT NULL,
				`key` text NOT NULL,
				PRIMARY KEY  (`id`),
				KEY `uid` (`uid`),
				KEY `type` (`type`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		// Add uploads table
		// Added in #[0df9f868f227d2db74c16cd9eba0a7e89e882ab7], 21 June 2012
		if( !$this->isTableExists( '#__komento_uploads' ) )
		{
			$query = 'CREATE TABLE IF NOT EXISTS `#__komento_uploads` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`filename` text NOT NULL,
				`hashname` text NOT NULL,
				`path` text NULL,
				`created` datetime NOT NULL,
				`created_by` bigint(20) unsigned DEFAULT \'0\',
				`published` tinyint(1) NOT NULL,
				`mime` text NOT NULL,
				`size` text NOT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		// Add UID column to uploads table
		// Added in #[d1fdeaa0f5ab7dd1874cd88a38bdc869e71c7aa0], 25 June 2012
		if( !$this->isColumnExists( '#__komento_uploads', 'uid' ) )
		{
			$query = 'ALTER TABLE  `#__komento_uploads` ADD `uid` int(11) NULL AFTER `id`';
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		// Add Komento Frontend key to comments table
		// Added in #[1c63083778fc0c8a854c06c08a7691619a582e43], 30 August 2012
		if( !$this->isIndexKeyExists( '#__komento_comments', 'komento_frontend' ) )
		{
			$query = 'ALTER TABLE `#__komento_comments` ADD INDEX `komento_frontend` (`component`, `cid`, `published`)';
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		// Add Komento Frontend Threaded key to comments table
		// Added in #[1c63083778fc0c8a854c06c08a7691619a582e43], 30 August 2012
		if( !$this->isIndexKeyExists( '#__komento_comments', 'komento_frontend_threaded' ) )
		{
			$query = 'ALTER TABLE `#__komento_comments` ADD INDEX `komento_frontend_threaded` (`component`, `cid`, `published`, `id`, `lft`)';
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		// Add Komento Actions comment_id key to actions table
		// Added in #[eb86a6ced927a7f8483adf71515272d264618b58], 5 December 2012
		if( !$this->isIndexKeyExists( '#__komento_actions', 'komento_actions_comment_id' ) )
		{
			$query = 'ALTER TABLE `#__komento_actions` ADD INDEX `komento_actions_comment_id` (`comment_id`)';
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		// Add Komento Comments depth column
		// Added in #[acfda1dd752c7a7daf496d381f54b4c93e344983], 10 December 2012
		if( !$this->isColumnExists( '#__komento_comments', 'depth' ) )
		{
			$query = 'ALTER TABLE  `#__komento_comments` ADD `depth` INT(11) NOT NULL DEFAULT \'0\' AFTER `rgt`';
			$this->db->setQuery( $query );
			if( !$this->db->query() ) return false;
		}

		// Add Komento configs for backend comments listing configuration
		// Added in #[10224b46e3a0f4453fc8d9778c2dfb0ac87ab01c], 12 December 2012
		$this->db->setQuery( 'SELECT COUNT(1) FROM `#__komento_configs` WHERE `component` = "com_komento_comments_columns"' );
		if( !$this->db->loadResult() )
		{
			require_once( JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_komento' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'helper.php' );
			$registry = Komento::getRegistry();
			$registry->set( 'column_comment', 1 );
			$registry->set( 'column_published', 1 );
			$registry->set( 'column_sticked', 1 );
			$registry->set( 'column_link', 1 );
			$registry->set( 'column_edit', 1 );
			$registry->set( 'column_component', 1 );
			$registry->set( 'column_article', 1 );
			$registry->set( 'column_date', 1 );
			$registry->set( 'column_author', 1 );
			$registry->set( 'column_id', 1 );
			$query = 'INSERT INTO `#__komento_configs` VALUES("com_komento_comments_columns", "' . $registry->toString( 'INI' ) . '")';
			$this->db->setQuery( $query );
			$this->db->query();
		}

		return true;
	}

	private function isTableExists( $tableName )
	{
		$query	= 'SHOW TABLES LIKE ' . $this->db->quote($tableName);
		$this->db->setQuery( $query );

		return (boolean) $this->db->loadResult();
	}

	private function isColumnExists( $tableName, $columnName )
	{
		$query	= 'SHOW FIELDS FROM ' . $this->db->nameQuote( $tableName );
		$this->db->setQuery( $query );

		$fields	= $this->db->loadObjectList();

		$result = array();

		foreach( $fields as $field )
		{
			$result[ $field->Field ]	= preg_replace( '/[(0-9)]/' , '' , $field->Type );
		}

		if( array_key_exists($columnName, $result) )
		{
			return true;
		}

		return false;
	}

	private function isIndexKeyExists( $tableName, $indexName )
	{
		$query	= 'SHOW INDEX FROM ' . $this->db->nameQuote( $tableName );
		$this->db->setQuery( $query );
		$indexes	= $this->db->loadObjectList();

		$result = array();

		foreach( $indexes as $index )
		{
			$result[ $index->Key_name ]	= preg_replace( '/[(0-9)]/' , '' , $index->Column_name );
		}

		if( array_key_exists($indexName, $result) )
		{
			return true;
		}

		return false;
	}

	private function getJoomlaVersion()
	{
		$jVerArr	= explode('.', JVERSION);
		$jVersion	= $jVerArr[0] . '.' . $jVerArr[1];

		return $jVersion;
	}
}

class KomentoDBHelper
{
	public static $helper = null;

	public static function getDBO()
	{
		if( is_null( self::$helper ) )
		{
			$version    = self::getJoomlaVersion();
			$className	= 'KomentoDBJoomla15';

			if( $version >= '2.5' )
			{
				$className 	= 'KomentoDBJoomla30';
			}

			self::$helper   = new $className();
		}

		return self::$helper;

	}

	public function getJoomlaVersion()
	{
		$jVerArr   = explode('.', JVERSION);
		$jVersion  = $jVerArr[0] . '.' . $jVerArr[1];

		return $jVersion;
	}

}


class KomentoDBJoomla15
{
	public $db 		= null;

	public function __construct()
	{
		$this->db	= JFactory::getDBO();
	}

	public function __call( $method , $args )
	{
		$refArray	= array();

		if( $args )
		{
			foreach( $args as &$arg )
			{
				$refArray[]	=& $arg;
			}
		}

		return call_user_func_array( array( $this->db , $method ) , $refArray );
	}
}


class KomentoDBJoomla30
{
	public $db 		= null;

	public function __construct()
	{
		$this->db	= JFactory::getDBO();
	}

	public function loadResultArray()
	{
		return $this->db->loadColumn();
	}

	public function nameQuote( $str )
	{
		return $this->db->quoteName( $str );
	}

	public function __call( $method , $args )
	{
		$refArray	= array();

		if( $args )
		{
			foreach( $args as &$arg )
			{
				$refArray[]	=& $arg;
			}
		}

		return call_user_func_array( array( $this->db , $method ) , $refArray );
	}
}

class KomentoMenuMaintenance
{
	function removeAdminMenu()
	{
		$db = KomentoDBHelper::getDBO();
		$query  = 'DELETE FROM `#__menu` WHERE link LIKE \'%com_komento%\' AND client_id = \'1\'';

		$db->setQuery($query);
		$db->query();
	}
}

<?php
/**
 * @version		$Id: $
 * @author		Mr.LongAnh
 * @package		Joomla!
 * @subpackage	Plugin
 * @copyright	Copyright (C) 2008 - 2011 by Codextension. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL version 3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');

class JFormFieldPreview extends JFormField{
	protected $type = 'preview';

	protected function getInput(){
		$html = '<div class="button2-left"><div class="blank"><a href="../index.php?jl_popup_anywhere_pro_preview=true" target="_blank">Click for Preview (apply first)</a></div></div>';
		return $html;
	}
}

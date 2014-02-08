<?php
/**
* @version		$Id: imagelist.php 14401 2010-01-26 14:10:00Z louis $
* @package		Joomla.Framework
* @subpackage	Parameter
* @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* Modded by Monev Software to use specific folders
*
*/

defined('JPATH_BASE') or die;

//jimport('joomla.html.html');
//jimport('joomla.filesystem.folder');
//jimport('joomla.filesystem.file');
//jimport('joomla.form.formfield');
//jimport('joomla.form.helper');
//JFormHelper::loadFieldClass('list');

JHTML::_('behavior.modal');

class JFormFieldTemplateimage extends JFormField {

	protected $type = 'Templateimage';

	protected function getInput() {

		$template = basename(dirname(dirname(dirname(__FILE__))));
		$directory = basename($this->element['directory']);

		// Build the script.
		$script = array();
		$script[] = '	function jInsertFieldValue(value, id) {';
		$script[] = '		var old_value = document.id(id).value;';
		$script[] = '		if (old_value != value) {';
		$script[] = '			var elem = document.id(id);';
		$script[] = '			elem.value = value;';
		$script[] = '			elem.fireEvent("change");';
		$script[] = '			if (typeof(elem.onchange) === "function") {';
		$script[] = '				elem.onchange();';
		$script[] = '			}';
		$script[] = '			jMediaRefreshPreview(id);';
		$script[] = '		}';
		$script[] = '	}';

		$script[] = '	function jMediaRefreshPreview(id) {';
		$script[] = '		var value = document.id(id).value;';
		$script[] = '		var img = document.id(id + "_preview");';
		$script[] = '		if (img) {';
		$script[] = '			if (value) {';
		$script[] = '				img.src = "' . JURI::root() . '" + value;';
		$script[] = '				document.id(id + "_preview_empty").setStyle("display", "none");';
		$script[] = '				document.id(id + "_preview_img").setStyle("display", "");';
		$script[] = '			} else { ';
		$script[] = '				img.src = ""';
		$script[] = '				document.id(id + "_preview_empty").setStyle("display", "");';
		$script[] = '				document.id(id + "_preview_img").setStyle("display", "none");';
		$script[] = '			} ';
		$script[] = '		} ';
		$script[] = '	}';

		$script[] = '	function jMediaRefreshPreviewTip(tip)';
		$script[] = '	{';
		$script[] = '		var img = tip.getElement("img.media-preview");';
		$script[] = '		tip.getElement("div.tip").setStyle("max-width", "none");';
		$script[] = '		var id = img.getProperty("id");';
		$script[] = '		id = id.substring(0, id.length - "_preview".length);';
		$script[] = '		jMediaRefreshPreview(id);';
		$script[] = '		tip.setStyle("display", "block");';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		$html = '<div class="input-prepend input-append">
			<a class="btn" title="View image" onclick="window.open(\''.Juri::root().'templates/'.$template.'/images/'.$directory.'/\'+document.getElementById(\''.$this->id.'\').value,\'_blank\',\'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=400\');">
				<i class="icon-eye"> </i>
			</a>
			<input type="text" class="input-small" name="'.$this->name.'" id="'.$this->id.'" value="'.$this->value.'" />
			&nbsp;
			<a class="modal btn" title="Select an image"
				 href="index.php?option=com_jxtc&view=files&tmpl=component&fld='.$this->id.'&id='.JRequest::getInt('id').'&f='.$directory.'" 
				 rel="{handler: \'iframe\', size: {x: 755, y: 495}}">'.
				 JText::_('JSELECT').'
		  </a>';
		if (!$this->element['hide_none']) {
		 	$html .= '<a class="btn btn-danger" title="'.JText::_('JLIB_FORM_BUTTON_CLEAR').'" href="#" onclick="jInsertFieldValue(\'\', \''.$this->id.'\');return false;">
				<i class="icon-remove icon-white"></i>
			</a>';
		}
		$html .= '</div>';
		
		return $html;
	}
}


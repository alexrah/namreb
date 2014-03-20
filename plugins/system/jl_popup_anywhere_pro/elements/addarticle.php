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

class JFormFieldAddarticle extends JFormField
{
	protected $type = 'addarticle';

	protected function getInput()
	{
		$name	= $this->name;
		$value	= $this->value;
		$id		= $this->id;
		
		$db			=& JFactory::getDBO();
		$doc 		=& JFactory::getDocument();
		//$fieldName	= $control_name.'['.$name.']';
		$article =& JTable::getInstance('content');
		
		if ($value) {
			$article->load($value);
		} else {
			$article->title = JTEXT::_('PLG_JL_POPUP_ANYWHERE_PRO_SELECT_A_ARTICLE');
		}

		$js = "
		function jSelectArticle(id, title, object) {			
			document.getElementById('".$this->id."'+'_id').value = id;
			document.getElementById('".$this->id."'+'_name').value = title;
			document.getElementById('jform_params_link').value = 'index.php?option=com_content&view=article&tmpl=component&id='+id;
			document.getElementById('jform_params_link').setProperty('readonly','readonly');
			SqueezeBox.close();
		}";
		$doc->addScriptDeclaration($js);

		//$link = 'index.php?option=com_content&amp;task=element&amp;tmpl=component&amp;object='.$name;
		$link	= 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle';

		// class='required' for client side validation
		$class = '';
		if ($this->required) {
			$class = ' class="required modal-value"';
		}
		
		JHTML::_('behavior.modal', 'a.modal');
		$html = "\n".'<div style="float: left;"><input style="background: #ffffff;" type="text" id="'.$this->id.'_name" value="'.htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8').'" disabled="disabled" /></div>';
		$html .= '<div class="button2-left"><div class="blank"><a class="modal" title="'.JText::_('Select an Article').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">'.JText::_('Select').'</a></div></div>'."\n";
		$html .= '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';
		$html .= '<div class="button2-left">
					<div class="blank">
						<a onclick=\'document.id("jform_params_insertid_id").value="0";document.id("jform_params_insertid_name").value="'.JTEXT::_('PLG_JL_POPUP_ANYWHERE_PRO_SELECT_A_ARTICLE').'";document.id("jform_params_link").removeProperty("readonly");return false;\' href="#" title="'.JText::_('PLG_JL_POPUP_ANYWHERE_PRO_CLEAR').'">'.JText::_('PLG_JL_POPUP_ANYWHERE_PRO_CLEAR').'</a>
					</div>
				</div>';
		return $html;
	}
}
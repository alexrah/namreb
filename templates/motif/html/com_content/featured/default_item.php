<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Create a shortcut for params.
$params = &$this->item->params;
$images = json_decode($this->item->images);
$canEdit	= $this->item->params->get('access-edit');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');


?>
<div class="cat-item">
<?php if ($this->item->state == 0) : ?>
<div class="system-unpublished">
<?php endif; ?>







<?php if (!$params->get('show_intro')) : ?>
	<?php echo $this->item->event->afterDisplayTitle; ?>
<?php endif; ?>







<div class="category_text">
<?php  if (isset($images->image_intro) and !empty($images->image_intro)) : ?>
	<?php $imgfloat = (empty($images->float_intro)) ? $params->get('float_intro') : $images->float_intro; ?>
	<div class="imgframe">
	<img
		<?php if ($images->image_intro_caption):
			echo 'class="caption imgframe"'.' title="' .htmlspecialchars($images->image_intro_caption) .'"';
		endif; ?>
		src="<?php echo htmlspecialchars($images->image_intro); ?>" alt="<?php echo htmlspecialchars($images->image_intro_alt); ?>"/>
	</div>
<?php endif; ?>

    
<?php echo $this->item->event->beforeDisplayContent; ?>
    
<?php if ($params->get('show_title')) {?>
	<h4 class="title">
		<?php if ($params->get('link_titles') && $params->get('access-view')) : ?>
			<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid)); ?>">
			<?php echo $this->escape($this->item->title); ?></a>
		<?php else : ?>
			<?php echo $this->escape($this->item->title); ?>
		<?php endif; ?>
	</h4>
<?php } ?>	
    
    
    
<?php if ($params->get('show_parent_category') && $this->item->parent_id != 1) {?>
		<span class="art_info">
			<?php $title = $this->escape($this->item->parent_title);
				$url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->parent_id)) . '">' . $title . '</a>'; ?>
			<?php if ($params->get('link_parent_category')) : ?>
				<?php echo JText::sprintf('COM_CONTENT_PARENT', $url); ?>
				<?php else : ?>
				<?php echo JText::sprintf('COM_CONTENT_PARENT', $title); ?>
			<?php endif; ?>
		</span>
<?php } ?>  
    
<?php if ($params->get('show_category')) {?>
	
		<span class="art_info">
			<?php $title = $this->escape($this->item->category_title);
					$url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catid)) . '">' . $title . '</a>'; ?>
			<?php if ($params->get('link_category')) : ?>
				<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
				<?php else : ?>
				<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $title); ?>
			<?php endif; ?>
		</span>
<?php } ?>
    
<?php if ($params->get('show_author') && !empty($this->item->author )) { ?>
	<span class="art_info">
		<?php $author =  $this->item->author; ?>
		<?php $author = ($this->item->created_by_alias ? $this->item->created_by_alias : $author);?>

			<?php if (!empty($this->item->contactid ) &&  $params->get('link_author') == true):?>
				<?php 	echo JText::sprintf('COM_CONTENT_WRITTEN_BY' ,
				 '<span class="basecolor_1">'.JHtml::_('link', JRoute::_('index.php?option=com_contact&view=contact&id='.$this->item->contactid), $author)).'</span>'; ?>

			<?php else :?>
				<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', '<span class="basecolor_1">'.$author).'</span>'; ?>
			<?php endif; ?>
	</span>
<?php } ?>    
    
<?php 
echo $this->item->introtext; 
?>
</div>
<?php if ($params->get('show_readmore') && $this->item->readmore) {
	if ($params->get('access-view')) :
		$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid));
	else :
		$menu = JFactory::getApplication()->getMenu();
		$active = $menu->getActive();
		$itemId = $active->id;
		$link1 = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
		$returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid));
		$link = new JURI($link1);
		$link->setVar('return', base64_encode($returnURL));
	endif;
?>
		<div class="readmore">
        		<a href="<?php echo $link; ?>">
                <span style="padding-right:8px;"><i class="icon-circle-arrow-right"></i></span>
					<?php if (!$params->get('access-view')) :
						echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
					elseif ($readmore = $this->item->alternative_readmore) :
						echo $readmore;
						if ($params->get('show_readmore_title', 0) != 0) :
						    echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
						endif;
					elseif ($params->get('show_readmore_title', 0) == 0) :
						echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
					else :
						echo JText::_('COM_CONTENT_READ_MORE');
						echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
					endif; ?></a>
		</div>
<?php } ?>

<?php if ($params->get('show_print_icon') || $params->get('show_email_icon') || $canEdit) { ?>
	<div class="action floatRight">
		<?php if ($params->get('show_print_icon')) { ?>
		<span class="print-icon">
			<?php echo JHtml::_('icon.print_popup', $this->item, $params); ?>
		</span>
		<?php } ?>
		<?php if ($params->get('show_email_icon')) { ?>
		<span class="email-icon">
			<?php echo JHtml::_('icon.email', $this->item, $params); ?>
		</span>
		<?php } ?>
		<?php if ($canEdit) : ?>
		<span class="edit-icon">
			<?php echo JHtml::_('icon.edit', $this->item, $params); ?>
		</span>
		<?php endif; ?>
	</div>
<?php } ?>
 <div style="clear:both;"></div>
<?php if ($this->item->state == 0) : ?>
</div>
<?php endif; ?>


</div>
<?php echo $this->item->event->afterDisplayContent; ?>

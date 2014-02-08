<?php
/**
 * @package		Komento
 * @copyright	Copyright (C) 2012 Stack Ideas Private Limited. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 *
 * Komento is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<?php echo Komento::trigger( 'onBeforeKomentoItem', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>

<?php // KURO THEME

// Process data
$row = KomentoCommentHelper::process($row);

// Usergroup CSS Classname control
$classname	= '';
if (Komento::getProfile( $row->created_by )->guest)
{
	$classname = ' ' . $system->config->get( 'layout_css_public' );
} else {
	$classname = ' ' . $system->config->get( 'layout_css_registered' );
}
if (Komento::getProfile( $row->created_by )->isAdmin())
{
	$classname = ' ' . $system->config->get( 'layout_css_admin' );
}
if( $row->created_by == $row->extension->getAuthorId() )
{
	$classname .= ' ' . $system->config->get( 'layout_css_author' );
}

$usergroups	= Komento::getUserGids( Komento::getProfile( $row->created_by )->id );
if (is_array($usergroups) && !empty($usergroups))
{
	foreach ($usergroups as $usergroup) {
		$classname .= ' kmt-comment-item-usergroup-' . $usergroup;
	}
} ?>
<li id="kmt-<?php echo $row->id; ?>" class="kmt-<?php echo $row->id; ?> kmt-item kmt-child-<?php echo $row->depth; ?> <?php if($row->sticked) echo 'kmt-sticked'; ?> <?php echo $classname; ?> <?php echo $row->published == 1 ? 'kmt-published' : 'kmt-unpublished'; ?>" parentid="kmt-<?php echo $row->parent_id; ?>" depth="<?php echo $row->depth; ?>" childs="<?php echo $row->childs; ?>" published="<?php echo $row->published; ?>"<?php if( $system->konfig->get( 'enable_schema' ) ) echo ' itemscope itemtype="http://schema.org/UserComments"'; ?>>

<?php // depth and indentation calculation
	$css = '';
	if( $system->config->get( 'enable_threaded' ) )
	{
		$unit = $system->konfig->get('thread_indentation');
		$total = $unit * $row->depth;

		$css = 'style="margin-left: ' . $total . 'px !important"';

		// support for RTL sites
		// forcertl = 1 for dev purposes
		if( JFactory::getDocument()->direction == 'rtl' || JRequest::getInt( 'forcertl' ) == 1 )
		{
			$css = 'style="margin-right: ' . $total . 'px !important"';
		}
	}
?>
<div class="kmt-wrap" <?php echo $css; ?>>

	<?php echo Komento::trigger( 'onBeforeKomentoItemAvatar', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>
	<!-- Avatar div.kmt-avatar -->
	<?php echo $this->fetch( 'comment/item/avatar.php' ); ?>
	<?php echo Komento::trigger( 'onAfterKomentoItemAvatar', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>

	<div class="kmt-content">

		<?php echo Komento::trigger( 'onBeforeKomentoItemHead', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>
		<div class="kmt-head">
			<?php echo $this->fetch( 'comment/item/id.php' ); ?>

			<!-- Name span.kmt-author -->
			<?php echo $this->fetch( 'comment/item/author.php' ); ?>

			<!-- In reply to span.kmt-inreplyto -->
			<?php echo $this->fetch( 'comment/item/inreplyto.php' ); ?>

			<span class="kmt-option float-wrapper">
				<!-- Report Comment span.kmt-report-wrap -->
				<?php echo $this->fetch( 'comment/item/report.php' ); ?>

				<!-- Permalink span.kmt-permalink-wrap -->
				<?php echo $this->fetch( 'comment/item/permalink.php' ); ?>

				<!-- AdminTools span.kmt-admin-wrap -->
				<?php echo $this->fetch( 'comment/item/admintools.php' ); ?>
			</span>
		</div>
		<?php echo Komento::trigger( 'onAfterKomentoItemHead', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>

		<div class="kmt-body">
			<i></i>

			<?php echo Komento::trigger( 'onBeforeKomentoItemContent', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>
			<!-- Comment div.kmt-text -->
			<?php echo $this->fetch( 'comment/item/text.php' ); ?>
			<?php echo Komento::trigger( 'onAfterKomentoItemContent', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>

			<?php echo Komento::trigger( 'onBeforeKomentoItemAttachment', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>
			<!-- Attachment div.kmt-attachments -->
			<?php echo $this->fetch( 'comment/item/attachment.php' ); ?>
			<?php echo Komento::trigger( 'onAfterKomentoItemAttachment', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>

			<?php echo Komento::trigger( 'onBeforeKomentoItemInfo', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>
			<!-- Info span.kmt-info -->
			<?php echo $this->fetch( 'comment/item/info.php' ); ?>
			<?php echo Komento::trigger( 'onAfterKomentoItemInfo', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>
		</div>

		<div class="kmt-control">

			<?php echo Komento::trigger( 'onBeforeKomentoItemMeta', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>
			<div class="kmt-meta">
				<!-- Time span.kmt-time -->
				<?php echo $this->fetch( 'comment/item/time.php' ); ?>

				<!-- Location span.kmt-location -->
				<?php echo $this->fetch( 'comment/item/location.php' ); ?>
			</div>
			<?php echo Komento::trigger( 'onAfterKomentoItemMeta', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>

			<?php echo Komento::trigger( 'onBeforeKomentoItemUserAction', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>
			<div class="kmt-control-user float-wrapper">
				<!-- Likes span.kmt-like-wrap -->
				<?php echo $this->fetch( 'comment/item/likesbutton.php' ); ?>

				<!-- Share div.kmt-share-wrap -->
				<?php echo $this->fetch( 'comment/item/sharebutton.php' ); ?>

				<!-- Reply span.kmt-reply-wrap -->
				<?php echo $this->fetch( 'comment/item/replybutton.php' ); ?>
			</div>
			<?php echo Komento::trigger( 'onAfterKomentoItemUserAction', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>
		</div>
	</div>
</div>
</li>
<?php echo Komento::trigger( 'onAfterKomentoItem', array( 'comment' => &$row, 'system' => &$system, 'options' => &$options ) ); ?>

<?php
/**
 * @package	Acymailing for Joomla!
 * @version	4.0.1
 * @author	acyba.com
 * @copyright	(C) 2009-2012 ACYBA S.A.RL. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>	<h1><?php echo $this->mail->subject ?></h1>
	<div class="newsletter_body" >
	<?php echo $this->mail->sendHTML ? $this->mail->body : nl2br($this->mail->altbody); ?>
	</div>

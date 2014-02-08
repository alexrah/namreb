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
$name = 'Build Bio';
$description = '<img src="media/com_acymailing/templates/newsletter-6/newsletter-6.png" />';
$body = JFile::read(dirname(__FILE__).DS.'index.html');

$styles['tag_h1'] = 'font-weight:bold; font-size:14px;color:#3c3c3c !important;margin:0px;';
$styles['tag_h2'] = 'color:#b9cf00 !important; font-size:14px; font-weight:bold; margin-top:20px; border-bottom:1px solid #d6d6d6; padding-bottom:4px;';
$styles['tag_h3'] = 'color:#7e7e7e !important; font-size:14px; font-weight:bold; margin:20px 0px 0px 0px; border-bottom:1px solid #d6d6d6; padding-bottom:0px 0px 4px 0px;';
$styles['tag_h4'] = 'color:#879700 !important; font-size:12px; font-weight:bold; margin:0px; padding:0px;';
$styles['color_bg'] = '#3c3c3c';
$styles['tag_a'] = 'cursor:pointer; color:#a2b500; text-decoration:none; border:none;';
$styles['acymailing_online'] = 'color:#dddddd; text-decoration:none; font-size:11px; margin:10px; text-align:center;';
$styles['acymailing_unsub'] = 'color:#dddddd; text-decoration:none; font-size:11px; text-align:center;';
$styles['acymailing_readmore'] = 'cursor:pointer; color:#ffffff; background-color:#b9cf00; border-top:2px solid #b9cf00; border-bottom:2px solid #b9cf00; border-right:4px solid #b9cf00; border-left:4px solid #b9cf00;';


$stylesheet = 'table, div, p{
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size:11px;
	color:#575757;
}

.footer a{
	color:#575757;}
';

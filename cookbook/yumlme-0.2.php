<?php if (!defined('PmWiki')) exit();
/**
 * yumlme.php
 * 
 * Copyright 2010 Volker Eichhorn 
 * This file is distributed under the terms of the GNU General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * Module to create image links to engine room's yUML online diagram service.  
 * Documentation for yUML syntax is available here: 
 * http://yuml.me/diagram/scruffy/class/draw
 *
 * (:yuml:)
 * [Customer]+1->*[Order]
 * [Order]++1-items >*[LineItem]
 * [Order]-0..1>[PaymentMethod]
 * (:yumlend:)
 * 
 * Gets converted into an image tag with this source:
 *
 * http://yuml.me/diagram/class/[Customer]+1->*[Order],\ 
 *              [Order]++1-items >*[LineItem], [Order]-0..1>[PaymentMethod] 
 *
 *
 * To use this module, simply place this file in the cookbook/ directory and
 * add the following line into config.php:
 *
 *    include_once("$FarmD/cookbook/yumlme.php");
 *
 *
 *  2010-10-10 added activity diagram (thanks Zatelli)
 *  2009-06-15 initial release
 *
 */
$RecipeInfo['YumlMe']['0.2'] = '2010-10-10';

function yumleize($str,$sargs) {
	$args = ParseArgs($sargs);
	$url = "http://yuml.me/diagram/";
	$bscale = strstr($sargs,'scale');
	$bscruffy = strstr($sargs,'scruffy');
	$busecase = strstr($sargs,'usecase');
	$bactivity = strstr($sargs,'activity');
	if($bscruffy) { $url .= 'scruffy'; }
	if($bscruffy&&$bscale) { $url .= ';'; }
	if($bscale) { $url .= 'scale:'.$args['scale']; }
	if($bscruffy||$bscale) { $url .= '/'; }
	if($busecase) { $url .= 'usecase/'; } 
	elseif($bactivity) { $url .= 'activity/'; }
	else { $url .= 'class/'; }
	$url .= trim(preg_replace('/(\<:vspace\>|[\\r\\n])+/imsx', ',', $str),', ');
	return Keep('<img src="'.$url.'.png" />');
}

Markup('yuml',
	'fulltext', 
	'/\\(:yuml(.*?):\\)(.*?)\\(:yumlend:\\)/sxi',
	function($matches) { return yumleize($matches[2],$matches[1]);} );


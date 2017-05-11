<?php
/*
     http://www.cyaneus.net
     
     By Cárlisson Galdino <bardo@swissinfo.org>
     
     This cookbook for PmWiki 2 uses S5 http://www.meyerweb.com/eric/tools/s5/
     
     
     Installing:
     
        Download S5 and uncompress it to pub/s5 folder in your "Farm" directory

     Using:
     
        Include this (slideshow.php) file in your config.php.
        Create a page using "!" titles-1level to define slides and then
	access the page with action=slideshow
     
     History
     	1.2: added contribution by JonHaupt
*/

$SlidesSkin = "pmwiki";

# Slideshow skin list
global $SlideShowSkinList;
SDVA($SlideShowSkinList, array (
        'pmwiki' => 'pmwiki',
        'blue' => 'blue',
        'flower' => 'flower',
        'pixel' => 'pixel',
        'pretty' => 'pretty',
        ));

# ?theme= to specify a particular theme on display
if(isset($_REQUEST['theme'])) {
    if (@$SlideShowSkinList[$_REQUEST['theme']]) {
      $SlidesSkin = ($_REQUEST['theme']);
    }
} else {
    if (! @$SlideShowSkinList[$SlidesSkin]) {
      $SlidesSkin = "pmwiki";
    }
}
  

SDV($HandleActions['slideshow'],'HandleSlides');  
Markup('slide','_begin','/\(:RSS *(.+):\)/', function($matches) { return RSS($matches[1]); });

SDV($SlideList,array());

SDV($SlideShowFmt, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>$Title</title>
<!-- metadata -->
<meta name="generator" content="S5" />
<meta name="version" content="S5 1.1" />
<!-- configuration parameters -->
<meta name="defaultView" content="slideshow" />
<meta name="controlVis" content="hidden" />
<!-- style sheet links -->

<link rel="stylesheet" href="$FarmPubDirUrl/s5/ui/default/slides.css" type="text/css" media="projection" id="slideProj" />
<link rel="stylesheet" href="$FarmPubDirUrl/s5/ui/default/outline.css" type="text/css" media="screen" id="outlineStyle" />
<link rel="stylesheet" href="$FarmPubDirUrl/s5/skins/$SlidesSkin/skin.css" type="text/css" media="screen" id="themeStyle" />
<link rel="stylesheet" href="$FarmPubDirUrl/s5/ui/default/print.css" type="text/css" media="print" id="slidePrint" />
<link rel="stylesheet" href="$FarmPubDirUrl/s5/ui/default/opera.css" type="text/css" media="projection" id="operaFix" />
<!-- embedded styles -->
<style type="text/css" media="all">
.imgcon {width: 525px; margin: 0 auto; padding: 0; text-align: center;}
#anim {width: 270px; height: 320px; position: relative; margin-top: 0.5em;}
#anim img {position: absolute; top: 42px; left: 24px;}
img#me01 {top: 0; left: 0;}
img#me02 {left: 23px;}
img#me04 {top: 44px;}
img#me05 {top: 43px;left: 36px;}
</style>
<!-- S5 JS -->
<script src="$FarmPubDirUrl/s5/ui/default/slides.js" type="text/javascript"></script>
</head>
<body>

<div class="layout">
<div id="controls"><!-- DO NOT EDIT --></div>
<div id="currentSlide"><!-- DO NOT EDIT --></div>
<div id="header"></div>

<div id="footer">
<h1>PmWiki Slideshow - running from <a href="$PageUrl">$WikiTitle</a></h1>
<h2>Powered by <a href="http://www.meyerweb.com/eric/tools/s5/" title="A Simple Standards-Based Slide Show System">S5</a></h2>
</div>

</div>
<div class="presentation">');

SDV($SlideSoloFmt,'
<div class="slide">
$SlideContent
</div>');
SDV($HandleSlideShowFmt,array(&$SlideShowFmt,&$SlideList,'</div></body></html>'));

function HandleSlides($pagename, $auth = 'read') {
  global $SlideShowFmt, $SlideSoloFmt,
    $SlideList,$RssItemFmt,
    $HandleSlideShowFmt,$FmtV,$ScriptUrl,$Group,$Name;
	 
  $t = ReadTrail($pagename,$pagename);
  $page = RetrieveAuthPage($pagename, $auth, false, READPAGE_CURRENT);
  if (!$page) Abort("?cannot read $pagename");
  
  $cbgmt = $page['time'];
  $source = $page['text'];
  $number_of_items = preg_match_all('/\n\!([\ \w].*)/', $source, $titles); // get the number of items and the dates
  $body_of_items = preg_split('/\n\!([\ \w].*)/', $source); // get the number of items and the dates
  $titles = $titles[0];
for ($i = 0; $i < $number_of_items; $i++) {
  $FmtV['$SlideContent'] = MarkupToHTML($pagename, $titles[$i] . "\n" . $body_of_items[$i + 1]);
 $SlideList[] = FmtPageName($SlideSoloFmt, $pagename);
 }   
  PrintFmt($pagename,$HandleSlideShowFmt);
  exit();
}

?>

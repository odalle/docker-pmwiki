<?php if (!defined('PmWiki')) exit();
/**
  AutoTOC - Unobtrusive Automatic Table of Contents for PmWiki
  Written by (c) Petko Yotov 2011-2014    www.pmwiki.org/Petko

  This text is written for PmWiki; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published
  by the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version. See pmwiki.php for full details
  and lack of warranty.
*/
$RecipeInfo['AutoTOC']['Version'] = '20140403';


if (function_exists('Markup_e')) {
  Markup_e("AutoTOC", '<split', '/^\\(:([#*h]|quick)?(toc|tdm).*?:\\)\\s*$/im', 'initAutoTOC($pagename, 1)');
  Markup_e("noAutoTOC", 'directives', '/\\(:no(toc|tdm):\\)/i', "PZZ(\$GLOBALS['HTMLFooterFmt']['autotoc']='')");
}
else {
  Markup("AutoTOC", '<split', '/^\\(:[#*]?(toc|tdm).*?:\\)\\s*$/ime', 'initAutoTOC($pagename, 1)');
  Markup("noAutoTOC", 'directives', '/\\(:no(toc|tdm):\\)/ie', "PZZ(\$GLOBALS['HTMLFooterFmt']['autotoc']='')");
}

SDV($AutoTocPrefix, '');
SDV($AutoTocIndent, '&nbsp;&nbsp;&nbsp;&nbsp;');
SDV($AutoTocMaxLevel, 6);
SDV($AutoTocNbHeadings, 3);
SDV($AutoTocCustomFold, array());
SDV($AutoTocPosition, '');
SDV($AutoTocUrl, '$FarmPubDirUrl/autotoc.js');
SDV($AutoTocIdEncoding, 'default');

function initAutoTOC($pagename, $directive = 0) {
  global $HTMLFooterFmt, $AutoTocUrl, $AutoTocIdEncoding, $AutoTocPosition, $AutoTocCustomFold, $AutoTocNbHeadings;
  if((!$directive) && $AutoTocNbHeadings<0) return;
  static $called;
  $f = array();
  foreach($AutoTocCustomFold as $k=>$v) $f[] = "\"$k\": $v";
  SDVA($HTMLFooterFmt, array(
  'autotoc' => '<script type="text/javascript"><!--
var i18nTOC = { contents: "$[Contents]", none: "$[show]", block: "$[hide]", levels: $AutoTocMaxLevel, 
  prependToDiv:"'.$AutoTocPosition.'", nbheadings: $AutoTocNbHeadings, prefix: "$AutoTocPrefix", 
  indent: "$AutoTocIndent", idenc: "'.$AutoTocIdEncoding.'", custfold: { '
  . implode(', ', $f) .' } };//--></script>
<script type="text/javascript" src="'.$AutoTocUrl.'"></script>'
  ));
  if($directive) return "<:block,1>".Keep("<div class='AutoTOCdiv'></div>");
}

if($VersionNum>=2002017) $PostConfig['initAutoTOC'] = 50;
else initAutoTOC($pagename);

<?php if (!defined('PmWiki')) exit();
/*
    Adds a range of markup extensions to PmWiki 2. Combines a number 
    of items that support Wikipublisher.

    Version 2.1.1 works with PmWiki 2.2.56 or above
    Requires php 5.3 or above and is compatible with php 5.5

    Copyright 2004-2014 John Rankin (john.rankin@affinity.co.nz)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    
    lazy web links Copyright 2004 Patrick R. Michaud (pmichaud@pobox.com)  
*/

SDV($MarkupCss,false);
if ($MarkupCss) $HTMLHeaderFmt[] = 
 "<link rel='stylesheet' href='\$FarmPubDirUrl/css/markup.css' type='text/css' />\n";
else $HTMLStylesFmt['extend'] = "
a.createlink { color: red; }
#wikitext { line-height: 1.4em; }
#wikitext sub, #wikitext sup { line-height: 0; }
div.footnote { 
    width: 10em; 
    border-bottom: 1px solid blue;
	margin-bottom: 0.5em;
}
p.footnote {
	text-indent: -1em;
	margin-right: 3em;
	margin-left: 3em;
	margin-top: 0px;
	margin-bottom: 0.5em;
	font-size: smaller;
}
p.qanda:first-letter {
    float: left;
    font-family: Old English, Georgia, serif;
    color: #777777;
    font-size: 250%;
    line-height: 0.85em;
    margin-right: 0.3em;
    margin-bottom:-0.25em;
}
p.drop:first-letter {
    float: left;
    font-family: Old English, Georgia, serif;
    font-size: 290%;
    line-height: 0.85em;
    margin-right: 0.1em;
    margin-bottom:-0.25em;
}
p#phone:before    { content: '\260E\a0'; display: inline; }
p#location:before { content: '\2709\a0'; display: inline; }
dt#copy:after     { content: ':'; }
p#address { text-align: right; }
p#closing { margin-top: 1.67em; }
p#name    { margin-left:40px; text-indent:-40px; }
dt#copy   { float: left; }
dt.signed { float: left; }
del { color: red; }
ins { background-color: yellow; }
span.highlight { background-color: #b2ffa1; }
div.inote {
    font-size: 80%;
    line-height: 1.2em;
    float: right;
    padding: 2px;
    margin-left: 10px;
    margin-bottom: 10px;
    width: 18em;
    border-top: 1px dotted gray;
    border-bottom: 1px dotted gray;
    background-color: #ffffa1;
}
div.inote h1 {
    font-weight: normal;
    background-color: #ffe53e;
    font-size: 100%;
    margin-top: -2px;
	margin-left: -2px;
	margin-right: -2px;
    margin-bottom: 3px;
    padding: 2px;
}
div.inote h1 span.inote {
    float: right;
}
div.inote ul, div.inote ol { margin-left: -1.5em; }
div.inote p.vspace { margin-top:0.5em; }
span.stickynote {
    font-size: smaller;
    float: right;
    padding: 8px;
    margin-left: 10px;
    margin-bottom: 10px;
    width: 15em;
    border-top: 2px solid gray;
    border-bottom: 2px solid gray;
    text-align: center;
    color: navy;
    background-color: #dcdcdc;
}
span.mnote {
    font-size: smaller;
    float: right;
    padding: 3px;
    margin-left: 0.5em;
    border: 1px solid #cccccc;
    background-color: #ffffcc;
}
span.smallcaps { font-variant: small-caps; }
dfn  { font-style: normal; cursor: help; }
abbr { font-style: italic; cursor: help; }
abbr, dfn.definition { border-bottom: 1px dotted; }
h5.runin { display: run-in; font-size: 100%; border: none; }
div.figure {
    border: thin silver solid;
    padding: 0.3em;
}
div.figure p {
    text-align: center;
    font-style: italic;
    font-size: smaller;
    padding-top: 0.2em;
    margin: 0;
}
dd, li p { margin-bottom: 0.5em }
b.selflink { border-bottom: 1px dotted; }
@media screen{ #wikitext b.selflink { color: #e66e31; } }
";

## By default, all markup extensions are enabled. If you want to disable
## them all by default, set $MarkupExtensionsDefaultState = false before
## you include markup.php then turn on just the ones you want.
SDV($MarkupExtensionsDefaultState, true);
SDV($MarkupExtensionsFmt,
    array("inote abbr `A `. `- `s `: `f -d ... aquo mac '/ '@ '; [^ copy ref",
    "q&a A; {|} =| {= revisions ^!! fig :: para lazyweb spaced squo links"));
foreach(explode(' ',implode(' ',$MarkupExtensionsFmt)) as $me)
    SDV($MarkupExtensions[$me], $MarkupExtensionsDefaultState);


if ($MarkupExtensions['inote']) {
    SDV($InoteTextFmt, "[[\$FullName|\$Title]]" . 
        " <span class='inote'>([[\$FullName?action=edit|edit]])</span>");
    SDV($InoteExpiredFmt, "Expired \$LastModified");
    if ($action=="print" || $action=="publish")
        Markup('inote','>if',"/\\(:inote\\s+.*?:\\)/",'');
    else 
        Markup_e('inote','>if',
    "/\(:inote\s+(?:days=(\d+)\s+)?((?:$GroupPattern(?:[\/.]))?$NamePattern)(.*?):\)/",
    "PRR().IncludeNoteText(\$pagename,array('days' => \$m[1], 'fmt' => \$GLOBALS['InoteExpiredFmt']),\$m[2],\$m[3],\$GLOBALS['InoteTextFmt'])");
}

function IncludeNoteText($pagename,$t,$page,$opts,$fmt) {
  global $Now,$PCache;
  $age = $Now - 86400 * (($t['days']) ? $t['days'] : 365);
  $p = MakePageName($pagename,$page);
  PCache($p,RetrieveAuthPage($p, 'read', false, READPAGE_CURRENT));
  $i = ($PCache[$p]['time'] >= $age) ? preg_replace('/\(:title\s+.*?:\)/', '',
        IncludeText($pagename,"include $p$opts")) : FmtPageName($t['fmt'],$p);
  return "<div class='inote'>\n!".FmtPageName($fmt,$p)."\n$i\n<:block></div>";
}

## prevent wikiwords with only one lower case letter
SDV($AbbreviationPattern,
  "[[:upper:]]+(?:[[:upper:]][[:lower:]0-9]|[[:lower:]0-9][[:upper:]])[[:upper:]]*");
if ($MarkupExtensions['abbr']) {
    $AbbreviationEnabled = true;
    Markup_e("abbr",'>`wikiword',
      "/\\b((?:Ma?c[[:upper:]][[:lower:]]+)|(?:$AbbreviationPattern))\\b([.\/]$WikiWordPattern)?/",
      "\$m[2] ? \$m[0] : Keep(((PageExists(MakePageName(\$pagename,\$m[1]))) ? MakeLink(\$pagename, \$m[1], \$m[1]) : \$m[1]), 'L')");
    Markup('`abbr','inline',
      "/`([[:upper:]][-[:upper:]0-9]*)([[:lower:]])?\\b([.\/]?$NamePattern)?/",
      "abbrHelper");
}
function abbrHelper($m) {
  return $m[3] ? (ProbablyAnAbbreviation($m[2].$m[3])
                    ? '<small>'.$m[1].$m[3].'</small>' : $m[0])
               : '<small>'.$m[1].'</small>'.$m[2];
}
function ProbablyAnAbbreviation($txt) {
  return preg_match('/^\/[[:upper:]]+$/',$txt);
}

#### escape character (backtick) ####
## prevent WikiWords with Wiki`Word  and `WikiWord markups
if ($MarkupExtensions['`A'])
    Markup("`A",'>links','/([[:alnum:].\/])?`([[:upper:]])/','$1$2');

## '`.' (invisible stop)
if ($MarkupExtensions['`.'])
    Markup("`.",'>links',"/`\./",'');

## '`-' (en dash) 
if ($MarkupExtensions['`-'])
    Markup("`-",'inline',"/`-/",'&ndash;');

## '` ' (nonbreaking space)
if ($MarkupExtensions['`s'])
    Markup("`s",'inline',"/`\s(\s)?/","nbspHelper");
function nbspHelper($m) { return ($m[1] ? '&em' : '&nb').'sp;'; }

## '`:' (middot)
if ($MarkupExtensions['`:'])
    Markup("`:",'inline',"/`:/",'&middot;');

## simple fractions (quarter, half, three quarters)
if ($MarkupExtensions['`f']) {
    Markup("1/4",'inline',"/`1\/?4/",'&#188;');
    Markup("1/2",'inline',"/`1\/?2/",'&#189;');
    Markup("3/4",'inline',"/`3\/?4/",'&#190;');
    Markup("`/",'inline',"/`\//",'&divide;');
    Markup("`*",'inline',"/`\*/",'&times;');
    Markup("gle",'inline',"/(?:&([gl])t;|\/)=/","gleHelper");
}
function gleHelper($m) { return '&'.($m[1] ? $m[1] : 'n').'e;'; }

## em dash, en dash, plus or minus, and minus
if ($MarkupExtensions['-d']) {
    Markup("--",'>[+',"/(^|[^-!;])--([^-&>]|$)/",'$1&mdash;$2');
    Markup("mm",'>--',"/([^-!;])----([^-&>]|$)/",'$1&mdash;&mdash;$2');
    Markup("d-d",'>links',"/(\\d)-(\\d)/",'$1&ndash;$2');
    Markup("dxd",'>links',"/(\\d)x(\\d)/",'$1&times;$2');
    Markup("+-",'<-d',"/\+\/?-/",'&plusmn;');
    Markup("-d",'>d-d',"/([^'\":[:alpha:]])-(\\d)/",'$1&minus;$2');
}

## ellipsis ...
if ($MarkupExtensions['...'])
    Markup("...",'inline',"/\.\.\./",'&hellip;');

if ($MarkupExtensions['aquo']) {
## left and right arrows
    Markup("<->",'<<-',"/&lt;--?&gt;/",'&harr;');
    Markup("<-",'<lsa',"/&lt;--?/",'&larr;');
    Markup("->",'>block',"/--?&gt;/",'&rarr;');

## angle brackets
    Markup("aquo",'>links',"/&lt;&lt;(.*?)&gt;&gt;/",'&laquo;$1&raquo;');
    Markup("lsa",'>aquo',"/(&lt;)(.*?)\|/","lsaHelper");
    Markup("rsa",'>->',"/\|(.*?)(&gt;)/","rsaHelper");
}
function lsaHelper($m) {
  return (strstr($m[2],'&gt;') ? $m[1] : '&larr;').$m[2].'|';
}
function rsaHelper($m) {
  return '|'.$m[1].(strstr($m[1],'&lt;') ? $m[2] : '&rarr;');
}

## long vowels (macrons)
if ($MarkupExtensions['mac']) {
    $LongVowels = array (
    'A' => '&#256;',
    'a' => '&#257;',
    'E' => '&#274;',
    'e' => '&#275;',
    'I' => '&#298;',
    'i' => '&#299;',
    'O' => '&#332;',
    'o' => '&#333;',
    'U' => '&#362;',
    'u' => '&#363;');
    Markup("mac",'directives',"/(?:&|{)([AaEeIiOoUu])(?:m;|})/","macHelper");
    $FmtPV['$Titlespaced'] = 
    'TitleLatex(@$page["title"] ? Macronise($page["title"]) : 
       $AsSpacedFunction($name))';
    $FmtPV['$Title'] = 
    '@$page["title"] ? TitleLatex(Macronise($page["title"])) : 
      ($GLOBALS["SpaceWikiWords"] ? TitleLatex($AsSpacedFunction($name)) : 
       $name)';
}

function TitleLatex($text) {
  global $EnableTitleLatex;
  SDV($EnableTitleLatex, false);
  if ($EnableTitleLatex) $text = preg_replace('/(?:La)?TeX/','&$0;',$text);
  return $text;
}

function macHelper($m) {
  return Macron($m[1]);
}
function Macron($vowel) {
  global $LongVowels;
  return $LongVowels[$vowel];
}
function Macronise($text) {
  return preg_replace_callback(
             "/(?:&|{)([AaEeIiOoUu])(?:m;|})/",
             "macHelper",
             $text);
}

#### inline markups ####
## '/cite/'
if ($MarkupExtensions["'/"])
    Markup("'/","<'''''","/'\/(.*?)\/'/",'<cite>$1</cite>');

## '@keyboard@'
if ($MarkupExtensions["'@"]) {
    Markup("'@","<'''''","/'@(.*?)@'/",'<kbd>$1</kbd>');
    Markup("''@@","<'@","/(''+)(@@.*?@@)(''+)/",'$1 $2 $3');
}

## ';small caps;'
if ($MarkupExtensions["';"])
    Markup("';","<'''''","/';(.*?);'/",'<span class=\'smallcaps\'>$1</span>');

## [^footnote text^] and [^#^] to list footnotes
## includes a style to tidy line spacing
if ($MarkupExtensions['[^']) {
    Markup("[^",'>links','/\[\^(.*?)\^\]/',"Footnote");
    Markup("^[^",'<[^',  '/^\[\^(#)\^\]$/',"Footnote");
}

function Footnote($m) {
  static $fngroup, $fncount, $fntext;
  if ($m[1] == "#") {
     if ($fncount) {
         $r = "<:block><div class='footnote'>&nbsp;</div>$fntext";
         $fntext = ''; $fncount = 0; $fngroup++;
     } else $r = '';
  } else {
     $fncount++; $fnid = $fngroup+1 . '_' . $fncount;
     $r = "<a name='fnr$fnid' id='fnr$fnid'></a>".
        "<sup><a href='#fn$fnid'>$fncount</a></sup>";
     $fntext .= "<p class='footnote'><a name='fn$fnid' id='fn$fnid'></a>".
        "<sup>$fncount</sup> $m[1] <a href='#fnr$fnid'>(&uarr;)</a></p>";
  }
  return $r;
}

## copyright and related entities
if ($MarkupExtensions['copy']) {
    Markup("copy",'inline',"/\([cC]\)/",'&copy;');
    Markup("trade",'inline',"/\((?:tm|TM)\)/",'&trade;');
    Markup("reg",'inline',"/\([rR]\)/",'&reg;');
}

## figure and table references, including custom float settings
if ($MarkupExtensions['ref']) {
    SDV($ReferencePrefixFmt, array('FIG' => '$[this figure]', 
        'TAB' => '$[this table]',  'DIV' => '$[this excerpt]'));
    SetCfloatName(isset($CfloatName) ? $CfloatName : 'Excerpt');
    $ReferenceList = array();
    Markup_e('figref','inline',
        '/`?(FIG|Fig|TAB|Tab|DIV|Div)\(([A-Za-z][-.:\\w]*)\)/',
        "FigureRef(\$pagename,\$m[1],\$m[2])");
    Markup('figlist', '<split',
  "/(?>%([A-Za-z][-,.=:#\\w\\s'\"]*)%\\s*\\[*)[^\\s$UrlExcludeChars]+$ImgExtPattern\"([^\"]*)\"/i",
  "figlistHelper");
    Markup('tablist', '<split',
  '/(?>(id=[A-Za-z][^\s%"]*))\s*"([^"]*)"/i',
  "tablistHelper");
    Markup('cfloat', '<table',
  '/^(\(:div.*?)summary="([^"]*)"(.*?:\))\s*$/',
  '$1$3<p><strong>$2</strong></p>');
    $FmtPV['$CfloatName'] = '$GLOBALS["CfloatName"]';
    $FmtPV['$CfloatList'] = '$GLOBALS["CfloatList"]';
    Markup('markuplist', '<markup',
  '/(?>(\(:markup\s+)(id=([A-Za-z][^\s%"]*)))\s*"([^"]*)"/i',
  "markupHelper");
    Markup('markupid','>restore','/(><caption>)([^\|\s<]*?)\|/',' id="$2"$1');
}
function figlistHelper($m) {
  return $m[0].MakeFigureList($m[1],$m[2]);
}
function tablistHelper($m) {
  return $m[1].' summary="'.$m[2].'"'.MakeFigureList($m[1],$m[2]);
}
function markupHelper($m) {
  return $m[1].' caption="'.$m[3].'|'.$m[4].'"'.MakeFigureList($m[2],$m[4]);
}
function SetCfloatName($cfloat, $clist = NULL) {
  global $CfloatName, $CfloatList, $ReferencePrefixFmt, $format;
  if (preg_match('/(.*?)\s*"([^"]+)"/',$cfloat,$c)) { $cfloat=$c[1]; $clist=$c[2]; }
  $CfloatName = $cfloat;
  $CfloatList = (is_null($clist)) ? 'List of ' . $cfloat . 's' : $clist;
  $ReferencePrefixFmt['DIV'] = 
    '$['.(($format=='pdf') ? '' : 'this ').strtolower($cfloat[0]).substr($cfloat,1).']';
}
function FigureRef($pagename, $kind, $id, $pn='') {
  global $ReferencePrefixFmt, $ReferenceList;
  if (strstr('FigTabDiv', $kind)) {
      $kind = strtoupper($kind);
      $Fcap = true;
  } else
      $Fcap = false;
  if ($pn) $txt = 
      FmtPageName($ReferencePrefixFmt[$kind].' $[on] ', $pagename).$pn;
  else
      $txt = isset($ReferenceList[$id]) ? $ReferenceList[$id] :
          FmtPageName($ReferencePrefixFmt[$kind], $pagename);
  if ($Fcap) $txt[0] = strtoupper($txt[0]);
  return "[[$pn#$id | $txt]]";
}

function MakeFigureList($style, $alt) {
  global $ReferenceList;
  if (preg_match('/id=([^\s]*)/',$style,$m)) $ReferenceList[$m[1]] = $alt;
}

## Q: and A: markup
if ($MarkupExtensions['q&a']) {
    $HTMLStylesFmt['q&a'] = "
p.question { margin-top: 2.0em; }
p.question:first-letter {
    float: left;
    font-family: Old English, Georgia, serif;
    color: #777777;
    font-size: 200%;
    line-height: 1.0em;
    margin-right: 0.2em;
}";
    Markup('^Q:', 'block', '/^Q:(.*)$/', "<:block><p class='question'>Q$1</p>");

## letter markup
    SDV($LetterSignedChar, '&#10002;');
    foreach(array(
        'L' => '%id=letter apply=block%(:linebreaks:)',
        'R' => '%id=address apply=block%(:linebreaks:)',
        'S' => '!!!%id=subject apply=block%(:nolinebreaks:)',
        'D' => '%id=opening apply=block%Dear ',
        'Y' => '%id=closing apply=block%Yours sincerely<br /><br /><br />(:linebreaks:)',
        'N' => '%id=name apply=block%',
        'C' => ':cc:%id=copy apply=block%',
        'F' => '%id=location apply=block%(:nolinebreaks:)',
        'P' => '%id=phone apply=block%',
        'X' => ':'.$LetterSignedChar.':%class=signed apply=block%') as $k => $v) 
            SDV($LetterMarkupStyles[$k], $v);
    Markup_e('countersign', '<letter', '/^:?N([QA]):/',
      "'N:'.TranslateCountersign(\$pagename,\$m[1]).': <br />'");
    Markup('letter', '<directives', '/^:?([LRSDYNCFPX]):/',
      "letterHelper");
    function TranslateCountersign($pagename,$type) { return 
      FmtPageName('$['.(($type=='A') ? 'Accepted and agreed' : 'Quality review').']',$pagename); }
}
function letterHelper($m) {
  return $GLOBALS['LetterMarkupStyles'][$m[1]];
}

## Z; dropcaps markup
if ($MarkupExtensions['A;'])
    Markup('A;','block','/^([[:upper:]]);(([^;&]*(&[^;]+;)*)*);(.*)$/',
    '<:block><p class=\'drop\'>$1<span class=\'smallcaps\'>$2</span>$5</p>');

## {abbr|abbreviations}, {:term:definitions}, =< left & =>right aligned text
if ($action=="print" || $action=="publish") {
    if ($MarkupExtensions['{|}']) {
        Markup("{|}",'>links',"/\{(.*?)\|(.*?\}?)\}/",'$1 ($2)');
        Markup("{:}",'>&',
        "/\{:((?:\[\[[^\]]+\]\])?\{?[^:\}]*?\}?):(.*?\}?)\}/",
        "dfnpHelper");
    }
    if ($MarkupExtensions['=|'])
        Markup('^=>','block','/^=&[gl]t;(.*)$/','<:block>');
    $hide = 2;
} else {
    if ($MarkupExtensions['{|}']) {
        Markup("{|}",'>links',
        "/\{(.*?)\|(.*?\}?)\}/",'<abbr title=\'$2\'>$1</abbr>');
        Markup("{:}",'>&',
        "/\{:((?:\[\[[^\]]+\]\])?\{?[^:\}]*?\}?):(.*?\}?)\}/",
        "dfnHelper");
    }
    if ($MarkupExtensions['=|']) {
        Markup('^=>','block','/^=&gt;(.*)$/',
        '<:block><p style=\'text-align: right\'>$1</p>');
        Markup('^=<','block','/^=&lt;(.*)$/',
        '<:block><p style=\'text-align: left\'>$1</p>');
    }
}

function dfnpHelper($m) {
  return $m[1].' ['.Keep(trim($m[2])).']';
}
function dfnHelper($m) {
  return '<dfn title='.Keep(DfnTitle($m[2],$m[1])).'>'.$m[1].'</dfn>';
}

function DfnTitle($title,$text) {
    $title = str_replace('"','&quot;',trim($title));
    $title = (strstr($title,"'")) ? '"'.$title.'"' : "'$title'";
    return (preg_match('/^[[:alnum:]].*$/',$text)) ? 
        "$title class='definition'" : $title;
}

## =| centred text
if ($MarkupExtensions['=|'])
    Markup('^=|','block','/^=([?|])(.*)$/',"centerHelper");

function centerHelper($m) {
  return '<:block><p'.(($m[1]=='|') ? ' style="text-align: center"' : '').'>'.
    $m[2].'</p>';
}

## {+insertions+}, {-deletions-}, (:revisions:), {=sticky notes=}, {*highlight*}
SDV($hide, isset($_GET['hide']) ? $_GET['hide'] : 0);
$pgnum = isset($_GET['p']) ? "?p=".$_GET['p'] : '';
if ($hide) {
    if ($MarkupExtensions['{=']) {
        Markup("{=",'inline',"/{=(.*?)=}/",'');
        Markup("{*",'inline',"/{\*(.*?)\*}\s*/",'');
    } 
    if ($MarkupExtensions['revisions']) {
        Markup("{+",'inline',"/{\+(.*?)\+}/",'$1');
        Markup("{-",'inline',"/{\-(.*?)\-}/",'');
        if ($hide==1) Markup('revisions','<${fmt}','/\(:revisions:\)/',
            '[[{$Name}?hide=0'.$pgnum.' | Show revisions]]');
        else Markup('revisions','directives','/\(:revisions:\)/','');
    }
} else { 
    if ($MarkupExtensions['{=']) {
        Markup("{=",'inline',"/{=(.*?)(?:\|\s*(.*?))?=}/","noteHelper");
        Markup("{*",'inline',"/{\*(.*?)\*}/",'<span class="highlight">$1</span>');
    }
    if ($MarkupExtensions['revisions'])
        Markup('revisions','<${fmt}','/\\(:revisions:\)/',
        '[[{$Name}?hide=1'.$pgnum.' | Hide revisions]]');
}

function noteHelper($m) {
  return '<span class="stickynote"'.NoteStyle($m[2]).'>'.$m[1].'</span>';
}

function NoteStyle($color) {
    $colors = array(
            'yellow' => array('ffffa1','ffe53e'),
            'green'  => array('b2ffa1','95ff95'),
            'blue'   => array('71ffff','3ee5ff'),
            'purple' => array('b2c7ff','91b8ff'),
            'pink'   => array('ffc7c7','ffb2b2'),
            'grey'   => array('eeeeee','d4d4d4')
            );
    return ($colors[$color][0]) ? 
        " style='background-color:#" . $colors[$color][0] . 
        "; border-top:2px solid #" . $colors[$color][1] . 
        "; border-bottom:2px solid #" . $colors[$color][1] .";'"
        : '';
}

## !run-in heads!and text
if ($MarkupExtensions['^!!']) {
## add one extra <:vspace> after !headings
    Markup('!!vspace', '<!vspace', "/^(!(?>[^!\n]+![^\n]+)\n)/m",'$1<:vspace>');
    Markup('^!!','<^!','/^!([^!]+?)([.:?])?!(.*?)$/',"runinHelper");

## aphorisms
    Markup('^;;','block','/^;([^;]*);\s*(.*?)$/',"aphorismHelper");
}
function runinHelper($m) {
  return '<:block><h5 class="runin">'.$m[1].($m[2] ? $m[2] : '.').'</h5><p> '.
    $m[3].'</p>';
}
function aphorismHelper($m) {
  if (preg_match('/(&[^\s]+)$/',$m[1])) {
      $m[1] .= ';';
      $aph = preg_replace('/(&[^;]+);/',"$1<>",$m[2]);
      if (preg_match('/^([^;]*);\s*(.*?)$/',$aph,$match)) {
          $m[1] .= str_replace('<>',';',$match[1]);
          $m[2]  = str_replace('<>',';',$match[2]);
      }   
  }
  return '<:block><blockquote>'.$m[2].
    ($m[1] ? ' &mdash; <cite>'.$m[1].'</cite>' : '').'</blockquote>';
}

## figure captions
if ($MarkupExtensions['fig'])
    Markup('fig','<links',
    "/^=figure\s+((?:\[\[)?.*?$ImgExtPattern\"([^\"]*)\"(?:\]\])?)\s*(.*?)$/",
    "figHelper");

function figHelper($m) {
  return '<:block><div class="figure"><p>'.$m[1].'</p><p>'.
    ($m[3] ? $m[3] : $m[2]).'</p></div>';
}

if ($MarkupExtensions['::']) {
## tidy :: used merely to indent
    Markup('^::2: :','<^: :2->','/^(:+)(:[^:]+)$/','$1 $2');
    Markup('^: :2->','<^::','/^(:+)\\s+:/',
    "indentHelper");

## :: or :+ for multiple <dd> per <dt> and multipar item lists
    Markup('::$','<\\$',"/:[:+]\n/",':+');
    Markup(':+:','<^::','/^(:+.*?:)((?:.*?:\+.*?)+)$/',
    "ddHelper");
    Markup(':+*','<^*','/^([#*]+)((?:.*?:\+.*?)+)$/',
    "itemHelper");
    Markup(':+P','>:+*','/:\+/','<br />&nbsp; &nbsp; &nbsp;');

## remove first whitespace in a preformatted text block
    Markup('^pre ','>^ ','/^(<:pre,1>)\\s/','$1');
}
function indentHelper($m) {
  return str_replace(':','-',$m[1]).'&gt;';
}
function ddHelper($m) {
  return $m[1].str_replace(':+','</dd><dd>',$m[2]);
}
function itemHelper($m) {
  return $m[1].'<p>'.str_replace(':+','</p><p>',$m[2]).'</p>';
}

## teaser markups T[:*#] Name#id and (:para Name#id:)
if ($MarkupExtensions['para']) {
    Markup_e('para','directives',
    "/\(:para\s+(.+?)(?:#([^:\s]+))?(?:\s+(more|edit))?:\)/",
    "TeaseParagraph(\$pagename,\$m[1],\$m[2],\$m[3])");
    Markup_e('tfl','directives',"/^T([:*#]+)\s*(\[\[.+?\]\])/",
    "TeaserFL(\$pagename,\$m[1],\$m[2])");
    Markup_e('tww','directives',
    "/^T([:*#]+)\s*((?:$GroupPattern([\/.]))?$WikiWordPattern)/",
    "Teaser(\$pagename,\$m[1],\$m[2])");
    SDV($ParaBadAnchorFmt,"'''\$Anchor''' \$[not found in] \$FullName\n");
    SDV($DefaultTeaserAnchor,'teaser');
    SDV($TeaserMoreFmt,' ([[$FullName | more]])');
    SDV($TeaserEditFmt,' ([[$FullName?action=edit | edit]])');
    SDV($DefaultTeaserTextFmt,'Page [[$Group/$Namespaced]] is undefined.');
}

function TeaserFL($pagename,$markup,$linkword) {
  global $UrlExcludeChars,$DefaultTeaserAnchor;
  if (preg_match('/#wikipublisher\\.[^\\|]+\\|([^\\]]+)/',$linkword,$match))
      $link = $match[1];
  else
      $link = FLRef($linkword);
  if (preg_match("/^\\[\\[(.+?)#([^\\s$UrlExcludeChars]*)/",$linkword,$m)) {
      $link = str_replace('#'.$m[2],'',$link);
      $linkword = str_replace($m[1].'#'.$m[2],$m[1],$linkword);
      $anch = ($m[2]=='') ? $DefaultTeaserAnchor : $m[2];
  } else $anch = '';
  return "$markup$linkword: " . TeaseParagraph($pagename,$link,$anch,'');
}

function FLRef($linkword) {
  $l = preg_replace('/\\s*\\|[^\\]]+/','',$linkword);
  $l = preg_replace('/[^\\]]+-+&gt;\\s*/','',$l);
  $l = preg_replace('/[()]/','',$l);
  return preg_replace('/[#?][^\\s]+/','',$l);
}

function Teaser($pagename,$markup,$linkword) {
  return "$markup$linkword: " . TeaseParagraph($pagename,$linkword,'','');
}

function TeaseParagraph($pagename,$teasername,$teaseranch,$act=NULL) {
  global $ParaBadAnchorFmt,$TeaserMoreFmt,$TeaserEditFmt,$DefaultTeaserAnchor,
    $DefaultTeaserTextFmt, $Charset;
  $tname = MakePageName($pagename,$teasername);
  if ($tname==$pagename) return "''self reference omitted''";
  if ($act=='edit') $taction = str_replace('$FullName',$tname,$TeaserEditFmt);
  else $taction = '';
  $tpage=RetrieveAuthPage($tname,'read',false,'');
  if (isset($tpage['text'])) $ttext = $tpage['text'];
  else return FmtPageName($DefaultTeaserTextFmt,$teasername);
  $tgroup = FmtPageName('$Group',$tname);
  if ($teaseranch=='') {
      $tpara = CleanParagraph($pagename,$tgroup,
                    substr($ttext,0,strpos($ttext."\n","\n")));
      if ($act=='more') $taction=str_replace('$FullName',$tname,$TeaserMoreFmt);
  } elseif (preg_match("/\\[\\[#+$teaseranch\\]\\]\\n?([^\\n]+)/",$ttext,$m)) {
      $tpara = CleanParagraph($pagename,$tgroup,$m[1]);
      if ($act=='more') 
        $taction = str_replace('$FullName',"$tname#$teaseranch",$TeaserMoreFmt);
  } elseif ($teaseranch==$DefaultTeaserAnchor)
      $tpara = CleanParagraph($pagename,$tgroup,
                    substr($ttext,0,strpos($ttext."\n","\n")));
  else
      $tpara = str_replace('$Anchor',$teaseranch,
                    FmtPageName($ParaBadAnchorFmt,$tname));
  return htmlspecialchars($tpara,ENT_NOQUOTES,$Charset).$taction;
}

function CleanParagraph($pagename,$group,$para) {
  global $GroupPattern,$WikiWordPattern;
  if (preg_match('/^\\|\\|/',$para)) return "''tabular material omitted''";
  $pgroup = FmtPageName('$Group',$pagename);
  $p = preg_replace("/^[#*!]+\s*/","",$para);
  $p = preg_replace("/^:.*?:/","",$p);
  $p = preg_replace("/^([[:upper:]]);(.*?);/","$1$2",$p);
  $p = preg_replace("/`\\..*?$/","...",$p);
  $p = preg_replace("/\\[@(.*?)@\\]/","@@[=$1=]@@",$p);
  $p = preg_replace_callback(
           "/\\[=(.*?)=\\]/",
           function ($m) { return Keep($m[1]); },
           $p);
  $p = preg_replace("/\\(:title.*?:\\)/","",$p);
  $p = preg_replace("/\\[\\[#.*?\\]\\]/","",$p);
  $p = preg_replace_callback(
           "/([`:\/]?)\\b(($GroupPattern([\\/.]))?$WikiWordPattern)/",
           function ($m) use ($group,$pgroup) {
             return QualifyWLink($pgroup,$group,$m[1],$m[2]);
           },
           $p);
  $p = preg_replace_callback(
           "/\\[\\[(.*?)\\]\\]/",
           function ($m) use ($group,$pgroup) {
             return '[['.QualifyFLink($pgroup,$group,$m[1]).']]';
           },
           $p);
  $p = str_replace('::','',$p);
  return FmtPageName(preg_replace("/{(\\$.*?)}/",'$1',$p),$pagename);
}

function QualifyWLink($pgroup,$group,$esc,$link) {
  global $WikiWordCount,$WikiWordCountMax,$AbbreviationEnabled,
    $AbbreviationPattern;;
  if ($esc) return "$esc$link";
  if ($pgroup==$group) return $link;
  $wwcount = (isset($WikiWordCount[$link])) ? $WikiWordCount[$link] : 
    $WikiWordCountMax;
  if ($wwcount==0) return $link;
  if ($AbbreviationEnabled && preg_match("/^$AbbreviationPattern$/",$link))
    return $link;
  return (preg_match("/[.\\/]/",$link)) ? $link : QualifiedLink($group,$link);
}

function QualifyFLink($pgroup,$group,$link) {
  if ($pgroup==$group) return $link;
  $l = FLRef($link);
  return (preg_match("/[~!:.\\/]/",$l)) ? $link : 
            str_replace("$l",QualifiedLink($group,$l),$link);
}

function QualifiedLink($grp,$ref) {
  return ($grp.'1'==FmtPageName('$Group',MakePageName($grp.'1.'.$grp,$ref))) ?
         "$grp/$ref" : $ref;
}

## lazy web links (an alternative to the one from Pm)
if ($MarkupExtensions['lazyweb'])
    Markup_e('lazyweb','<wikilink',
    "/\\bwww\\.[^\\s$UrlExcludeChars]*[^\\s.,?!$UrlExcludeChars]/",
    "Keep(MakeLink(\$pagename,'http://'.\$m[0],\$m[0]),'L')");

## enhanced AsSpaced function
if ($MarkupExtensions['spaced']) {
    $SpaceWikiWords = 1;
    $AsSpacedFunction = 'SpaceWikiWords';
    $SpaceWikiWordsFunction = 'SpaceWikiWords';
    $RecentChangesFmt['$SiteGroup.AllRecentChanges'] =
        '* [[$FullName | $Group.$Title]]  . . . $CurrentTime $[by] $AuthorLink'.
        ': [=$ChangeSummary=]';
    $RecentChangesFmt['$Group.RecentChanges'] =
        '* [[$FullName | $Title]]  . . . $CurrentTime $[by] $AuthorLink'.
        ': [=$ChangeSummary=]';
    $DefaultPageTextFmt = 'Describe [[$Group/$Title]] here.';
    $StopList = array(
		'A',
		'An',
		'And',
		'As',
		'At',
		'But',
		'By',
		'For',
		'From',
		'In',
		'Is',
		'It',
		'Of',
		'On',
		'Or',
		'The',
		'To',
		'Vs',
		'With',
            );
    $UnspacedList = array(
        'Mac ',
        'Mc ',
        'Pm Wiki',
        'Side Bar',
        '*I Pod'
            );
}

function SpaceWikiWords($text) {
  global $StopList,$UnspacedList;
  $text = AsSpaced($text);
  $text = preg_replace("/([[:lower:]])(\\d[[:lower:]])/", '$1 $2', $text);
  foreach((array)$StopList as $s)
    $text = preg_replace_callback(
                "/(\\s$s\\s)/",
                function ($m) { return strtolower($m[1]); },
                $text);
  foreach((array)$UnspacedList as $u) {
    if ($u[0]=='*') {
        $u = substr($u,1); $uo = strtolower($u[0]) . substr($u,1);
    } else $uo = $u;
    $text = str_replace($u,str_replace(' ','',$uo),$text);
  }
  return $text;
}

## automatic smart quotes
if ($MarkupExtensions['squo']) {
    Markup('nl>','<<nl',"/\s?\n\s*([^<]+?>)/",' $1');
    Markup('<nl','<squo',"/(<[^>]+?)\s*\n\s?/",'$1 ');
    Markup('emquo','<squo',"/(&[mn]dash;)(['\"]+)([[:alpha:]])/",
    "emquoHandler");
    Markup('squo','>style',"/(<.*?>['\"]*(?:`')?\s?)|(.?['\"]+(?:`')?\s?)/",
    "squoHandler");
    Markup('sq|','>inline',"/(\\[\\[[^|\\]]+\\|)(.*?)(\\]\\])/",
    "squolinkHandler");
    Markup('sq->','>inline',"/(\\[\\[)([^\\]]+?)(-+&gt;.*?\\]\\])/",
    "squolinkHandler");
    SDV($SmartQuoteStyle, 'GB');
    $SmartQuoteChars = array(
        'CH' => array('o' => array('l' => 'la', 'r' => 'ra'), 'i' => array('l' =>'lsa', 'r' => 'rsa')),
        'DE' => array('o' => array('l' => 'bd', 'r' => 'ld'), 'i' => array('l' =>'sb', 'r' => 'ls')),
        'DK' => array('o' => array('l' => 'ra', 'r' => 'la'), 'i' => array('l' =>'rsa', 'r' => 'lsa')),
        'ES' => array('o' => array('l' => 'la', 'r' => 'ra'), 'i' => array('l' =>'ld', 'r' => 'rd')),
        'FI' => array('o' => array('l' => 'rd', 'r' => 'rd'), 'i' => array('l' =>'rs', 'r' => 'rs')),
        'FR' => array('o' => array('l' => 'la', 'r' => 'ra'), 'i' => array('l' =>'ld', 'r' => 'rd')),
        'GB' => array('o' => array('l' => 'ld', 'r' => 'rd'), 'i' => array('l' =>'ls', 'r' => 'rs')),
        'NL' => array('o' => array('l' => 'bd', 'r' => 'rd'), 'i' => array('l' =>'sb', 'r' => 'rs')),
        'PL' => array('o' => array('l' => 'bd', 'r' => 'rd'), 'i' => array('l' =>'la', 'r' => 'ra')),
        'SE' => array('o' => array('l' => 'ra', 'r' => 'ra'), 'i' => array('l' =>'ls', 'r' => 'rs')),
                            );
}

function emquoHandler($m) {
  return $m[1].ltrim(SmartenQuotes(' '.$m[2])).$m[3];
}
function squoHandler($m) {
  return BypassHTML($m[1],$m[2]);
}
function squolinkHandler($m) {
  return $m[1].SmartenLinkText($m[2]).$m[3];
}

function SmartenLinkText($txt) {
  global $LinkPattern,$UrlExcludeChars,$ImgExtPattern;
  if (!preg_match("/($LinkPattern)([^$UrlExcludeChars]+$ImgExtPattern)/",$txt)) 
        $txt = preg_replace_callback(
                   "/(<.*?>['\"]*\s?)|(.?['\"]+\s?)/",
                   function ($m) { return BypassHTML($m[1],$m[2]); },
                   $txt);
  return $txt;
}

function BypassHTML($hstring,$qstring) {
  if ($qstring=='') {
     $qstring = preg_replace("/.*>/",'',$hstring);
     $hstr = preg_replace("/>.*/",'>',$hstring);
     if (trim($qstring)=='') $r = $qstring;
     else { 
         if (strstr($hstr,"</") || strstr($hstr,"/>")) $qstring = ">" . $qstring;
         $r = SmartenQuotes($qstring);
     }
     return $hstr.$r;
  } else
     return SmartenQuotes($qstring);
}

function SmartenQuotes($chars) {
  global $SmartQuoteStyle, $SmartQuoteChars;
  $s = 0;  $r = '';
  $sty = $SmartQuoteStyle;
  if ($chars[0] =="'" || $chars[0] == '"') {
      $quotes = $chars;
      $char = '';
  } else {
      $quotes = substr($chars,1);
      $char = $chars[0];
      if ($char=='`') $sty = 'GB';
      elseif (strlen($quotes)>1 && $quotes[0]==$quotes[1] && !strstr(" =-[(",$char)) {
         $p = ($quotes[0]=="'") ? "p" : "P";
         $r = "$char&$p" . "rime;";
         $s = 2;
         $char = "`";
      }
  }
  $hands = array('l','r');
  $h = ($char=="" || strstr(" =-[(",$char)) ? 0 : 1;
  if ($char=="`" || $char==">") $char = "";
  $r .= $char;
  $x = '';
  if (preg_match("/\s$/",$quotes,$m)) {
     $quotes = rtrim($quotes); $x = ' ';
  } elseif ($h && $quotes=="'") $sty = 'GB';
  $preq = 'x';
  for ($i=$s;$i<strlen($quotes);$i++) {
      if ($quotes[$i]=='`') { $h = 1; continue; }
      if ($i && $sty!='FR') $r .= '&thinsp;';
      $q = ($quotes[$i]=="'") ? 'i' : 'o';
      if ($q==$preq) $h = 1 - $h;
      $r .= '&'.$SmartQuoteChars[$sty][$q][$hands[$h]].'quo;';
      $sty = $SmartQuoteStyle;
      $preq = $q;
  }
  if ($sty=='FR') $r = preg_replace('/&laquo;/','$0&nbsp;',preg_replace('/&raquo;/','&nbsp;$0',$r));
  return $r.$x;
}

## page self-reference format and tool-tip format
  $LinkCleanerEnabled = true;
  function isClosure($r) {
    return (is_object($r) && ($r instanceof Closure));
  }
  function cleanLinkText($pagename, $text) {
    global $LinkCleaner;
    foreach ($LinkCleaner as $p => $r) {
      $text = isClosure($r) ? preg_replace_callback($p,$r,$text)
                            : preg_replace($p,$r,$text);
    }
    return $text;
  }

  SDVA($LinkCleaner, array(
    '/`\..*?$/' => '...',
    "/\\{(\\$.*?)\\}/" => '$1',
    "/\\[\\[([^|\\]]+)\\|\\s*(.*?)\\]\\]($SuffixPattern)/" =>
      function ($m) use (&$pagename) { return MakeLink($pagename,$m[1],$m[2],$m[3],'$LinkText'); },
    "/\\[\\[([^\\]]+?)\\s*-+&gt;\\s*(.*?)\\]\\]($SuffixPattern)/" =>
      function ($m) use (&$pagename) { return MakeLink($pagename,$m[2],$m[1],$m[3],'$LinkText'); },
    '/\\[\\[#([A-Za-z][-.:\\w]*)\\]\\]/' => "",
    "/\\[\\[(.*?)\\]\\]($SuffixPattern)/" =>
      function ($m) use (&$pagename) { return MakeLink($pagename,$m[1],NULL,$m[2],'$LinkText'); },
    '/[\\[\\{](.*?)\\|(.*?)[\\]\\}]/' => '$1',
    "/`(($GroupPattern([\\/.]))?($WikiWordPattern))/" => '$1',
    "/$GroupPattern\\/($WikiWordPattern)/" => '$1'
            ));

if ($MarkupExtensions['links']) {
    SDV($WikiStylePattern,'%%|%[A-Za-z][-,=:#\\w\\s\'"().]*%');
    $oLinkPageFunction = $LinkFunctions['<:page>'];
    $LinkFunctions['<:page>'] = 'LinkPageTitle';
    if ($action=='browse') {
        $LinkPageSelfFmt = "<b class='selflink'>\$LinkText</b>";
        $HTMLStylesFmt['selfref'] = "li.browse b { font-weight: normal; }";
    } elseif ($action=='edit') 
        $HTMLStylesFmt['selfref'] = "li.edit a { border-bottom: 1px dotted; }";
    elseif ($action=='diff')
        $HTMLStylesFmt['selfref'] = "li.diff a { border-bottom: 1px dotted; }";
    elseif ($action=='upload')
        $HTMLStylesFmt['selfref'] = "li.upload a { border-bottom: 1px dotted; }";
    elseif ($action=='searchinsitu')
        $HTMLStylesFmt['selfref'] = "form a.search { border-bottom: 1px dotted; }";
    $LinkPageExistsTitleFmt = 
    "<a class='wikilink' href='\$LinkUrl' title=\"\$ToolTip\">\$LinkText</a>";
    $LinkPageCreateFmt = 
    "<a class='createlinktext' href='\$PageUrl?action=edit' title='Create page'>\$LinkText</a><a 
  class='createlink' href='\$PageUrl?action=edit'>?</a>";
    $MakePageNameFunction = 'LocalMakePageName';
}

function LocalMakePageName($basepage, $str) {
    global $MakePageNameFunction;
    $MakePageNameFunction = '';
    $n = MakePageName($basepage, preg_replace('/".*?"/', '', $str));
    $MakePageNameFunction = 'LocalMakePageName';
    return $n;
}

function LinkPageTitle($pagename,$imap,$path,$title,$txt,$fmt=NULL) {
    global  $oLinkPageFunction,$LinkPageExistsTitleFmt,$UrlExcludeChars;
    if ($fmt!='') 
        return $oLinkPageFunction($pagename,$imap,$path,$title,$txt,$fmt);
    if (preg_match("/^([^#?]+)(?:#([^\\s$UrlExcludeChars]*))?$/",$path,$match)) {
        $tgtname = MakePageName($pagename,$match[1]); $anch=@$match[2];
        if ($title) $txt = str_replace('"'.$title.'"','',$txt);
        if (PageExists($tgtname) && $tgtname!=$pagename) {
            if (!$title) $title = TitleParagraph($tgtname,$anch);
            if ($title) 
                $fmt = str_replace('$ToolTip',$title,$LinkPageExistsTitleFmt);
        }
    }
    return $oLinkPageFunction($pagename,$imap,$path,$title,$txt,$fmt);
}

function TitleParagraph($pagename,$anch) {
    global $WikiStylePattern, $ParaBadAnchorFmt;
    $refpage = ReadPage($pagename); $para = '';
    $title = ($anch=='') ? 
        preg_match("/^(?:!+|:.*?:)\\s*(?:\\[\\[#.*?\\]\\])?([^\\n]+)/",
                $refpage['text'],$match) :
        preg_match("/\\[\\[#+$anch\\]\\]\\n?([^\\n]+)/",
                $refpage['text'],$match);
    if ($title) {
        $para = preg_replace("/!.*?$/",'',$match[1]);
        $para = preg_replace("/(''+|@@)(.*?)\\1/",'$2',$para);
        $para = preg_replace("/'([-_^;+\\/])(.*?)\\1'/",'$2',$para);
        $para = preg_replace("/\\[([@=]|[-+]+)(.*?)\\1\\]/",'$2',$para);
        $para = preg_replace("/$WikiStylePattern/",'',$para);
        $para = cleanLinkText($pagename, $para);
        if (strlen($para)>60) $para = substr($para,0,60) . '...';
        $para = str_replace(array('"','...','`'),array('&quot;','&hellip;',''),
            htmlentities($para));
    } elseif ($anch!='') 
        $para = $anch;
    return $para;
}

## rowspan in simple tables
Markup('||++', '>^||', '/(<t[dh][^>]*>.*?)(\+\++)/',
  "CellRowspanHandler");
function CellRowspanHandler($m) {
  $string = $m[1];  $r = strlen($m[2]);
  return preg_replace('/^(.*<\/t[dh]>)?(<t[dh][^>]*)(>.*?)$/',
      '$1$2 rowspan="'.$r.'"$3',$string);
}
## rowspan filler
Markup('||^^', '<^||||', '/\|\|\^\^+(?=\|\|)/','');
## colspan filler
Markup('||__', '<^||||', '/\|\|__+(?=\|\|)/','||');

?>

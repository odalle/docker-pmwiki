<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004 Patrick R. Michaud (pmichaud@pobox.com)
    This file is distributed under the terms of the GNU General Public
    License as published by the Free Software Foundation; either
    version 2 of the License, or (at your option) any later version.

    This module enables embedding of Scaleable Vector Grafics (.svg) files into
    wiki pages.
    The width= and height= Parameter needed to be set to define the painting area.
    The adobve svg-plugin is needed


    To use this module, simply place this file in the cookbook/ directory
    and add the following line into config.php:

        include_once('cookbook/svg.php');


   Script maintained by Petko YOTOV www.pmwiki.org/petko

*/

$RecipeInfo['Svg']['Version'] = '20161021';

# Disable SVG handling by the core (since PmWiki 2.2.85)
$ImgExtPattern="\\.(?:gif|jpg|jpeg|png|GIF|JPG|JPEG|PNG)";

SDV($SvgTagFmt,
   "<embed type='image/svg+xml' src='\$LinkUrl'/>");

Markup_e('svg', '<urllink',
  "/\\b(?>(\\L))([^\\s$UrlExcludeChars]+\\.svgz?)/",
  "Keep(\$GLOBALS['LinkFunctions'][ \$m[1] ](\$pagename,\$m[1],\$m[2],NULL,\$m[1].\$m[2],
    \$GLOBALS['SvgTagFmt']), 'L')");


SDVA($WikiStyleAttr,array(
  'height' => 'img|object|embed',
  'width' => 'img|object|embed'));



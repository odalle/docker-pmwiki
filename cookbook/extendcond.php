<?php if (!defined('PmWiki')) exit();
/*
 * ConditionalExtensions - A Conditional Markup extension for PmWiki 2.0
 * Copyright 2005-2015 by D.Faure (dfaure@cpan.org)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Thanks to M.Fick for pointing out a security issue and to P.Michaud for
 * fixing it, string comparisons and earlier optimization hints.
 *
 * See http://www.pmwiki.org/wiki/Cookbook/ConditionalExtensions for info.
 */
$RecipeInfo['ConditionalExtensions']['Version'] = '20150217';

$Conditions['matchgroup'] = "MatchCondition(\$pagename, \$condparm, '$1$2.*')";
$Conditions['matchname']  = "MatchCondition(\$pagename, \$condparm, '$1*.$2')";

function MatchCondition($pagename, $condparm, $pat) {
  $args = ParseArgs($condparm);
  $args = $args[''];
  if(count($args) < 2) $args[] = $pagename;
  return MatchPageNames($args[1], FixGlob($args[0]), $pat);
}

$Conditions['eq'] = $Conditions['eqi'] = 'ArithCondition($condname, $condparm, "==")';
$Conditions['le'] = $Conditions['lei'] = 'ArithCondition($condname, $condparm, "<=")';
$Conditions['lt'] = $Conditions['lti'] = 'ArithCondition($condname, $condparm, "<")';
$Conditions['ge'] = $Conditions['gei'] = 'ArithCondition($condname, $condparm, ">=")';
$Conditions['gt'] = $Conditions['gti'] = 'ArithCondition($condname, $condparm, ">")';
if(!$Conditions['equal']) $Conditions['equal'] = $Conditions['equali'] = $Conditions['eq'];

function ArithCondition($condname, $condparm, $op) {
  $args = ParseArgs($condparm);
  $args = $args[''];
  $cs = (substr($condname, -1, 1) == 'i') ? 'strtolower' : '';
  return @eval("return (boolean)($cs('$args[0]') $op $cs('$args[1]'));");
}

$Conditions['regmatch'] = 'RegMatchArgs($condparm)';

function RegMatchArgs($args) {
  $args = ParseArgs($args);
  return preg_match(@$args[''][0], @$args[''][1]);
}

#!/usr/bin/php -q
<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2004 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Jakub Vr�na <vrana@php.net>                              |
  +----------------------------------------------------------------------+
*/

if ($_SERVER["argc"] < 3) {
	exit("Purpose: Syntax highlight PHP examples in DSSSL generated HTML manual.\n"
		.'Usage: html_syntax.php [ "html" | "php" ] [ filename.ext | dir | wildcard ] ...' ."\n"
		.'"html" - highlight_string() is applied, "php" - highlight_php() is added' ."\n"
	);
}
set_time_limit(5*60); // can run long, but not more than 5 minutes

function callback_html_number_entities_decode($matches) {
	return chr($matches[1]);
}

function callback_highlight_php($matches) {
	$with_tags = preg_replace_callback("!&#([0-9]+);!", "callback_html_number_entities_decode", trim($matches[1]));
	if ($GLOBALS["TYPE"] == "php") {
		return "\n<?php\nhighlight_php('". addcslashes($with_tags, "'\\") ."');\n?>\n";
	} else { // "html"
		return highlight_string($with_tags, true);
	}
}

function callback_highlight_xml($matches) {
        $source = htmlentities($matches[1]);

        $match = array(
            '/(\w+)=(&quot;|"|\')(.*?)(&quot;|"|\')/',
            '/!DOCTYPE (\w+) (\w+) (&quot;|\'|")(.*?)(&quot;|\'|")/',
            '/&lt;([a-zA-Z_][a-zA-Z0-9_:-]*)/',
            '/&lt;\/([a-zA-Z_][a-zA-Z0-9_:-]*)&gt;/',
            '/&lt;!--/',
            '/--&gt;/',
            '/&lt;\?xml (.*?) ?\?&gt;/i',
            '/&lt;!\[CDATA\[(.*)\]\]&gt;/i',
        );

        $replace = array(
            '<span class="attributes">$1</span>=<span class="string">$2$3$2</span>',
            '<span class="tags">!DOCTYPE</span> <span class="attributes">$1 $2 $3$4$3</span>',
            '&lt;<span class="tags">$1</span>',
            '&lt;/<span class="tags">$1</span>&gt;',
            '<span class="comment"><!--',
            '--&gt;</span>',
            '&lt;<span class="tags">?xml</span> $1 <span class="tags">?</span>&gt;',
            '<span class="tags">&lt;![<span class="keyword">CDATA</span>[</span><span class="cdata">$1</span><span class="tags">]]&gt;</span>'
        );

	 return '<div class="xmlcode">' . nl2br(preg_replace($match, $replace, $source)) . '</div>';
}

$files = $_SERVER["argv"];
array_shift($files); // $argv[0] - script filename
$TYPE = array_shift($files); // "html" or "php"
while (($file = array_shift($files)) !== null) {
	if (is_file($file)) {
		$process = array($file);
	} elseif (is_dir($file)) {
		$lastchar = substr($file, -1);
		$process = glob($file . ($lastchar == "/" || $lastchar == "\\" ? "*" : "/*"));
	} else { // should be wildcard
		$process = glob($file);
	}
	foreach ($process as $filename) {
		if (!is_file($filename)) { // do not recurse
			continue;
		}
		//~ echo "$filename\n";
		$original = file_get_contents($filename);
		$highlighted = preg_replace_callback("!<PRE\r?\nCLASS=\"php\"\r?\n>(.*)</PRE\r?\n>!sU", "callback_highlight_php", $original);
		$highlighted = preg_replace_callback("@<HIGHLIGHTME>(.*)</HIGHLIGHTME>@sU", "callback_highlight_php", $highlighted); /* XSL build */
		if ($original != $highlighted) {
			// file_put_contents is only in PHP >= 5
			$fp = fopen($filename, "wb");
			fwrite($fp, $highlighted);
			fclose($fp);
		}
	}
}
?>

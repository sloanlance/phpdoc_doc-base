#!/usr/local/bin/php -q
<?php

/*
 * Script to convert (most of the) php documentation from docbook to unix man format.
 * Roel Vanhout - roel@2e-systems.com - 20010413
 * No long license statements here - do whatever you want.
 */

/*
 * Problems:
 * - examples are not shown correctly
 */

$lang = 'en';


$file = `cat \`find $lang | grep .xml\``;
#$file = str_replace("\n", '', $file);

// First get everything in <refentry></refentry> tags
preg_match_all('/<refentry.*?<\/refentry>/s', $file, $refentries);

$functions = array();
$i = 0;

foreach($refentries[0] as $refentry) {
    preg_match('/<refname>(.*)<\/refname>/s', $refentry, $matches);
    if(!empty($matches[1])) {
        $functions[$i]['name'] = $matches[1];
    } else {
        $functions[$i]['name'] = '';
    }

    preg_match('/<refpurpose>(.*)<\/refpurpose>/s', $refentry, $matches);
    if(!empty($matches[1])) {
        $functions[$i]['shortdesc'] = $matches[1];
    } else {
        $functions[$i]['shortdesc'] = '';
    }

    preg_match('/<funcprototype>(.*)<\/funcprototype>/s', $refentry, $matches);
    if(!empty($matches[1])) {
        $funcprototype = $matches[1];
    } else {
        $funcprototype = '';
    }

    preg_match('/<funcdef>(.*)<\/funcdef>/s', $funcprototype, $matches);
    if(!empty($matches[1])) {
        $functions[$i]['prototype'] = $matches[1];
        $functions[$i]['prototype'] = preg_replace('/<.*?>/s', '', $functions[$i]['prototype']);
        $functions[$i]['prototype'] .= '(';
    } else {
        $functions[$i]['prototype'] = '';
    }

    preg_match_all('/<paramdef>.*?<\/paramdef>/s', $funcprototype, $matches);
    $first = 1;
    foreach($matches[0] as $param) {
        if(preg_match('/<optional>.*<\/optional>/s', $param)) {
            if($first != 1 ) {
                $functions[$i]['prototype'] .= ' [, ' . trim(preg_replace('/<.*?>/s', '', $param)) . ']';
            } else {
                $functions[$i]['prototype'] .= ' [' . trim(preg_replace('/<.*?>/s', '', $param)) . ']';
                $first = 0;
            }
        } else {
            if($first != 1 ) {
                $functions[$i]['prototype'] .= ', ';
            } else {
                $first = 0;
            }
            $functions[$i]['prototype'] .= trim(preg_replace('/<.*?>/s', '', $param));
        }
    }
    $functions[$i]['prototype'] = preg_replace('/\n/', '', $functions[$i]['prototype']);
    $functions[$i]['prototype'] = preg_replace('/\s{2,}/s', ' ', $functions[$i]['prototype']);
    $functions[$i]['prototype'] .= ')';

    $y = 0;
    preg_match_all('/<para>.*?<\/para>/s', $refentry, $matches);
    foreach($matches[0] as $paragraph) {
        if(preg_match('/<example>/s', $paragraph)) {
            // If this paragraph has an example, do some special formatting.
            preg_match('/<title>(.*)<\/title>/s', $paragraph, $tmp);
            $functions[$i]['example'] = $tmp[1];
            $functions[$i]['example'] = preg_replace('/\s{2,}/', ' ', $functions[$i]['example']);
            $functions[$i]['example'] = preg_replace('/<.*?>/', '', $functions[$i]['example']);
            $functions[$i]['example'] .= "\n\n";
            preg_match('/<programlisting.*?>(.*)<\/programlisting>/s', $paragraph, $tmp);
            $programlisting = $tmp[1];
            // Hmm, no function for this?
            $programlisting = str_replace('&lt;', '<', $programlisting);
            $programlisting = str_replace('&gt;', '>', $programlisting);
            $programlisting = str_replace('&quot;', '"', $programlisting);
            $programlisting = str_replace('&amp;', '&', $programlisting);
            $programlisting = str_replace('&nbsp;', ' ', $programlisting);
            $programlisting = str_replace('&sp;', ' ', $programlisting);
            $programlisting = str_replace('&amp;', '&', $programlisting);
            #$functions[$i]['example'] .= `echo '$programlisting' | indent -kr` . "\n\n";
            $functions[$i]['example'] .= $programlisting;
        } elseif(preg_match('/See also/s', $paragraph))  {
            $functions[$i]['seealso'] = preg_replace('/<.*?>/', '', $paragraph);
            $functions[$i]['seealso'] = preg_replace('/See also:/', '', $functions[$i]['seealso']);
            $functions[$i]['seealso'] = preg_replace('/\s{2,}/', ' ', $functions[$i]['seealso']);
        } else {
            // Nothing special, just put it in.
            $functions[$i]['paragraph'][$y] = preg_replace('/<.*?>/', '', $paragraph);
            $functions[$i]['paragraph'][$y] = preg_replace('/\s{2,}/', ' ', $functions[$i]['paragraph'][$y]);
        }
        $y++;
    }

    $i++;
}

/*
 * We have an array now with all the data, now write it to seperate files.
 */

if(!file_exists('man7')) {
    umask(0000);
    mkdir('man7', 0755);
}

foreach($functions as $function) {
    if(function_exists('gzwrite')) {
        $fp = fopen('man7/php_' . $function['name'] . '.man.gz',  'w');
    } else {
        $fp = fopen('man7/php_' . $function['name'] . '.man',  'w');
    }
    /*
    $function['name']
    $function['shortdesc']
    $function['prototype']
    $function['paragraph'][$y] // Array
    $function['seealso']
    $function['example']
    */

    $page = '.TH ' . $function['name'] . " 7  \"" . date("j F, Y") . "\" \"PHPDOC MANPAGE\" \"PHP Programmer's Manual\"\n.SH NAME\n" . 
            $function['name'] . "\n.SH SYNOPSIS\n.B " . $function['prototype'] . "\n.SH DESCRIPTION\n" . $function['shortdesc'] . ".\n";
    if(!empty($function['paragraph']) && count($function['paragraph']) > 0) {
        foreach($function['paragraph'] as $para) {
            $page .= ".PP\n";
            $page .= trim($para) . "\n";
        }
    }

    if(!empty($function['example'])) {
        $page .= ".SH \"EXAMPLE\"\n";
        $page .= $function['example'] . "\n";
    }

    if(!empty($function['seealso'])) {
        $page .= ".SH \"SEE ALSO\"\n";
        $page .= $function['seealso'] . "\n";
    }

    if(function_exists('gzwrite')) {
        gzwrite($fp, $page);
    } else {
        fwrite($fp, $page);
    }
}


?>

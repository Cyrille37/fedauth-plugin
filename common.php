<?php
/**
 * Federated Login for DokuWiki - common utility functions
 *
 * Enables your DokuWiki to provide users with
 * Hybrid OAuth + OpenId federated login.
 *
 * @copyright  2012 Aoi Karasu
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

if (!defined('FEDAUTH_PLUGIN')) die();

/**
 * Implodes an array with the key and value pair giving
 * a glue, a separator between pairs and flattens the nested
 * array using square braces, eg. topkey[nested1][nested2].
 *
 * Params b1 and b2 are key wprappers for recursive array scan.
 * Do not use them, unless you want to wrap top level keys.
 *
 * @param array $array the array to implode
 * @param string $glue glue between key and value
 * @param string $separator separator between pairs
 * @return string the imploded array
 *
 * @author Aoi Karasu <aoikarasu@gmail.com>
 * @author Original by lightningspirit <lightningspirit@gmail.com>
 */
function array_implode($array, $glue='=', $separator='&', $b1='',$b2='') {
    if (!is_array($array)) return $array;
    $string = array();
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            $val = array_implode($val, $glue, $separator.$b1.$key.$b2, '[', ']');
            $string[] = $b1.$key.$b2.$val;
        } else
        $string[] = $b1.$key.$b2.$glue.$val;
    }
    return implode($separator, $string);
}

/**
 * Renders XHTML script block with contents loaded from a plugin-local
 * JavaScript file. The file location is supposed to be 'js/$name.js'.
 * All comments (including the docblock) are removed by default.
 *
 * @param string $name the name of the JavaScript file without path nor extension
 * @return string XHTML scrpit block or empty string, if file not found
 */
function plugin_script_block($name, $nocomments=true) {
    $jsfile = FEDAUTH_PLUGIN . 'js/' . $name . '.js';
    if (!file_exists($jsfile)) return '';

    $text = @file_get_contents($jsfile);
    // remove all comments from the file
    if ($nocomments) {
        $text = preg_replace('!/\*.*?\*/!s', '', $text);
        $text = preg_replace('/\n\s*\n/', "\n", $text);
    }
    $out = '<script type="text/javascript" charset="utf-8"><!--//--><![CDATA[//><!--' . "\n"
         . $text . "\n" . '//--><!]]></script>' . "\n";
    return $out;
}

/**
 * Loads a handler class.
 *
 * @param object $plugin owner of the handler instance
 * @param string $cmd command to handle
 * @param string $type scope suffix beeing a part of class' filename
 * @param string $provid (optional) an authorization provider id
 */
function &load_handler_class($plugin, $cmd, $type, $provid='', $base='base') {
    $class = "fa_" . $cmd;

    // don't want require_once end up dying, load base class instead
    $hfile = FEDAUTH_PLUGIN . "classes/$type/$class.$type.class.php";
    if (file_exists($hfile)) {
        require_once($hfile);
    }
    if (!class_exists($class)) {
        $class = "fa_" . $base;
        if (!class_exists($class)) {
            throw new RuntimeException("Base class $class must be included before load_handler_class() is called.");
        }
    }

    return new $class($plugin, $cmd, $provid);
}

function request_url() {
    $s = empty($_SERVER['HTTPS']) ? '' : ($_SERVER['HTTPS'] == 'on') ? 's' : '';
    $protocol = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos(strtolower($_SERVER['SERVER_PROTOCOL']), '/')) . $s;
    $defp = empty($s) ? '80' : '443';
    $port = ($_SERVER['SERVER_PORT'] == $defp) ? '' : (':'.$_SERVER['SERVER_PORT']);
    return $protocol . '://' . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

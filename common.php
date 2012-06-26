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

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

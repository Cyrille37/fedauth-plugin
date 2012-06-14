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

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
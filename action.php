<?php
/**
 * Federated Login for DokuWiki - helper class
 *
 * Enables your DokuWiki to provide users with
 * Hybrid OAuth + OpenId federated login.
 *
 * @copyright  2012 Aoi Karasu
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_fedauth extends DokuWiki_Action_Plugin {

    /**
     * Returns the plugin meta information.
     */
    function getInfo() {
        return array(
            'author' => 'Aoi Karasu',
            'email'  => 'aoikarasu@gmail.com',
            'date'   => '2012-06-09',
            'name'   => 'Federated Login Plugin',
            'desc'   => 'Functions to handle user identity related DokuWiki actions',
            'url'    => 'http://www.dokuwiki.org/plugin:fedauth',
        );
    }

    /**
     * Registers the event handlers.
     */
    function register(&$controller)
    {
        $controller->register_hook(
            'HTML_LOGINFORM_OUTPUT',
            'BEFORE',
            $this,
            'handle_login_form',
            array());
        $controller->register_hook(
            'HTML_UPDATEPROFILEFORM_OUTPUT',
            'AFTER',
            $this,
            'handle_profile_form',
            array());
        $controller->register_hook(
            'ACTION_ACT_PREPROCESS',
            'BEFORE',
            $this,
            'handle_act_preprocess',
            array());
        $controller->register_hook(
            'TPL_ACT_UNKNOWN',
            'BEFORE',
            $this,
            'handle_act_unknown',
            array());
        // TODO: send password handler for 'fedauthonly' interception
    }

    /**
     * Handles login action preprocess to output the login form for federated login only.
     */
    function handle_act_preprocess(&$event, $param) {
        if ($event->data == 'login') {
            // output custom login form without password textbox
        }
    }

    /**
     * Handles unknown action preprocess.
     */
    function handle_act_unknown(&$event, $param) {
    }

    /**
     * Handles the login form rendering.
     */
    function handle_login_form(&$event, $param) {
    }

    /**
     * Handles the profile form rendering.
     */
    function handle_profile_form(&$event, $param) {
    }

} /* action_plugin_federate */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

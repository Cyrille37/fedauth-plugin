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
if (!defined('FEDAUTH_PLUGIN')) define('FEDAUTH_PLUGIN', DOKU_PLUGIN . 'fedauth/');

require_once(DOKU_PLUGIN.'action.php');

class action_plugin_fedauth extends DokuWiki_Action_Plugin {

    var $provid = '';
    var $cmd = '';
    var $handler = null;

    var $providers = null;

    var $functions = array('select','signin','signedin','remove'); // require a provider id
    var $commands = array('add','login','manage'); // don't require a provider id

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
            'AFTER',
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
        if ($event->data != 'login' && $event->data != 'profile' && $event->data != 'fedauth') {
            return;
        }

        require_once(FEDAUTH_PLUGIN . 'common.php');
        require_once(FEDAUTH_PLUGIN . "classes/fa_base.class.php");
        require_once(FEDAUTH_PLUGIN . "classes/fa_service.class.php");
        require_once(FEDAUTH_PLUGIN . "classes/usr/fa_login.usr.class.php");

        $user = $_SERVER['REMOTE_USER'];

        // load helper plugin
        if ($helper =& plugin_load('helper', 'fedauth')) {
            $this->providers = $helper->getProviders();
        }

        $fa = $_REQUEST['fa'];
        if (is_array($fa)) {
            $this->cmd = key($fa);
            $this->provid = is_array($fa[$this->cmd]) ? key($fa[$this->cmd]) : null;
        } else {
            $this->cmd = $fa;
            $this->provid = null;
        }

        // verify $_REQUEST vars
        if (in_array($this->cmd, $this->commands)) {
            $this->provid = '';
        } else if (!in_array($this->cmd, $this->functions) || !$this->providers->get($this->provid)) {
            $this->cmd = empty($user) ? 'login' : 'manage';
            $this->provid = '';
        } else if ($this->cmd == 'select') {
            $this->cmd = 'login';
        }

        if ($event->data == 'fedauth') {
            $event->stopPropagation();
            $event->preventDefault();

            if (($this->cmd != 'login' || $this->provid != '') && !checkSecurityToken()) {
                $this->cmd = 'login';
                $this->provid = '';
            }
        }

        $this->handler =& load_handler_class($this, $this->cmd, 'usr', $this->provid, 'login');
        $result = $this->handler->process();
        if (is_array($result) && empty($_REQUEST['ajax'])) {
            msg($result['msg'], $result['code']);
        }
    }

    /**
     * Handles unknown action preprocess.
     */
    function handle_act_unknown(&$event, $param) {
        // mandatory check since other plugins' actions trigger this as well
        if ($event->data != 'fedauth') {
             return;
        }

        // enable direct access to language strings
        $this->setupLocale();

        $event->stopPropagation();
        $event->preventDefault();

        $this->handler->html();
    }

    /**
     * Handles the login form rendering.
     */
    function handle_login_form(&$event, $param) {
        $this->setupLocale();
        $this->handler->html();
    }

    /**
     * Handles the profile form rendering.
     */
    function handle_profile_form(&$event, $param) {
        $this->setupLocale();
        $this->handler->html();
    }

} /* action_plugin_federate */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

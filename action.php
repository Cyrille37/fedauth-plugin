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

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if (!defined('FEDAUTH_PLUGIN')) define('FEDAUTH_PLUGIN', DOKU_PLUGIN . 'fedauth/');

require_once(DOKU_PLUGIN.'action.php');

global $conf;

// define cookie and session id, append server port when securecookie is configured
if (!defined('FEDAUTH_COOKIE')) define('FEDAUTH_COOKIE', 'DWFA'.md5(DOKU_REL.(($conf['securecookie'])?$_SERVER['SERVER_PORT']:'')));

if (!defined('USER_CMD_SCOPE')) define('USER_CMD_SCOPE', 'usr');

class action_plugin_fedauth extends DokuWiki_Action_Plugin {

    var $provid = '';
    var $cmd = '';
    var $handler = null;

    var $cookie = null;

    var $providers = null;

    var $functions = array('select','signin','signedin','remove'); // require a provider id
    var $commands = array('add','login','logout','manage','register'); // don't require a provider id

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
        $controller->register_hook('HTML_LOGINFORM_OUTPUT',         'AFTER',  $this, 'handle_login_form',     array());
        $controller->register_hook('HTML_UPDATEPROFILEFORM_OUTPUT', 'AFTER',  $this, 'handle_profile_form',   array());
        $controller->register_hook('ACTION_ACT_PREPROCESS',         'BEFORE', $this, 'handle_act_preprocess', array());
        $controller->register_hook('TPL_ACT_UNKNOWN',               'BEFORE', $this, 'handle_act_unknown',    array());
        $controller->register_hook('AUTH_LOGIN_CHECK',              'BEFORE', $this, 'handle_login_check',    array());
    }

    /**
     * Validates federated login credentials.
     *
     * This method is crucial to keep the user logged in, when authorized via fedauth.
     * If the fedauth cookie is found, it is validated against the session data
     * and timeout value. When valid, user stays logged in. Otherwise a request
     * is issued to the authorization service stored in the cookie, to authorize
     * the user again. The default authorization check via auth_login() is suppressed
     * and $_SESSION[DOKU_COOKIE]['auth'] is never set - there is no need for it.
     *
     * If the cookie is not found, the default authorization is not suppressed and
     * will proceed as usual (unless other plugins influence it).
     *
     * IMPORTANT:
     * There is an uresolved problem with AJAX calls, however. For typical GET and POST
     * calls the timestamp of last successful authorization is validated against the
     * $conf['auth_security_timeout'] variable. On timeout an authorization request
     * is reissued to the authorization service. This involves HTTP redirections that
     * don't work with AJAX calls that eventually hang.
     *
     * For the time beeing the timeout check is skipped for AJAX calls and the user
     * stays logged in as long as other fedauth cookie data is valid.
     *
     * Dokuwiki built-in AJAX handler 'lib/exe/ajax.php' expects $_GET['call'] or
     * $_POST['call'] to be set. Thus the action_plugin_fedauth::isAjaxCall() use
     * this condition to recognize an AJAX call. Custom AJAX handlers should be
     * implemented the same way as the built-in one, or at least implment setting
     * of a dummy value before any invocations of Dokuwiki functions are made, eg.
     * $_GET['call'] = 'dummy';
     *
     * @param object $event event data
     * @param array $param additional parameters
     */
    function handle_login_check(&$event, $param) {
        global $conf, $USERINFO;

        // standard dokuwiki auhtorization in progress, escape
        if (!empty($event->data['user'])) return;

        require_once(FEDAUTH_PLUGIN . "classes/fa_cookie.class.php");
        $this->cookie = new fa_cookie();

        // fedauth signed-in complete or logout in progress; requires $this->cookie to be set,
        // however an escape is a must to prevent unwanted behavior (auth loop, blocked logout)
        if ((!empty($_REQUEST['fa']['signedin']) && ($_REQUEST['mode'] != 'add')) ||
            ($_REQUEST['do'] == 'logout')) return;

        if ($cdata = $this->cookie->get()) {
//        msg( "<pre>".print_r($cdata, true)."</pre>");
            $user = $cdata['user'];
            $session = $_SESSION[DOKU_COOKIE]['fedauth'];
            // remove temp data, if any
            if (isset($_SESSION[DOKU_COOKIE]['fedauth']['tmpr'])) {
                unset($_SESSION[DOKU_COOKIE]['fedauth']['tmpr']);
            }
//        msg( "<pre>".print_r($session, true)."</pre>");
            // refer to Dokuwiki's 'inc/auth/basic.class.php' for detailed information
            // on useSessionCache() method and the purpose of @filemtime() condition
            if (isset($session) &&
                ($session['time'] >= @filemtime($conf['cachedir'] . '/sessionpurge')) &&
                (($session['time'] >= time() - $conf['auth_security_timeout']) || $this->isLibExe() || $this->isAjaxCall()) &&
                ($session['user'] == $user) &&
                ($session['prid'] == $cdata['prid']) &&
                ($session['stok'] == $cdata['stok']) &&
                ($session['buid'] == auth_browseruid())
            ) {
                // cookie and session ok - keep user logged-in
//            msg( "<pre>".print_r($session['rq'], true)."</pre>");
                $_SERVER['REMOTE_USER'] = $user;
                $USERINFO = $session['info'];
                $event->preventDefault();
                if (isset($session['sgin'])) {
                   // redirected from authorization service, display welcome message
                   msg('login successful');
                   unset($_SESSION[DOKU_COOKIE]['fedauth']['sgin']);
                }
                if ($session['stor']) {
                   // restore request values from saved before authorization interception
                   $this->_restoreRequestData();
                }
                return;
            }
//print('<pre>'.print_r($GLOBALS, true).'</pre>'); return;
            // perform fedauth auhtorization based on cookie data
            $this->_ensure_providers_loaded();
            if ($pro = $this->providers->get($cdata['prid'])) {
                global $ID;
                $ID = getID();
                $this->_require_user_infrastructure();
                // load command class and process the signin command
                $this->handler =& load_handler_class($this, 'signin', USER_CMD_SCOPE, $cdata['prid'], 'login');
                $result = $this->handler->callService($pro, $cdata['svcd'], true);
                if (is_array($result) && empty($_REQUEST['ajax'])) {
                    msg($result['msg'], $result['code']);
                }
                return;
            }
            // provider not found
            msg('cookie found but provider is unknown or disabled');
            $this->cookie->clean();
        }
        else if (isset($_SESSION[DOKU_COOKIE]['fedauth']['tmpr'])) {
            // temporary fedauth data set, user authenticated but does not have local account
            if ($_REQUEST['do'] == 'register') {
                // if user navigated to standard register page, redirect to fedauth one
                send_redirect(wl(getID(), 'do=fedauth', true, '&') . '&fa[register]');
            }
            if (!isset($_REQUEST['fa']['register'])) {
                // display account creation reminder
                $msg = $this->getLang('registernow');
                $msg = str_replace('@PROVID@', $_SESSION[DOKU_COOKIE]['fedauth']['tmpr']['prnm'], $msg);
                $msg = str_replace('@REGURL@', wl(getID(), 'do=fedauth', true, '&').'&fa[register]', $msg);
                msg($msg, 2);
            }
        }
        // no fedauth cookie nor temp login, do nothing fedauth related
        // unless any other plugin takes over, auth_login() comes into play
    }

    /**
     * Handles federated login action preprocess for fedauth, login, logout
     * and profile actions. Loads required classes and authoriation providers
     * configuration, processes request variables to route commands and
     * optionally parameters to proper classes. Finally performs process()
     * method for the selected command and prints result message (if any)
     * using Dokuwiki message system, the msg() function.
     */
    function handle_act_preprocess(&$event, $param) {
        if ($event->data != 'login' && $event->data != 'logout' &&
            $event->data != 'profile' && $event->data != 'fedauth') {
            return;
        }

        // require infrastructure classes
        $this->_require_user_infrastructure();
        $this->_ensure_no_temp_data();
        $user = $_SERVER['REMOTE_USER'];

        // load providers configuration
        $this->_ensure_providers_loaded();

        // get command array and emulate logout command for dokuwiki logout action
        $fa = ($event->data != 'logout') ? $_REQUEST['fa'] : array('logout' => null);
        // read command arrray
        if (is_array($fa)) {
            $this->cmd = key($fa);
            $this->provid = is_array($fa[$this->cmd]) ? key($fa[$this->cmd]) : null;
        } else {
            $this->cmd = $fa;
            $this->provid = null;
        }

        $defaultcmd = empty($user) ? 'login' : 'manage';

        // validate command array
        if (in_array($this->cmd, $this->commands)) {
            $this->provid = '';
        } else if (!in_array($this->cmd, $this->functions) || !$this->providers->get($this->provid)) {
            // NOTE: would be nicer if redirected to wiki's login page on empty username
            $this->cmd = $defaultcmd;
            $this->provid = '';
        } else if ($this->cmd == 'select') {
            $this->cmd = 'login';
        }

        // takeover fedauth action handling
        if ($event->data == 'fedauth') {
            $event->stopPropagation();
            $event->preventDefault();

            // manage and signedin commands do not require checking the security token
            if ($this->cmd != 'manage' && $this->cmd != 'signedin') {
                if (($this->cmd != 'login' || $this->provid != '') && !checkSecurityToken()) {
                    $this->cmd = $defaultcmd;
                    $this->provid = '';
                }
            }
        }

        // load command class and process the command
        $this->handler =& load_handler_class($this, $this->cmd, USER_CMD_SCOPE, $this->provid, 'login');
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
        global $ID;

        print '<p>' . '<a href="' . wl($ID, 'do=fedauth', true, '&') . '">' . $this->getLang('mylogins') . '</a></p>';
    }

    /**
     * Removes temporary fedauth data in case user authenticated with unassigned
     * identity while not being logged in, and later on he did log in using
     * different credentials instead of creating a new account.
     */
    function _ensure_no_temp_data() {
        if (!empty($_SERVER['REMOTE_USER']) && isset($_SESSION[DOKU_COOKIE]['fedauth']['tmpr'])) {
            @session_start(); // make session writable
            unset($_SESSION[DOKU_COOKIE]['fedauth']['tmpr']);
        }
    }

    function _ensure_providers_loaded() {
        if ($this->providers == null) {
            if ($helper =& plugin_load('helper', 'fedauth')) {
                $this->providers = $helper->getProviders();
            }
        }
    }

    function _require_user_infrastructure() {
        require_once(FEDAUTH_PLUGIN . 'common.php');
        require_once(FEDAUTH_PLUGIN . "classes/fa_base.class.php");
        require_once(FEDAUTH_PLUGIN . "classes/fa_service.class.php");
        require_once(FEDAUTH_PLUGIN . "classes/usr/fa_login.usr.class.php");
    }

    /**
     * Restores arbitrarily selected variables from the pre-authorization
     * reqest and stored in a session variable.
     */
    function _restoreRequestData() {
        global $ACT;

        $_REQUEST = $_SESSION[DOKU_COOKIE]['fedauth']['stor']['rq'];
        $_GET     = $_SESSION[DOKU_COOKIE]['fedauth']['stor']['gt'];
        $_POST    = $_SESSION[DOKU_COOKIE]['fedauth']['stor']['pt'];
        $_FILES   = $_SESSION[DOKU_COOKIE]['fedauth']['stor']['fs'];
        $HTTP_RAW_POST_DATA = $_SESSION[DOKU_COOKIE]['fedauth']['stor']['rp'];
        // deprecated vars are subject to remove
        $HTTP_GET_VARS      = $_SESSION[DOKU_COOKIE]['fedauth']['stor']['hg'];
        $HTTP_POST_VARS     = $_SESSION[DOKU_COOKIE]['fedauth']['stor']['hp'];
        $HTTP_POST_FILES    = $_SESSION[DOKU_COOKIE]['fedauth']['stor']['hf'];

        unset($_SESSION[DOKU_COOKIE]['fedauth']['stor']);

        if (isset($_REQUEST['do'])) {
            $ACT = $_REQUEST['do'];
        }
    }

    /**
     * Detects standard Dokuwiki AJAX call.
     */
    function isAjaxCall() {
        return (isset($_POST['call']) || isset($_GET['call']));
    }

   /**
    * Detects Dokuwiki PHP script files that do not route thru doku.php
    */
   function isLibExe() {
       return (strpos($_SERVER['PHP_SELF'], DOKU_REL . 'lib/exe/') === 0);
   }

} /* action_plugin_federate */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

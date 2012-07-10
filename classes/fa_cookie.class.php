<?php
/**
 * Federated Login for DokuWiki - cookie manipulation class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

// Requires fedauth cookie definition
if (!defined('FEDAUTH_COOKIE')) die();

/**
 * Federated login cookie manipulation class. Gets the user-bound authorization
 * service data from the cookie or sets it as well as with session data.
 *
 * Based on functions in '/inc/auth.php' by Andreas Gohr <andi@splitbrain.org>
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_cookie {

    /**
     * Removes the federated login cookie and all related session data.
     */
    function clean() {
        global $USERINFO;

        // make sure the session is writable (it usually is)
        @session_start();

        // clear the session
        if (isset($_SESSION[DOKU_COOKIE]['fedauth']['user']))
            unset($_SESSION[DOKU_COOKIE]['fedauth']['user']);
        if (isset($_SESSION[DOKU_COOKIE]['fedauth']['prid']))
            unset($_SESSION[DOKU_COOKIE]['fedauth']['prid']);
        if (isset($_SESSION[DOKU_COOKIE]['fedauth']['svcd']))
            unset($_SESSION[DOKU_COOKIE]['fedauth']['svcd']);
        if (isset($_SESSION[DOKU_COOKIE]['fedauth']['stok']))
            unset($_SESSION[DOKU_COOKIE]['fedauth']['stok']);
        if (isset($_SESSION[DOKU_COOKIE]['fedauth']['info']))
            unset($_SESSION[DOKU_COOKIE]['fedauth']['info']);
        if (isset($_SESSION[DOKU_COOKIE]['fedauth']['sgin']))
            unset($_SESSION[DOKU_COOKIE]['fedauth']['sgin']);
        if (isset($_SESSION[DOKU_COOKIE]['bc']))
            unset($_SESSION[DOKU_COOKIE]['bc']);
        if (isset($_SERVER['REMOTE_USER']))
            unset($_SERVER['REMOTE_USER']);
        $USERINFO = null;

        // clear the cookie
        $this->_updateCookie('', time() - 600000);
    }

    /**
     * Gets the user-bound authorization service data from the cookie.
     *
     * Return array information: key - value meaning
     * user   - local username
     * sticky - timeout: true - 1 year, false - use $conf['auth_security_timeout'] value
     *          FIXME reimplement to timeout value
     * prid   - auth provider identifier
     * svcd   - user-bound auth service data
     * stok   - security token
     *
     * @return mixed array with data or false on failure
     */
    function get() {
        if (!isset($_COOKIE[FEDAUTH_COOKIE])) {
            return false;
        }

        list($user, $sticky, $provid, $svcdata, $sectok) = explode('|', $_COOKIE[FEDAUTH_COOKIE], 5);
        $user = base64_decode($user);
        $sticky = (bool) $sticky;
        $provid = base64_decode($provid);
        $svcdata = base64_decode($svcdata);
        $sectok = base64_decode($sectok);

        return array('user' => $user, 'sticky' => $sticky, 'prid' => $provid, 'svcd' => $svcdata, 'stok' => $sectok);
    }

    /**
     * Sets the authorization cookie and adds the user-bound authorization service data to the session.
     *
     * @param string $user username
     * @param string $provid authorization provider identifier
     * @param string $svcdata user-bound authorization service data
     * @param bool $sticky whether or not the cookie will last beyond the session
     */
    function set($user, $provid, $svcdata, $sticky) {
        global $auth, $USERINFO;

        if(!$auth) return false;

        // update vars required for Dokuwiki to acknowledge the logged-in user
        $USERINFO = $auth->getUserData($user);
        $_SERVER['REMOTE_USER'] = $user;

        // prepare fedauth data
        $sectok = sha1(getSecurityToken());
        $cookie = base64_encode($user)    . '|'
                . ((int) $sticky)         . '|' // FIXME reimplement to timeout value
                . base64_encode($provid)  . '|'
                . base64_encode($svcdata) . '|'
                . base64_encode($sectok);
        $time = $sticky ? (time() + 60 * 60 * 24 * 365) : 0; //one year

        // set the cookie
        $this->_updateCookie($cookie, $time);

        // set the session
        $_SESSION[DOKU_COOKIE]['fedauth']['user'] = $user;     // local username
        $_SESSION[DOKU_COOKIE]['fedauth']['prid'] = $provid;   // provider identifier
        $_SESSION[DOKU_COOKIE]['fedauth']['svcd'] = $svcdata;  // auth service data
        $_SESSION[DOKU_COOKIE]['fedauth']['stok'] = $sectok;   // security token
        $_SESSION[DOKU_COOKIE]['fedauth']['info'] = $USERINFO; // local user information
        $_SESSION[DOKU_COOKIE]['fedauth']['buid'] = auth_browseruid();
        $_SESSION[DOKU_COOKIE]['fedauth']['time'] = time();    // current time
        $_SESSION[DOKU_COOKIE]['fedauth']['sgin'] = 1;         // signed-in marker, should be cleared once read
        $_SESSION[DOKU_COOKIE]['fedauth']['rq'] = $_REQUEST;   // temp, DELETEME
    }

    /**
     * Updates the authorization cookie.
     *
     * @param string $value new cookie value
     * @param int $time cookie expire timestamp
     */
    function _updateCookie($value, $time) {
        global $conf;

        $cookieDir = empty($conf['cookiedir']) ? DOKU_REL : $conf['cookiedir'];
        if (version_compare(PHP_VERSION, '5.2.0', '>')) {
            setcookie(FEDAUTH_COOKIE, $value, $time, $cookieDir, '', ($conf['securecookie'] && is_ssl()), true);
        } else {
            setcookie(FEDAUTH_COOKIE, $value, $time, $cookieDir, '', ($conf['securecookie'] && is_ssl()));
        }
    }

} /* fa_cookie */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

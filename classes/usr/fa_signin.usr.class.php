<?php
/**
 * Federated Login for DokuWiki - sign-in processing class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Class responsible for the sign-in process using selected authentication service.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_signin extends fa_login {

    /**
     * Creates the class instance bound to a plugin instance and an authentication provider.
     *
     * @param objref $manager object reference to the admin plugin
     * @param string $cmd name of the command to handle
     * @param string $provid (optional) an authentication provider id
     */
    function __construct(&$manager, $cmd, $provid='') {
        parent::__construct(&$manager, $cmd, $provid);
    }

    function process_signin() {
        global $ID;

        if ($pro = $this->manager->providers->get($this->provid)) {
            $uname = $_REQUEST['fa_signinname'];
            if ($pro->hasUsername() && empty($uname)) {
                return $this->error('signinnamereq', array('@PROVID@' => '<b>'.$pro->getName().'</b>'));
            }
            if ($this->provid == 'openid') {
                $oid = $_REQUEST['fa_openidurl'];
                if (empty($oid) || $oid == 'http://') {
                    return $this->error('oidurlreq');
                }
                $uname = $oid;
            }
            return $this->callService($pro, $uname);
        }
    }

    function html_signin() {
        if (!$this->success) {
            $this->html_login_service_from();
        }
    }

    function callService($pro, $data, $renew=false) {
        global $ID;

        if ($renew) {
            // in case of reneval we want to store all data and retrieve it on auth success
            $_SESSION[DOKU_COOKIE]['fedauth']['stor']['rq'] = $_REQUEST;
            $_SESSION[DOKU_COOKIE]['fedauth']['stor']['gt'] = $_GET;
            $_SESSION[DOKU_COOKIE]['fedauth']['stor']['pt'] = $_POST;
            $_SESSION[DOKU_COOKIE]['fedauth']['stor']['fs'] = $_FILES;
            $_SESSION[DOKU_COOKIE]['fedauth']['stor']['rp'] = $HTTP_RAW_POST_DATA;
            $_SESSION[DOKU_COOKIE]['fedauth']['stor']['hg'] = $HTTP_GET_VARS;
            $_SESSION[DOKU_COOKIE]['fedauth']['stor']['hp'] = $HTTP_POST_VARS;
            $_SESSION[DOKU_COOKIE]['fedauth']['stor']['hf'] = $HTTP_POST_FILES;
        }
        // if current command is not 'fedauth' this means
/*print('<pre>reqe='.print_r($_REQUEST, true).'</pre>');
print('<pre>get='.print_r($_GET, true).'</pre>');
print('<pre>post='.print_r($_POST, true).'</pre>');
exit; // */

        $svcadd = ($renew || empty($_SERVER['REMOTE_USER'])) ? '' : '&mode=add';
        $svcdata = (empty($data)) ? '' : '&svcdata=' . urlencode(base64_encode($data));
        $return_to = wl($ID, 'do=fedauth', true, '&') . '&id=' . $ID . '&fa[signedin]['.$this->provid.']=1' . $svcdata . $svcadd;
        // process the request
        $svc =& $this->getService($pro);
        $result = $svc->request($data, $return_to);
        if ($result == -1) {
            return $this->error('oidurlreq');
        }
        $this->success = true;

        // redirect to OpenID provider for authentication
        header('Location: ' . $result);
        exit;
    }

} /* fa_signin */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

<?php
/**
 * Federated Login for DokuWiki - sign-in processing class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Class responsible for the sign-in process using selected authorization service.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_signin extends fa_login {

    /**
     * Creates the class instance bound to a plugin instance and an authorization provider.
     *
     * @param objref $manager object reference to the admin plugin
     * @param string $cmd name of the command to handle
     * @param string $provid (optional) an authorization provider id
     */
    function __construct(&$manager, $cmd, $provid='') {
        parent::__construct(&$manager, $cmd, $provid);
    }

    function process_signin() {
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

            // process the request
            $return_to = wl($ID, 'do=fedauth', true, '&') . '&id=' . $ID . '&fa[signedin]['.$this->provid.']';
            $svc = $this->getService($pro);
            $result = $svc->request($uname, $return_to);
            if ($result == -1) {
                return $this->error('oidurlreq');
            }
            $this->success = true;
            // redirect to OpenID provider for authentication
            header('Location: ' . $result);
            exit;
        }
    }

    function html_signin() {
        if (!$this->success) {
            $this->html_login_service_from();
        }
    }

} /* fa_signin */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

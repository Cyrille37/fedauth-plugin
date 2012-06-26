<?php
/**
 * Federated Login for DokuWiki - complete sign-in process class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Class responsible for completing the successful sign-in process using selected authorization service.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_signedin extends fa_login {

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

    function process_signedin() {
        if ($_REQUEST['openid_mode'] != 'id_res') {
            // TODO: handle openid_mode == 'cancel'
            return $this->error('authfailed');
        }

        $svc = $this->getService($pro);
        $result = $svc->response(wl($ID, 'do=fedauth', true, '&'));
        if ($result == -1) {
            return $this->error('authfailed');
        }
        else if ($result == -2) {
            return $this->error('identitymissing');
        }
        $this->success = true;
    }

    function html_signedin() {
//        print "<pre>".print_r($_REQUEST, true)."</pre>";
        print "Authorization successful. Biding remote credentials to local account is not yet implemented, however.";
        if (!$this->success) {
            $this->html_login_service_from();
        }
    }

} /* fa_signedin */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

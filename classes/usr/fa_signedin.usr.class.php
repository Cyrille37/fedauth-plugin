<?php
/**
 * Federated Login for DokuWiki - complete sign-in process class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Class responsible for completing the successful sign-in process using selected authentication service.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_signedin extends fa_login {

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

    function process_signedin() {
        global $ID;

        $svc =& $this->getService(null); // parameter not required at this time
        $result = $svc->response(wl($ID, 'do=fedauth', true, '&'));
        if ($result == -1) {
            return $this->error('authfailed');
        }
        else if ($result == -2) {
            return $this->error('identitymissing');
        }

        $svcdata = (empty($_REQUEST['svcdata'])) ? '' : urldecode(base64_decode($_REQUEST['svcdata']));
        return $this->_process_claimed_identity($result, $svcdata);
    }

    function _process_claimed_identity($claimedId, $svcdata) {
        $store =& $this->getUserStore();
        $uname = $store->getUsernameByIdentity($this->provid, $claimedId);
        $pname = @$this->manager->providers->get($this->provid)->getName();

        if (empty($_SERVER['REMOTE_USER'])) {
            // not logged in; login or create
            if ($uname === false) {
                // claimed id not associated with local account
                if (actionOK('register')) {
                    // redirect to create new account
                    $this->_storeTempAuth($claimedId, $svcdata, $pname);
                    $_REQUEST['mode'] = 'register';
                }
                else {
                    // inform that registration is disabled
                    $this->msg($this->error('regdisabled', array('@PROVID@' => $pname)));
                }
            }
            else {
                // claimed id associated, login the user
                $this->manager->cookie->set($uname, $this->provid, $svcdata, false /*$sticky*/);
                $store->refreshUserDataEntry($claimedId);
            }
        }
        else {
            if ($uname !== false) {
                // claimed id already assigned to user account, return error
                $this->msg($this->error('alreadyassigned', array('@PROVID@' => $pname)));
            }
            else {
                // add claimed id to user's identities store
                $store->addUserDataEntry($this->provid, $claimedId);
                $this->msg($this->success('loginadded', array('@PROVID@' => $pname)));
            }
        }

        $this->success = true;
        // redirect and exit process
        send_redirect($this->restoreLocation());
    }

    /**
     * Stores temporary login data until users creates local account.
     */
    function _storeTempAuth($claimedId, $svcdata, $pname) {
        $_SESSION[DOKU_COOKIE]['fedauth']['tmpr'] = array(
            'prid' => $this->provid,
            'prnm' => $pname,
            'ident' => $claimedId,
            'svcd'  => $svcdata,
            'email' => $_REQUEST['openid_sreg_email'],
            'fullname' => $_REQUEST['openid_sreg_fullname'],
            'nickname' => $_REQUEST['openid_sreg_nickname']
        );
    }

} /* fa_signedin */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

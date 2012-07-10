<?php
/**
 * Federated Login for DokuWiki - remove user fedauth entry class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Class responsible for removing user authorization identity from the user's fedauth profile.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_remove extends fa_login {

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

    function process_remove() {
        if (empty($_SERVER['REMOTE_USER'])) return null;

        $uid = base64_decode(key($_REQUEST['fa']['remove'][$this->provid]));

        $store = $this->getUserStore();
        if ($entry = $store->deleteUserDataEntry($uid)) {
            $this->success = true;
            $pname = $this->manager->providers->get($entry['id'])->getName();
            $this->msg($this->success('logindel', array('@PROVID@' => $pname)));
            $_REQUEST['mode'] = 'removed';
        }
        send_redirect($this->restoreLocation());
    }

} /* fa_remove */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

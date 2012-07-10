<?php
/**
 * Federated Login for DokuWiki - logout action handling class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Class responsible for clearing the user-related fedauth data on logout.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_logout extends fa_base {

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

    /**
     * Removes fedauth cookie and clears related session data.
     */
    function process_logout() {
        $this->success = true;
        if ($this->manager->cookie) {
            $this->manager->cookie->clean();
            return $this->info('logoutok');
        }
        return null;
    }

} /* fa_logout */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

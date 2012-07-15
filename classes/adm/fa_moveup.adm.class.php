<?php
/**
 * Federated Login for DokuWiki - move up a provider class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Authorization providers management class responsible
 * for moving a provider item up in the list order.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_moveup extends fa_manage {

    /**
     * Creates the class instance bound with the admin plugin and an authorization provider.
     *
     * @param objref $manager object reference to the admin plugin
     * @param string $cmd name of the command to handle
     * @param string $provid (optional) an authorization provider id
     */
    function __construct(&$manager, $cmd, $provid='') {
        parent::__construct(&$manager, $cmd, $provid);
    }

    /**
     * Performs the move up action in the providers list order.
     *
     * @return string the processing result message
     */
    function process_moveup() {
        if ($this->manager->providers->moveUp($this->provid)) {
            $this->saveConfig();
            $this->success = true;
            return 'Your changes have been saved.';
        }
        return '';
    }

    /**
     * Handles AJAX call to return the result in JSON format.
     *
     * @return bool true on success
     */
    function handle_ajax_moveup() {
        print '{"success":' . (int)$this->success . '}';
        return true;
    }

} /* fa_moveup */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

<?php
/**
 * Federated Login for DokuWiki - remove a provider class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Authorization providers management class responsible
 * for removing a provider item from the local configuration.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_remove extends fa_manage {

    /**
     * Creates the class instance bound with the admin plugin and an authorization provider.
     *
     * @param objref $manager object reference to the admin plugin
     * @param string $provid (optional) an authorization provider id
     */
    function __construct(&$manager, $provid='') {
        parent::__construct(&$manager, $provid);
    }

    /**
     * Performs removal of the provider item from the local configuration.
     *
     * @return string the processing result message
     */
    function process_remove() {
        return '';
    }

    /**
     * Handles AJAX call to return the result in JSON format.
     *
     * @return bool true on success
     */
    function handle_ajax_remove() {
        print '{"success":' . (int)$this->success . '}';
        return true;
    }

} /* fa_remove */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

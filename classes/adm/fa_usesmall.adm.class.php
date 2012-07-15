<?php
/**
 * Federated Login for DokuWiki - configure a provider as small button class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Authorization providers management class responsible
 * for moving a provider item to the small buttons list.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_usesmall extends fa_manage {

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
     * Performs the move action to the small provider button list.
     *
     * @return string the processing result message
     */
    function process_usesmall() {
        if ($this->manager->providers->toggleSize($this->provid)) {
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
    function handle_ajax_usesmall() {
        if ($this->success) {
            // now, when in small providers list, output the move to large button info
            print $this->_json_buttoninfo('uselarge');
            return true;
        }
        print '{"success":0}';
        return false;
    }

} /* fa_usesmall */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

<?php
/**
 * Federated Login for DokuWiki - move down a provider class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Authorization providers management class responsible
 * for moving a provider item down in the list order.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_movedn extends fa_manage {

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
     * Performs the move down action in the providers list order.
     *
     * @return string the processing result message
     */
    function process() {
        if ($this->manager->providers->moveDown($this->provid)) {
            $this->saveConfig();
            return 'Your changes have been saved.';
        }
        return '';
    }


} /* fa_movedn */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

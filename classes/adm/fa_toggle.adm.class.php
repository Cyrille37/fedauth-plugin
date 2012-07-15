<?php
/**
 * Federated Login for DokuWiki - toggle enabled providers class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Authorization providers management class responsible 
 * for toggling the enabled state of the providers.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_toggle extends fa_manage {

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
     * Performs either enable or disable action depending on checked items.
     *
     * @return string the processing result message
     */
    function process_toggle() {
        if (!$this->isValidListSource()) return '';

        $enable = is_array($_REQUEST['toggle']) ? $_REQUEST['toggle'] : array();
        foreach ($this->getProvidersByListSource() as $id => $pro) {
            if ($pro->toggle(in_array($id, $enable))) {
                $save = true;
            }
        }
        $this->success = true; // always success, even with no changes
        if ($save) {
            $this->saveConfig();
            return 'Your changes have been saved.';
        }
        return '';
    }

    /**
     * Handles AJAX call to display updated provider list.
     *
     * @return bool true on success
     */
    function handle_ajax_toggle() {
        if (!$this->isValidListSource()) return false;

        print $this->html_providers_list($this->getProvidersByListSource(), $this->listSource == 'large');
        return true;
    }

} /* fa_toggle */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

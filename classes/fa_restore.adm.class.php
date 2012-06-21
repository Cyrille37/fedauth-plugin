<?php
/**
 * Federated Login for DokuWiki - restore default settings class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

// config path must be defined
if (!defined('CONFIG_PATH')) die('CONFIG_PATH is not defined!');

/**
 * Authorization providers management class responsible for restoring
 * the default settings. Deletes local plugin cofiguration.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_restore extends fa_manage {

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
     * Performs the restore defaults action by deleting the local plugin config files.
     */
    function process_restore() {
        global $ID;

        if ($this->_deleteDir(CONFIG_PATH)) {
            $location = wl($ID).'?do='.$_REQUEST['do'].'&page='.$_REQUEST['page'];
            header('Location: '.$location);
        }
        return '';
    }

    /**
     * Deletes a directory tree.
     *
     * @param string $dirPath directory path to delete
     */
    function _deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            return false;
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
        return true;
    }

} /* fa_restore */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

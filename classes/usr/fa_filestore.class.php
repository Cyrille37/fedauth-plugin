<?php
/**
 * Federated Login for DokuWiki - file-based data storage class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

// The filestore requires Dokuwiki
if (!defined('DOKU_INC')) die();

// Constants for known core changelog line types.
// Use these in place of string literals for more readable code.
if (!defined('FEDAUTH_CHANGE_TYPE_CREATE'))  define('FEDAUTH_CHANGE_TYPE_CREATE',  'C');
if (!defined('FEDAUTH_CHANGE_TYPE_DELETE'))  define('FEDAUTH_CHANGE_TYPE_DELETE',  'D');
if (!defined('FEDAUTH_CHANGE_TYPE_REFRESH')) define('FEDAUTH_CHANGE_TYPE_REFRESH', 'R');

/**
 * Federated login file-based data storage class. Handles all
 * i/o operations performed on authorization user data.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_filestore {

    /**
     * Base data storage path, eg. [dw_root]/data/users/
     */
    var $root = '';

    /**
     * Base providers configuration path, eg. [dw_root]/conf/fedauth/
     */
    var $conf = '';

    /**
     * Array containing user identity entries.
     */
    var $userData = null;

    /**
     * Array containing the identities from single provider
     * service associated with local usernames (accounts).
     */
    var $claimedIdentities = null;

    /**
     * Creates the class instance.
     */
    function __construct() {
        $this->root = DOKU_INC . 'data/users/';
        $this->conf = DOKU_CONF . 'fedauth/';
        io_makeFileDir($this->root);
        io_makeFileDir($this->conf);
//        print "<pre>" . $this->root . "\n" . $this->conf . "</pre>";
    }

    /**
     * Loads the identities from single provider service
     * associated with local usernames (accounts) from a file.
     *
     * @param string $providerId provider identifier
     * @return mixed identity+username pairs array or false on failure
     */
    function &getClaimedIdentities($providerId) {
        if (is_array($this->claimedIdentities)) {
            return $this->claimedIdentities;
        }
        $lines = @file_get_contents($this->provFN($providerId));
        $lines = explode("\n", $lines);
        if (empty($lines)) return false;

        $data = array();
        foreach ($lines as $value) {
            $tmp = $this->parseClaimedIdentitiesLine($value);
            if ($tmp !== false) {
                $data[] = $tmp;
            }
        }
        $this->claimedIdentities = $data;
        return $this->claimedIdentities;
    }

    /**
     * Returns a username associated with a claimed identity.
     *
     * @param string $providerId provider identifier
     * @param string $claimedId claimed identifier
     * @return mixed username or false on failure
     */
    function getUsernameByIdentity($providerId, $claimedId) {
        if (($entries =& $this->getClaimedIdentities($providerId)) === false) return false;

        $strip = array("\t", "\n");
        $claimedId = str_replace($strip, '', $claimedId);
        foreach ($entries as $entry) {
            if ($entry['ident'] == $claimedId) {
                return $entry['user'];
            }
        }
        return false;
    }

    /**
     * Parses a claimed identity line into it's components.
     *
     * @param string $line claimed identity line to parse
     * @return mixed claimed identity entry array or false on failure
     */
    function parseClaimedIdentitiesLine($line) {
        $tmp = explode("\t", $line);
        if ($tmp!==false && count($tmp)>1) {
            $info = array(
                'ident' => $tmp[0], // user identity
                'user'  => $tmp[1]); // local username
            return $info;
        }
        return false;
    }

    /**
     * Loads user authorization data from file.
     *
     * @return mixed user authorization data array or false on failure
     */
    function &getUserData() {
        if (is_array($this->userData)) {
            return $this->userData;
        }
        $lines = @file_get_contents($this->userFN());
        $lines = explode("\n", $lines);
        if (empty($lines)) return false;

        $data = array();
        foreach ($lines as $value) {
            $tmp = $this->parseUserDataLine($value);
            if ($tmp !== false) {
                $data[] = $tmp;
            }
        }
        $this->userData = $data;
        return $this->userData;
    }

    /**
     * Searches for user authorization data entry by identity.
     *
     * @param string $claimedId identity associated with the user data entry
     * @return mixed user authorization data array or false on failure
     */
    function getUserDataEntry($claimedId) {
        if (($entries =& $this->getUserData()) === false) return false;

        $strip = array("\t", "\n");
        $claimedId = str_replace($strip, '', $claimedId);
        foreach ($entries as $entry) {
            if ($entry['ident'] == $claimedId) {
                return $entry;
            }
        }
        return false;
    }

    /**
     * Parses an user data line into it's components.
     *
     * @param string $line user data line to parse
     * @return mixed user data entry array or false on failure
     */
    function parseUserDataLine($line) {
        $tmp = explode("\t", $line);
        if ($tmp!==false && count($tmp)>1) {
            $info = array(
                'id'    => $tmp[0], // provider id
                'ident' => $tmp[1], // user identity
                'last'  => (int)$tmp[2]); // last used
            return $info;
        }
        return false;
    }

    /**
     * Adds an entry to the user authorization data file.
     *
     * @param string $providerId identifier of the auth provider service
     * @param string $claimedId user authorization identity
     * @param int $date (optional) timestamp of the change
     */
    function addUserDataEntry($providerId, $claimedId, $date=null) {
        if (!$date) $date = time(); //use current time if none supplied
        $user = $_SERVER['REMOTE_USER'];

        $strip = array("\t", "\n");
        $data = array(
            'id'    => $providerId, // provider id
            'ident' => str_replace($strip, '', $claimedId), // user identity
            'last'  => $date); // last used
        $provline = array(
            'ident' => $data['ident'],
            'user'  => $user);

        $dataline = implode("\t", $data) . "\n";
        $provline = implode("\t", $provline) . "\n";
        io_saveFile($this->userFN($user), $dataline, true); //user data
        io_saveFile($this->provFN($providerId), $provline, true); //global provider identities
        $this->addLogEntry($date, $providerId, $claimedId, FEDAUTH_CHANGE_TYPE_CREATE);

        $this->userData = null; // force reload userData on next get
    }

    /**
     * Deletes user authorization data entry from the data file by identity.
     *
     * @param string $claimedId identity associated with the user data entry
     * @return mixed deleted entry or false on failure
     */
    function deleteUserDataEntry($claimedId) {
        if ($entry = $this->getUserDataEntry($claimedId)) {
            $user = $_SERVER['REMOTE_USER'];
            $provline = array(
                'ident' => $entry['ident'],
                'user'  => $user);
            $dataline = implode("\t", $entry) . "\n";
            $provline = implode("\t", $provline) . "\n";
            io_deleteFromFile($this->userFN($user), $dataline);
            io_deleteFromFile($this->provFN($entry['id']), $provline);
            $this->addLogEntry(time(), $entry['id'], $claimedId, FEDAUTH_CHANGE_TYPE_DELETE);
            $this->userData = null; // force reload userData on next get
            return $entry;
        }
        return false;
    }

    /**
     * Updates last used time for user authorization data entry by identity.
     *
     * @param string $claimedId identity associated with the user data entry
     */
    function refreshUserDataEntry($claimedId) {
        if ($entry = $this->getUserDataEntry($claimedId)) {
            $user = $_SERVER['REMOTE_USER'];
            $dataline = implode("\t", $entry) . "\n";
            io_deleteFromFile($this->userFN($user), $dataline);
            $entry['last'] = time();
            $dataline = implode("\t", $entry) . "\n";
            io_saveFile($this->userFN($user), $dataline, true);
            $this->addLogEntry(time(), $entry['id'], $claimedId, FEDAUTH_CHANGE_TYPE_REFRESH);
            $this->userData = null; // force reload userData on next get
        }
    }

    /**
     * Parses a changelog line into it's components.
     *
     * @param string $line changelog line to parse
     * @return mixed changelog entry array or false on failure
     */
    function parseChangelogLine($line) {
        $tmp = explode("\t", $line);
        if ($tmp!==false && count($tmp)>1) {
            $info = array(
                'date'  => (int)$tmp[0],
                'ip'    => $tmp[1],
                'type'  => $tmp[2],
                'id'    => $tmp[3],
                'ident' => $tmp[4],
                'user'  => $tmp[5]);
            return $info;
        }
        return false;
    }

    /**
     * Adds an entry to the user authorization data changelog.
     *
     * @param int $date timestamp of the change
     * @param string $providerId identifier of the auth provider service
     * @param string $claimedId user authorization identity
     * @param string $type type of the change see FEDAUTH_CHANGE_TYPE_*
     * @param bool $isExternal (optional) is change made by user or as result of internal cleanup
     */
    function addLogEntry($date, $providerId, $claimedId, $type, $isExternal=true) {
        if (!$date) $date = time(); //use current time if none supplied
        $remote = ($isExternal) ? clientIP(true) : '127.0.0.1';
        $user   = ($isExternal) ? $_SERVER['REMOTE_USER'] : '';

        $strip = array("\t", "\n");
        $logline = array(
            'date'  => $date,
            'ip'    => $remote,
            'type'  => str_replace($strip, '', $type),
            'id'    => $providerId,
            'ident' => str_replace($strip, '', $claimedId),
            'user'  => $user);

        // add the changelog line
        $logline = implode("\t", $logline) . "\n";
        $refresh = ($type == FEDAUTH_CHANGE_TYPE_REFRESH);
        io_saveFile($this->userFN($user, $refresh ? 'activity' : 'changes'), $logline, true); //user data changelog
        // don't use global provider changelog to log sign-ins; too much data
        if (!$refresh) {
            io_saveFile($this->provFN($providerId, 'changes'), $logline, true);
        }
    }

    /**
     * Returns the full path to the provider file with user identities.
     * Note: use the extension parameter to get related (eg. meta) file path.
     *
     * @param string $provid provider identifier
     * @param string $ext (optional) file extension
     * @return string the full path
     */
    function provFN($provid, $ext='conf') {
        return $this->conf . $provid . '.' . $ext;
    }

    /**
     * Returns the full path to the user file with all associated idenities.
     * Note: use the extension parameter to get related (eg. meta) file path.
     *
     * @param string $username (optional) the username; leave empty to autodetect
     * @param string $ext (optional) file extension
     * @return string the full path
     */
    function userFN($username='', $ext='data') {
        if (empty($username)) $username = $_SERVER['REMOTE_USER'];
        return $this->root . substr($username, 0, 1) . '/' . $username . '/fedauth.' . $ext;
    }

} /* fa_filestore */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

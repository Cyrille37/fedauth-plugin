<?php
/**
 * Federated Login for DokuWiki - default configuration file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

$conf['adminsonly']      = 1; // restrict the authorization providers configuration to admins only
$conf['customloginform'] = 0; // use the customized login form instead of adding federated login to bottom of the original login form
$conf['fedauthonly']     = 0; // disable local authorization and use federated login only (no local passwords)
$conf['useajax']         = 1; // use AJAX calls (this works only for browsers that support JavaScript)

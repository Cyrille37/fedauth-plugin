<?php
/**
 * Federated Login for DokuWiki - English language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu']            = 'Manage Federated Login Providers';

$lang['btn_toggle']      = 'Toggle';
$lang['btn_moveup']      = 'Move Up';
$lang['btn_movedn']      = 'Move Down';
$lang['btn_uselarge']    = 'To Large';
$lang['btn_usesmall']    = 'To Small';
$lang['btn_remove']      = 'Delete';
$lang['btn_details']     = 'Details';
$lang['btn_restore']     = 'Restore Defaults';
$lang['btn_login']       = 'Log In';
$lang['btn_signin']      = 'Sign In';

$lang['inuse']           = 'in use';
$lang['disabled']        = 'disabled';
$lang['serviceurl']      = 'Service URL';

$lang['dologin']         = 'Log In';
$lang['addlogin']        = 'Add Login';
$lang['mylogins']        = 'manage my logins';
$lang['gotlogin']        = 'Do you already have an account on one of these sites?';
$lang['clicktoadd']      = 'Click the logo to <b>add another login</b> to your account:';
$lang['clicktologin']    = 'Click the logo to <b>log in</b> with it here:';
$lang['manualopenid']    = 'Or, you can manually enter your OpenID';
$lang['morelogin']       = 'show more options...';
$lang['enterlogin']      = 'Enter your <b>@PROVID@</b> login name:';
$lang['unknowncmd']      = 'Uknown Federated Login command:';
$lang['signinnamereq']   = 'To use the <b>@PROVID@</b> authentication service you have to provide a login name.';
$lang['oidurlreq']       = 'Valid URL is required to use the OpenID authentication service.';
$lang['authfailed']      = 'Authentication failed.';
$lang['identitymissing'] = 'Authentication successful, however your identity information is missing.';
$lang['alreadyassigned'] = 'The retrieved identity is already associated with an account on this site.';
$lang['logoutok']        = 'You have been logged out successfully.';
$lang['loginadded']      = 'Your <b>@PROVID@</b> login has been successfully associated with this account.';
$lang['logindel']        = 'Your <b>@PROVID@</b> login has been removed from this account.';
$lang['regdisabled']     = 'Your <b>@PROVID@</b> login is not associated with any account and registration of new accounts is disabled.';
$lang['registernow']     = 'You are authenticated with your <b>@PROVID@</b> login, but you still have to <a href="@REGURL@">create</a> an account on this site.';

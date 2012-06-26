<?php
/**
 * Federated Login for DokuWiki - authorization service stub class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * A stub class that emulates an interface between the real
 * authorization client service implementations and their consumers.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_service {

    /**
     * Authorization provider configuration object.
     */
    var $provider = null;

    /**
     * Creates the class instance bound to a provider object.
     *
     * @param objref $provider authorization provider configuration object
     */
    function __construct(&$provider) {
        $this->provider =& $provider;
    }

    /**
     * When overriden, performs the authorization request.
     *
     * @param string $username customized string required to perform the request, most likely an username
     * @param string $return_to an URL user by the authorization service to pass the response (result)
     */
    function request($username, $return_to) {
    }

    /**
     * When overriden, processes the authorization response.
     *
     * @param string $ref referer string used to identify the authorization request
     */
    function response($ref) {
    }

} /* fa_service */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

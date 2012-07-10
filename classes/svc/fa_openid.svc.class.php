<?php
/**
 * Federated Login for DokuWiki - OpenID authorization service client class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * The OpenID authorization service client class.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_openid_svc extends fa_service {

    /**
     * OpenID consumer object.
     */
    var $consumer = null;

    /**
     * Creates the class instance bound to a provider object.
     *
     * @param objref $provider authorization provider configuration object
     */
    function __construct(&$provider) {
        parent::__construct(&$provider);
    }

    /**
     * Returns the OpenID consumer.
     *
     * @author Aoi Karasu <aoikarasu@gmail.com>
     * @author Original by François Hodierne (http://h6e.net/)
     * @return the OpenID consumer object
     */
    function &getConsumer()
    {
        global $conf;

        if (isset($this->consumer)) {
            return $this->consumer;
        }
        define('Auth_OpenID_RAND_SOURCE', null);
        set_include_path( get_include_path() . PATH_SEPARATOR . FEDAUTH_PLUGIN );
        require_once("Auth/OpenID/Consumer.php");
        require_once("Auth/OpenID/FileStore.php");
        // start session (needed for YADIS)
        session_start();
        // create file storage area for OpenID data
        $store = new Auth_OpenID_FileStore($conf['tmpdir'] . '/fedauth/openid');
        // create OpenID consumer
        $this->consumer = new Auth_OpenID_Consumer($store);
        return $this->consumer;
    }

    /**
     * Performs the OpenID authorization request.
     *
     * @param string $username an username or OpenID URL
     * @param string $return_to an URL user by the authorization service to pass the response (result)
     * @return mixed error code or fully configured authorization service request URL
     */
    function request($username, $return_to) {
        global $ID;

        // prepare service URL
        $url = $this->provider->getURL();
        if (empty($url)) {
            $url = $username;
        }
        else {
            $url = str_replace('{username}', $username, $url);
        }

        // try to login with the service URL
        $consumer =& $this->getConsumer();
        $auth = $consumer->begin($url);
        if (!$auth) {
            return -1; // invalid openid
        }
        // add an attribute query extension to get user details
        require_once("Auth/OpenID/SReg.php");
        $attrq = Auth_OpenID_SRegRequest::build(array(),array('nickname','email','fullname'));
        $auth->addExtension($attrq);

        $url = $auth->redirectURL(DOKU_URL, $return_to);
        return $url;
    }

    /**
     * Processes the OpenID authorization response.
     *
     * @param string $ref referer URL used to identify the authorization request
     * @return mixed error code or claimed identity string
     */
    function response($ref) {
        $consumer =& $this->getConsumer();
        $response = $consumer->complete($ref);

        if ($_REQUEST['openid_mode'] != 'id_res') {
            // TODO: handle openid_mode == 'cancel'
            return -1; // authorization failed
        }

        if ($response->status == Auth_OpenID_SUCCESS) {
            $openid = isset($_REQUEST['openid_claimed_id']) ? $_REQUEST['openid_claimed_id'] : $_REQUEST['openid1_claimed_id'];
            if (empty($openid)) {
                return -2; // cannot find the claimed ID
            }
            return $openid; // return the claimed ID
        }
        return -1; // authorization failed
    }

} /* fa_openid_svc */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

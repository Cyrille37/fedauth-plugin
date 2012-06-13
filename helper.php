<?php
/**
 * Federated Login for DokuWiki - helper class
 *
 * Enables your DokuWiki to provide users with 
 * Hybrid OAuth + OpenId federated login.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
if (!defined('FEDAUTH_PLUGIN')) define('FEDAUTH_PLUGIN', DOKU_PLUGIN . 'fedauth/');
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', DOKU_CONF . 'fedauth/');
if (!defined('CONFIG_INC')) define('CONFIG_INC', CONFIG_PATH . 'providers.php');
if (!defined('DEFIMG_INC')) define('DEFIMG_INC', FEDAUTH_PLUGIN . 'images/');

require_once(FEDAUTH_PLUGIN . "classes/fa_provider.class.php");
require_once(FEDAUTH_PLUGIN . "classes/fa_providerlist.class.php");

class helper_plugin_fedauth extends DokuWiki_Plugin {

    /**
     * Authorization providers collecion.
     */
    var $providers = null;

    /**
     * Returns the plugin meta information.
     */
    function getInfo() {
        return array(
            'author' => 'Aoi Karasu',
            'email'  => 'aoikarasu@gmail.com',
            'date'   => '2012-06-09',
            'name'   => 'Federated Login Plugin',
            'desc'   => 'Functions to handle hybrid oauth + openid login',
            'url'    => 'http://www.dokuwiki.org/plugin:fedauth',
        );
    }

    /**
     * Returns the metadata of methods provided by this plugin.
     */
    function getMethods() {
        $result = array();
        $result[] = array(
                'name'   => 'getProviders',
                'desc'   => 'returns the authorization provider collection',
                'return' => array('authorization provider collection' => 'object'),
                );
        return $result;
    }

    function getProviders() {
        // try to return loaded providers
        if ($this->providers) {
            return $this->providers;
        }
        // try to load providers from local config
        $provs = fa_providerlist::create(CONFIG_INC);
        if ($provs->count() == 0) {
            // load default providers, setup details and save as local
            $provs = fa_providerlist::create(FEDAUTH_PLUGIN . 'providers.php');
            foreach ($provs->getAll() as $key => $prov) {
                $prov->setupDetails(DEFIMG_INC);
            }
            $provs->setConfigFile(CONFIG_INC);
            io_makeFileDir(CONFIG_INC);
            $provs->save($_SERVER['REMOTE_USER']);
        }
        $this->providers = $provs;
        return $this->providers;
    }

} /* helper_plugin_federate */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

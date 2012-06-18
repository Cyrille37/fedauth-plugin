<?php
/**
 * Federated Login for DokuWiki - provider list class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

if(!defined('FA_PROVLIST_INC')) define('FA_PROVLIST_INC', __FILE__);

define('PROV_LARGE', 2);
define('PROV_SMALL', 1);

/**
 * The provider list class stores all the authorization providers information
 * and the configuration of display and usage.
 *
 * REMARKS:
 * Most of the methods in this class assumes that the arrays responsible for
 * the providers display order are normalized. Any outside denormalization will
 * cause an unexpected behaviour and possible unhandled exceptions.
 *
 * On-load normalization is planned in the nearest future.
 *
 * @author Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_providerlist {

    /**
     * All configured providers.
     *
     * @var array
     * @access private
     */
    var $providers = array();

    /**
     * Display order of the large provider buttons.
     *
     * @var array
     * @access private
     */
    var $orderLarge = array();

    /**
     * Display order of the small provider buttons.
     *
     * @var array
     * @access private
     */
    var $orderSmall = array();

    /**
     * Name of the configuration file.
     *
     * @var string
     * @access private
     */
    var $cfgFile = '';

    /**
     * Creates an instance of fa_providerlist class using specified configuration file.
     *
     * @param string $cfg name of the configuration file
     * @return object fa_providerlist class instance
     */
    public static function create($cfg) {
        $instance = new self();
        $instance->loadFrom($cfg);
        return $instance;
    }

   /**
     * Loads and parses a configuration file to build the list of athorization providers.
     *
     * @param string $cfg name of the configuration file
     */
    protected function loadFrom($cfg) {
        $this->cfgFile = $cfg;

        if (file_exists($this->cfgFile)) {
            $fa_providers = array();
            $fa_order_large = array();
            $fa_order_small = array();

            include($this->cfgFile);
            $this->order_large = $fa_order_large;
            $this->order_small = $fa_order_small;
            // TODO: normalize the order arrays (most of the methods assumes they are)
            foreach ($fa_providers as $id => $data) {
                $provider = fa_provider::create($id, $data);
                $this->providers[$id] = $provider;
                // if a provider entry is not in any of sort arrays, add it to small
                if (!in_array($id, $this->order_large) && !in_array($id, $this->order_small)) {
                    array_push($this->order_small, $id);
                }
            }
        }
    }

    /**
     * Adds a custom authorization provider to the provider list.
     *
     * Note: provider id must be unique or else addition fails.
     *
     * @param string $id provider identifier
     * @param string $name name of the provider
     * @param string $url URL to the provider's authorization service
     * @param string $large wikilink to large image
     * @param string $small wikilink to small image
     */
    public function addCustom($id, $name, $url, $large, $small) {
        if (array_key_exists($id, $this->providers)) return 0;
        $provider = fa_provider::createCustom($id, $name, $url, $large, $small);
        $this->providers[$id] = $provider;
        array_push($this->order_small, $id);
        return $this->providers[$id];
    }

    /**
     * Returns the number of all configured providers.
     *
     * @return int number of providers
     */
    public function count() {
        return count($this->providers);
    }

    /**
     * Returns the number of all providers set to be displayed as large buttons.
     *
     * @return int number of providers
     */
    public function countLarge() {
        return count($this->order_large);
    }

    /**
     * Returns the number of all providers set to be displayed as small buttons.
     *
     * @return int number of providers
     */
    public function countSmall() {
        return count($this->order_small);
    }

    /**
     * Returns the requested authorization provider object.
     *
     * @param string $id provider identifier
     * @return object configured authorization provider
     */
    public function get($id) {
        return $this->providers[$id];
    }

    /**
     * Returns an array of all configured authorization providers.
     *
     * @return array all configured providers
     */
    public function getAll() {
        // NOTE: consider returning normalized array
        return $this->providers;
    }

    /**
     * Returns an array of the providers set to be displayed as large buttons.
     *
     * @return array providers to be shown as large buttons
     */
    public function getLarge() {
        $ret = array();
        foreach($this->order_large as $key => $val) {
            $ret[$val] = $this->providers[$val];
        }
        return $ret;
    }

    /**
     * Returns an array of the providers set to be displayed as small buttons.
     *
     * @return array providers to be shown as small buttons
     */
    public function getSmall() {
        $ret = array();
        foreach($this->order_small as $key => $val) {
            $ret[$val] = $this->providers[$val];
        }
        return $ret;
    }

    /**
     * Returns the XHTML of provider image choosing automatically
     * whether it should be small or large.
     *
     * @param string $id provider identifier
     * @param string $class (optional) a CSS class to use
     * @return string rendered image XHTML
     */
    public function getImageXHTML($id, $class='') {
        $size = in_array($id, $this->order_large) ? PROV_LARGE : PROV_SMALL;
        return $this->providers[$id]->getImageXHTML($size, $class);
    }

    /**
     * Indicates whether a provider is first on its order list.
     *
     * @param string $id provider identifier
     * @throws ErrorException provider not found in any of the order arrays
     * @return bool true, if the provider is the first one
     */
    public function isFirst($id) {
        $src =& $this->_getOrderFor($id);
        if (src == null) throw new ErrorException("isFirst() - item not found in any of the order arrays");
        $first = reset($src);
        return $first == $id;
    }

    /**
     * Indicates whether a provider is the last one on its order list.
     *
     * @param string $id provider identifier
     * @throws ErrorException provider not found in any of the order arrays
     * @return bool true, if the provider is the last one
     */
    public function isLast($id) {
        $src =& $this->_getOrderFor($id);
        if (src == null) throw new ErrorException("isLast() - item not found in any of the order arrays");
        $last = end($src);
        return $last == $id;
    }

    /**
     * Moves a provider one step up in its order array.
     *
     * @return true, if move succeded
     */
    public function moveDown($id) {
        $src =& $this->_getOrderFor($id);
        if (src == null) return false;

        // get the position (key is integer), assumed normalized array
        $index = array_search($id, $src);

        // do movedown
        if(count($src) > $index) {
            array_splice($src, $index+2, 0, $src[$index]);
            array_splice($src, $index, 1);
            return true;
        }
        return false;
    }

    /**
     * Moves a provider one step down in its order array.
     *
     * @return true, if move succeded
     */
    public function moveUp($id) {
        $src =& $this->_getOrderFor($id);
        if (src == null) return false;

        // get the position (key is integer), assumed normalized array
        $index = array_search($id, $src);

        // do moveup
        if ((count($src) > $index) && ($index>0)) {
            array_splice($src, $index-1, 0, $src[$index]);
            array_splice($src, $index+1, 1);
            return true;
        }
        return false;
    }

    /**
     * Removes a custom authorization provider from the provider list.
     *
     * @param string $id provider identifier
     */
    public function removeCustom($id) {
        if (!array_key_exists($id, $this->providers)) return false;
        if (!$this->providers[$id]->isRemovable()) return false;

        unset($this->providers[$id]);

        // remove entry from order array
        $ord =& $this->_getOrderFor($id);
        if ($ord != null) {
            $index = array_search($id, $ord);
            array_splice($ord, $index, 1);
        }
        return 1;
    }

    /**
     * Save the authorization providers configuration back to the file it was loaded from
     * or to a new file, if fname is supplied.
     *
     * @param string $user username of the user performing the save
     */
    public function save($user) {
        // create header
        $date = date('r');
        $cfg = '<?php' . "\n/**\n * Federated Login Plugin - configuration file\n"
             . " * This is an automatically generated file. Do not edit.\n"
             . " * Run for user: $user\n * Date: $date\n */\n\n";
        // serialize order
        if ($this->countLarge()) {
            $cfg .= "\$fa_order_large = array('" . implode("','", $this->order_large) . "');\n";
        }
        if ($this->countSmall()) {
            $cfg .= "\$fa_order_small = array('" . implode("','", $this->order_small) . "');\n";
        }
        $cfg .= "\n";
        // serialize providers
        foreach ($this->providers as $id => $provider) {
            $cfg .= $provider->getSerialized();
        }
        file_put_contents($this->cfgFile, $cfg);
    }

    /**
     * Changes the target configuration file the configuration is beeing saved to.
     *
     * @param string fname name of the new config file
     */
    public function setConfigFile($fname) {
       $this->cfgFile = $fname;
    }

    /**
     * Toggles the size of a provider button (moves a provider id between order arrays).
     *
     * @param string $id provider identifier
     * @return mixed provider button size code or false on failure
     */
    public function toggleSize($id) {
        if (in_array($id, $this->order_large)) {
            $index = array_search($id, $this->order_large);
            array_splice($this->order_large, $index, 1);
            array_push($this->order_small, $id);
            return PROV_SMALL;
        }
        if (in_array($id, $this->order_small)) {
            $index = array_search($id, $this->order_small);
            array_splice($this->order_small, $index, 1);
            array_push($this->order_large, $id);
            return PROV_LARGE;
        }
        return false;
    }

    /**
     * Null reference helper.
     */
    var $nullGuard = null;

    /**
     * Returns the reference to an order array that contains specified provider id.
     *
     * @param string $id provider identifier
     * @return arrayref reference to one of the order arrays or null
     */
    private function &_getOrderFor($id) {
        if (in_array($id, $this->order_small)) {
            return $this->order_small;
        }
        if (in_array($id, $this->order_large)) {
            return $this->order_large;
        }
        return $this->nullGuard;
    }

} /* fa_providerlist */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

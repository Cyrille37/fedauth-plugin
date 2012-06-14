<?php
/**
 * Federated Login for DokuWiki - provider class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

if(!defined('FA_PROVIDER_INC')) define('FA_PROVIDER_INC', __FILE__);

/**
 * The provider class stores all the configuration information related
 * to single authorization provider except its display size and order.
 *
 * The general purpose of this class is to make the provider data read-only
 * for all extenral access and provide the serialization usable with
 * the configuration file.
 *
 * @author Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_provider {

    var $id = '';
    var $data = array();

    public static function create($id, $data) {
        $instance = new self();
        $instance->loadProvider($id, $data);
        return $instance;
    }

    public static function createCustom($id, $name, $url, $large, $small) {
        $data = array(
            'name' => $name,
            'url' => $url,
            'img_large' => $large,
            'img_small' => $small,
            'disabled' => 0,
            'custom' => 1
            );

        return self::create($id, $data);
    }

    protected function loadProvider($id, $data) {
        $this->id = $id;
        $this->data = $data;
        $this->data['use_uname'] = strstr($data['url'], '{username}') ? 1 : 0;
    }

    public function enable() {
        $this->data['disabled'] = 0;
    }

    public function disable() {
        $this->data['disabled'] = 1;
    }

    public function toggle() {
        $this->data['disabled'] = $this->isEnabled() ? 1 : 0;
    }

    public function isEnabled() {
        return $this->data['disabled'] != 1;
    }

    public function isRemovable() {
        return $this->data['custom'] != 0;
    }

    public function getId() {
        return $this->id;
    }

    /**
     * Builds XHTML for selected image.
     */
    public function getImageXHTML($size=PROV_SMALL, $class='floatimg') {
        // select image source and size
        if ($size != PROV_SMALL) {
            $params = array('w' => 80, 'h' => 40);
            $src  = $this->data['img_large'];
        } else {
            $params = array('w' => 16, 'h' => 16);
            $src  = $this->data['img_small'];
        }
        // prepare image URL
        if (substr($src, 0, 1) === '@') {
            $src = str_replace('@DEF@', getBaseURL() .'lib/plugins/fedauth/images/', $src); // local image
        } else {
            // media link
            $mfn = mediaFN(':'.$src);
            if (@file_exists($mfn)) {
                $link = ml(':'.$src, $params);
                return $link;
            }
            // media not found, use _noimage
            $src = getBaseURL() .'lib/plugins/fedauth/images/' . ($size != PROV_SMALL ? 'large/' : '')  . '_noimage.png';
        }
        return '<img src="'.$src.'" class="'.$class.'" width="'.$params['w'].'" height="'.$params['h'].'" title="'.$this->getName().'" />';
    }

    public function getName() {
        return $this->data['name'];
    }

    /**
     * Returns the authorization service URL.
     */
    public function getURL() {
        return $this->data['url'];
    }

    /**
     * Returns the authorization provider data serialized using PHP syntax.
     * This is an utility method for saving the configuration to file.
     *
     * @param string $varname name of the variable used for the serialization
     * @return string serialized provider data
     */
    public function getSerialized($varname='fa_providers') {
        $str = '';
        foreach ($this->data as $key => $val) {
            if ($key == 'use_uname') continue;
            $str .= sprintf("\$%s['%s']['%s'] = %s;\n", $varname, $this->id, 
                $key, is_numeric($val) ? $val : "'" . $val . "'");
        }
        return $str;
    }

    public function hasUsername() {
        return $this->data['use_uname'] != 0;
    }

    /**
     * Sets some details, if this is a built-in provider loaded from default config.
     *
     * Note: Instead of hardlinks a @DEF@ string is included for dynamic generation
     *       of the image URL. This is enables support for multi-domain wikis and
     *       preserves image links in case wiki root was moved manually.
     *
     * @param string $imgpath local path to built-in provider images
     */
    public function setupDetails($imgpath) {
        if ($this->data['custom']) return;
        $large = 'large/' . $this->id . '.png';
        $small = $this->id . '.png';
        $this->data['img_large'] = '@DEF@' . (file_exists($imgpath . $large) ? $large  : 'large/_noimage.png');
        $this->data['img_small'] = '@DEF@' . (file_exists($imgpath . $small) ? $small  : '_noimage.png');
    }

} /* fa_provider */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

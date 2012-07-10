<?php
/**
 * Federated Login for DokuWiki - base command handler class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Federated login base command handler class. Handles all requests
 * from the plugin object  and renders the results of current action.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_base {

    /**
     * Plugin object owning this instance.
     */
    var $manager = null;

    /**
     * Locale data.
     */
    var $lang = array();

    /**
     * Identifier of a command to handle.
     */
    var $cmd = '';

    /**
     * Identifier of currently processed authorization service, if any.
     */
    var $provid = '';

    /**
     * Processing result.
     */
    var $success = false;

    /**
     * Creates the class instance bound to a DokuWiki plugin class instance and an authorization provider.
     *
     * @param objref $manager object reference to a plugin
     * @param string $cmd name of the command to handle
     * @param string $provid (optional) an authorization provider id
     */
    function __construct(&$manager, $cmd, $provid='') {
        $this->manager =& $manager;
        $this->lang =& $manager->lang;
        $this->cmd = $cmd;
        $this->provid = $provid;
    }

    /**
     * Performs an action depending on current command (and function).
     *
     * @return array processing status information
     */
    function process() {
        $method = 'process_' . $this->cmd;
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        $this->success = false;
        return array('msg' => $this->manager->getLang('unknowncmd').' <b>'.$this->cmd.'</b>', 'code' => -1);
    }

    /**
     * Outputs data for AJAX call.
     *
     * @return bool true on success
     */
    function ajax() {
        $method = 'handle_ajax_' . $this->cmd;
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return false;
    }

    /**
     * Outputs the XHTML as the command result.
     */
    function html() {
        $method = 'html_' . $this->cmd;
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    /**
     * Returns processing error array using localized string.
     *
     * @param string $msg localized string id
     * @param array $params replacements array
     * @return processing status array
     * @see fa_base::_status($msg, $code, $params)
     */
    function error($msg, $params=null) {
        return $this->_status($msg, -1, $params);
    }

    /**
     * Returns processing success array using localized string.
     *
     * @param string $msg localized string id
     * @param array $params replacements array
     * @return processing status array
     * @see fa_base::_status($msg, $code, $params)
     */
    function success($msg, $params=null) {
        return $this->_status($msg, 1, $params);
    }

    /**
     * Returns processing warning array using localized string.
     *
     * @param string $msg localized string id
     * @param array $params replacements array
     * @return processing status array
     * @see fa_base::_status($msg, $code, $params)
     */
    function warn($msg, $params=null) {
        return $this->_status($msg, 2, $params);
    }

    /**
     * Returns processing info array using localized string.
     *
     * @param string $msg localized string id
     * @param array $params replacements array
     * @return processing status array
     * @see fa_base::_status($msg, $code, $params)
     */
    function info($msg, $params=null) {
        return $this->_status($msg, 0, $params);
    }

    /**
     * Creates processing status array using localized string
     * with optional replacement of additional parameters.
     *
     * @param string $msg localized string id
     * @param int $code status code: -1 error, 0 info, 1 success, 2 warning
     * @param array $params replacements array; keys are chunks to replace,
     *                      values are the replacacements
     * @return processing status array
     */
    function _status($msg, $code, $params) {
        $msg = $this->manager->getLang($msg);
        if (is_array($params)) {
            foreach($params as $key => $val) {
                $msg = str_replace($key, $val, $msg);
            }
        }
        return array('msg' => $msg, 'code' => $code);
    }

    /**
     * Displays Dokuwiki message using message data array.
     */
    function msg($data) {
        msg($data['msg'], $data['code']);
    }

} /* fa_base */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

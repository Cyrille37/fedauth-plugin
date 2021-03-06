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
if (!defined('FEDAUTH_PLUGIN')) define ('FEDAUTH_PLUGIN', DOKU_PLUGIN . 'fedauth/');
if (!defined('ADMIN_CMD_SCOPE')) define('ADMIN_CMD_SCOPE', 'adm');

require_once(DOKU_PLUGIN . 'admin.php');
require_once(FEDAUTH_PLUGIN . 'common.php');
require_once(FEDAUTH_PLUGIN . "classes/fa_base.class.php");
require_once(FEDAUTH_PLUGIN . "classes/adm/fa_manage.adm.class.php");

class admin_plugin_fedauth extends DokuWiki_Admin_Plugin {

    var $provid = '';
    var $cmd = '';
    var $handler = null;

    var $providers = null;

    var $functions = array('details','movedn','moveup','remove','uselarge','usesmall'); // require a provider id
    var $commands = array('add','manage','restore','toggle'); // don't require a provider id

    var $msg = '';
    var $err = '';

    /**
     * Returns the plugin meta information.
     */
    function getInfo() {
        return array(
            'author' => 'Aoi Karasu',
            'email'  => 'aoikarasu@gmail.com',
            'date'   => '2012-06-09',
            'name'   => 'Federated Login Plugin',
            'desc'   => 'Functions to configure authorization providers',
            'url'    => 'http://www.dokuwiki.org/plugin:fedauth',
        );
    }

    /**
     * Restricts the access to admins only.
     */
    function forAdminOnly() {
        return $this->getConf('adminsonly');
    }

    /**
     * Handles configuration page requests.
     */
    function handle() {
        // enable direct access to language strings
        $this->setupLocale();

        $fa = $_REQUEST['fa'];
        if (is_array($fa)) {
            $this->cmd = key($fa);
            $this->provid = is_array($fa[$this->cmd]) ? key($fa[$this->cmd]) : null;
        } else {
            $this->cmd = $fa;
            $this->provid = null;
        }

        // load helper plugin
        if ($helper =& plugin_load('helper', 'fedauth')) {
            $this->providers = $helper->getProviders();
        }

        // verify $_REQUEST vars
        if (in_array($this->cmd, $this->commands)) {
            $this->provid = '';
        } else if (!in_array($this->cmd, $this->functions) || !$this->providers->get($this->provid)) {
            $this->cmd = 'manage';
            $this->provid = '';
        }

        if(($this->cmd != 'manage' || $this->provid != '') && !checkSecurityToken()){
            $this->cmd = 'manage';
            $this->provid = '';
        }

        // load command class and process the command
        $this->handler =& load_handler_class($this, $this->cmd, ADMIN_CMD_SCOPE, $this->provid, 'manage');
        $result = $this->handler->process();
        if (is_array($result) && empty($_REQUEST['ajax'])) {
            msg($result['msg'], $result['code']);
        }
    }

    /**
     * Outputs data for AJAX call.
     */
    function ajax() {
        if (!$this->getConf('useajax')) return;

        // enable direct access to language strings
        $this->setupLocale();

        if ($this->handler === NULL) $this->handler = new fa_manage($this, $this->cmd, $this->provid);

        if (!$this->handler->ajax()) {
            print "Unrecognized ajax call: " . $this->cmd;
        }
    }

    /**
     * Outputs configuration page.
     */
    function html() {
        // enable direct access to language strings
        $this->setupLocale();

        if ($this->handler === NULL) $this->handler = new fa_manage($this, $this->provid);

        ptln('<div id="fedauth__manager">');
        if ($this->getConf('useajax')) {
            print plugin_script_block('admin');
        }
        $this->handler->html();
        ptln('</div><!-- #fedauth__manager -->');
    }

} /* admin_plugin_federate */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

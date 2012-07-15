<?php
/**
 * Federated Login for DokuWiki - manage providers class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Authorization providers management class. Handles all requests from
 * the admin module and renders the results of current action.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_manage extends fa_base {

    /**
     * Source form/ajax call identifier used with provider lists.
     */
    var $listSource = null;

    /**
     * Creates the class instance bound with the admin plugin and an authorization provider.
     *
     * @param objref $manager object reference to the admin plugin
     * @param string $cmd name of the command to handle
     * @param string $provid (optional) an authorization provider id
     */
    function __construct(&$manager, $cmd, $provid='') {
        parent::__construct(&$manager, $cmd, $provid);
    }

    /**
     * Verifies whether the request contains valid list type.
     * Used by derived classes to ensure that the request is
     * related to any of the provider lists.
     *
     * @return bool true, if list type is valid
     */
    function isValidListSource() {
        if (is_null($this->listSource)) {
             $source = strtolower($_REQUEST['source']);
             $this->listSource = (($source == 'large') || ($source == 'small')) ? $source : null;
        }
        return !is_null($this->listSource);
    }

    /**
     * Returns a reference to an associative array cotaining the providers
     * selected by current list source. Use isValidListSource() method first,
     * to ensure that calling this method won't fail.
     *
     * @return arrayref reference to a providers array
     */
    function &getProvidersByListSource() {
        return $this->manager->providers->{'get'.ucfirst($this->listSource)}();
    }

    /**
     * Saves the authorization providers cofiguration file.
     */
    function saveConfig() {
        $this->manager->providers->save($_SERVER['REMOTE_USER']);

        // NOTE: Expiring the dokuwiki caches seem to be necessary,
        //       because Opera and IE9 seem not to refresh the management page
        //       on reload for changes made using different browser/session.
        //       Guess what, Firefox and Chrome don't need this!

        // TODO: Find a less intrusive solution.

        global $config_cascade;

        // touching local.php expires wiki page, JS and CSS caches
        @touch(reset($config_cascade['main']['local']));
    }

    /**
     * Performs an action depending on current command (and function).
     *
     * @return string the processing result message
     */
    function process() {
        $method = 'process_' . $this->cmd;
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        $this->success = true;
        return '';
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
     * Handles AJAX call to display provider details.
     *
     * @return bool true on success
     */
    function handle_ajax_details() {
        print $this->_html_details($this->provid);
        return true;
    }

    /**
     * Outputs the XHTML of the management page.
     */
    function html() {
        $out = $this->manager->plugin_locale_xhtml('admproviders');
        $out = str_replace('@LARGELIST@', $this->html_providers_form(true), $out);
        $out = str_replace('@SMALLLIST@', $this->html_providers_form(), $out);
        $out = str_replace('@ADDPROVIDER@', $this->html_add_provider_form(), $out);
        $out = str_replace('@RESTOREDEFAULTS@', $this->html_restore_defaults_form(), $out);
        print $out;
    }

    /**
     * Renders the form with provider buttons table and action buttons.
     *
     * @param bool $large true for providers configured to use large buttons or else small
     * @return string rendered provider list form
     */
    function html_providers_form($large=false) {
        global $ID;

        $listtype = $large ? 'large' : 'small';

        $out = '<div id="fa__' . $listtype . '" class="sprovs"><form action="'.wl($ID).'" method="post">'
             . '  <fieldset class="hidden">'
             . '    <input type="hidden" name="do" value="admin" />'
             . '    <input type="hidden" name="page" value="fedauth" />'
             . '    <input type="hidden" name="source" value="' . $listtype . '" />'
             . formSecurityToken(false)
             . '  </fieldset>'
             . '  <div id="axwrap__' . $listtype . '">'
             . $this->html_providers_list($this->manager->providers->{'get'.ucfirst($listtype)}(), $large)
             . '  </div>'
             . '  <fieldset class="buttons">'
             . '    <input type="submit" class="button" name="fa[toggle]" value="' . $this->lang['btn_toggle'] . '" />'
             . '  </fieldset>'
             . '</form></div>';
        return $out;
    }

    /**
     * Renders the form for adding a custom authorization provider.
     */
    function html_add_provider_form() {
        return '<b>WARNING:</b> This version does not support adding custom providers.';
    }

    /**
     * Renders the form for restoring the default settings.
     */
    function html_restore_defaults_form() {
        global $ID;

        $out = '<div id="fa__restore"><form action="'.wl($ID).'" method="post">'
             . '  <fieldset class="hidden">'
             . '    <input type="hidden" name="do" value="admin" />'
             . '    <input type="hidden" name="page" value="fedauth" />'
             . '    <input type="hidden" name="source" value="restore" />'
             . formSecurityToken(false)
             . '  </fieldset>'
             . '  <fieldset class="buttons">'
             . '    <input type="submit" class="button" name="fa[restore]" value="' . $this->lang['btn_restore'] . '" />'
             . '  </fieldset>'
             . '</form></div>';
        return $out;
    }

    function html_providers_list(&$source, $large=false) {
        if (!is_array($source)) return '';

        $out = '';
        $even = false;

        foreach ($source as $id => $pro) {

            $protected = false;

            $class = array();
            if (!$pro->isEnabled()) $class[] = 'disabled';
            if ($even = !$even) $class[] = 'even';
            $class = count($class) ? ' class="'.join('', $class).'"' : '';

            $checked = $pro->isEnabled() ? ' checked="checked"' : '';
            $check_disabled = ($protected) ? ' disabled="disabled"' : '';

            // this might need ajax disable check
            $details = ($this->cmd == 'details' && $this->provid == $id) ? $this->_html_details($id, true) : '';

            $out .= '    <fieldset'.$class.'>'
                 .  '      <legend>'.$id.'</legend>'
                 .  '      <input type="checkbox" class="enable" name="toggle[]" id="dw__p_'.$id.'" value="'.$id.'"'.$checked.$check_disabled.' />'
                 .  '      <div class="legend"><label for="dw__p_'.$id.'">'.$pro->getImageXHTML().$pro->getName().'</label>'.(!$pro->isEnabled()?'<span class="disabled">('.$this->lang['disabled'].')</span>':'')
                 .  '      <div id="fa__det_'.$id.'">'.$details.'</div></div>'
                 .  $this->_html_button($id, 'details', false, 6)
                 .  $this->_html_button($id, 'moveup', $this->manager->providers->isFirst($id), 6)
                 .  $this->_html_button($id, 'movedn', $this->manager->providers->isLast($id), 6)
                 .  $this->_html_button($id, $large ? 'usesmall' : 'uselarge', false, 6)
                 .  $this->_html_button($id, 'remove', !$pro->isRemovable(), 6)
                 .  '    </fieldset>';
            //$out .= "<h2>$id</h2><pre>" . print_r($pro, true) . "</pre>";
        }
        return $out;
    }

    function _html_button($provid, $btn, $disabled=false, $indent=0, $class='') {
        $disabled = ($disabled) ? 'disabled="disabled"' : '';
        return str_repeat(' ', $indent)
            . '<input type="submit" class="button '.$class.'" '.$disabled.' name="fa['.$btn.']['.$provid.']" title="'.$this->lang['btn_'.$btn].'" value="'.$this->lang['btn_'.$btn].'" />';
    }

    function _html_details($provid, $forcevisible=false) {
        $fv = $forcevisible ? ' style="display: block;"' : '';
        $pro =& $this->manager->providers->get($provid);
        return '<div class="details"'.$fv.'><b>ID:</b> '.$provid.'<br/><b>'.$this->lang['serviceurl'].':</b> '.$pro->getURL().'<br/>'
               .$pro->getImageXHTML(PROV_LARGE,'imgdetails').' 80x40 '.$pro->getImageXHTML(PROV_SMALL,'imgdetails').' 16x16</div>';
    }

    function _json_buttoninfo($cmd) {
        return sprintf('{"success":1,"name":"fa[%s][%s]","title":"%s","value":"%s"}',
            $cmd, $this->provid, $this->lang['btn_'.$cmd], $this->lang['btn_'.$cmd]);
    }

} /* fa_manage */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

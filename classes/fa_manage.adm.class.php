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
class fa_manage {

    /**
     * Admin plugin object owning this instance.
     */
    var $manager = null;

    /**
     * Locale data.
     */
    var $lang = array();

    /**
     * Identifier of currently processed authorization provider, if any.
     */
    var $provid = '';

    /**
     * Creates the class instance bound with the admin plugin and an authorization provider.
     *
     * @param objref $manager object reference to the admin plugin
     * @param string $provid (optional) an authorization provider id
     */
    function __construct(&$manager, $provid='') {
        $this->manager =& $manager;
        $this->provid = $provid;
        $this->lang =& $manager->lang;
    }

    /**
     * Performs the action depending on current command (and function).
     *
     * @return string the processing result message
     */
    function process() {
        return '';
    }

    /**
     * Outputs the XHTML of the management page.
     */
    function html() {
        $out = $this->manager->plugin_locale_xhtml('admproviders');
        $out = str_replace('@LARGELIST@', $this->html_large_providers_form(), $out);
        $out = str_replace('@SMALLLIST@', $this->html_small_providers_form(), $out);
        $out = str_replace('@ADDPROVIDER@', $this->html_add_provider_form(), $out);
        print $out;
    }

    /**
     * Renders the form with large provider buttons table and action buttons.
     */
    function html_large_providers_form() {
        global $ID;

        $out = '<div class="lprovs"><form action="'.wl($ID).'" method="post">'
             . '  <fieldset class="hidden">'
             . '    <input type="hidden" name="do"     value="admin" />'
             . '    <input type="hidden" name="page"   value="fedauth" />'
             . formSecurityToken(false)
             . '  </fieldset>'
             . $this->_html_providers_list($this->manager->providers->getLarge(), true)
             . '  <fieldset class="buttons">'
             . '    <input type="submit" class="button" name="fa[enable]" value="' . $this->lang['btn_toggle'] . '" />'
             . '  </fieldset>'
             . '</form></div>';
        return $out;
    }

    /**
     * Renders the form with small provider buttons table and action buttons.
     */
    function html_small_providers_form() {
        global $ID;

        $out = '<div class="sprovs"><form action="'.wl($ID).'" method="post">'
             . '  <fieldset class="hidden">'
             . '    <input type="hidden" name="do"     value="admin" />'
             . '    <input type="hidden" name="page"   value="fedauth" />'
             . formSecurityToken(false)
             . '  </fieldset>'
             . $this->_html_providers_list($this->manager->providers->getSmall())
             . '  <fieldset class="buttons">'
             . '    <input type="submit" class="button" name="fa[enable]" value="' . $this->lang['btn_toggle'] . '" />'
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

    function _html_providers_list(&$source, $large=false) {
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

            $out .= '    <fieldset'.$class.'>'
                 .  '      <legend>'.$id.'</legend>'
                 .  '      <input type="checkbox" class="enable" name="enabled[]" id="dw__p_'.$id.'" value="'.$id.'"'.$checked.$check_disabled.' />'
                 .  '      <div class="legend"><label for="dw__p_'.$id.'">'.$pro->getImageXHTML().$pro->getName().'</label>'/*.' <span class="provid">(ID: '.$id.')</span>'*/
                 .  '      <div id="fa__det_'.$id.'"></div></div>'
                 .  $this->_html_button($id, 'mvup', $this->manager->providers->isFirst($id), 6)
                 .  $this->_html_button($id, 'mvdn', $this->manager->providers->isLast($id), 6)
                 .  $this->_html_button($id, $large ? 'mksmall' : 'mklarge', false, 6)
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

} /* fa_manage */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

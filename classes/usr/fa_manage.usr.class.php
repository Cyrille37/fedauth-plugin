<?php
/**
 * Federated Login for DokuWiki - manage user claimed identities class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Class enabling users to manage their claimed identities.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_manage extends fa_login {

    /**
     * Creates the class instance bound to a plugin instance and an authentication provider.
     *
     * @param objref $manager object reference to the admin plugin
     * @param string $cmd name of the command to handle
     * @param string $provid (optional) an authentication provider id
     */
    function __construct(&$manager, $cmd, $provid='') {
        parent::__construct($manager, $cmd, $provid);
    }

    /**
     * No pocessing for the time being.
     * This method is intended to suppress the 'unknown command' message only.
     */
    function process_manage() {
        $this->success = true;
        return null;
    }

    /**
     * Renders the user identities management page.
     */
    function html_manage() {
//        print "<pre>".print_r($_REQUEST, true)."</pre>";

        $out = $this->manager->locale_xhtml('mylogins');
        $out = str_replace('@MYLOGINSLIST@', $this->html_mylogins_form(), $out);
        print $out;

        $this->html_login_service_from();
    }

    /**
     * Renders the user's identities associated with his wiki account.
     */
    function html_mylogins_form() {
        global $ID;

        $out = '<div id="fa__mylogins"><form action="'.wl($ID, 'do=fedauth', true, '&').'" method="post">'
             . '  <fieldset class="hidden">'
//             . '    <input type="hidden" name="do" value="fedauth" />'
             . formSecurityToken(false)
             . '  </fieldset>'
             . '  <div id="axwrap__mylogins">'
             . $this->html_mylogins_list()
             . '  </div>'
             . '</form></div>';
        return $out;
    }

    function html_mylogins_list() {
//        global $conf;

        $even = false;
        // TODO: sanity checks
        $store = $this->getUserStore();
        $data =& $store->getUserData();
//        $out = "<pre>".print_r($data, true)."</pre>";
        $current = isset($_SESSION[DOKU_COOKIE]['fedauth']) ? $_SESSION[DOKU_COOKIE]['fedauth']['prid'] : '';

        foreach ($data as $entry) {
            $id = $entry['id'];
            $pro = $this->manager->providers->get($id);
            $class = $even = !$even ? ' class="even"' : '';
            $using = $id == $current ? '<span class="inuse">('.$this->manager->getLang('inuse').')</span>' : '';

            $out .= '    <fieldset'.$class.'>'
                 .  '      <legend>'.$id.'</legend>'
                 .  '      <div class="legend"><label for="dw__p_'.$id.'">'.$pro->getImageXHTML().$pro->getName().'</label>'
                 .  $using
                 .  '      <div id="fa__det_'.$id.'">'.$entry['ident'].'</div></div>'
                 .  '      <span class="lastused">'.dformat($entry['last']).'</span>'
                 .  $this->_html_button($id, base64_encode($entry['ident']),'remove', $id == $current, 6)
                 .  '    </fieldset>';
        }

        return $out;
    }

    function _html_button($provid, $aid, $btn, $disabled=false, $indent=0, $class='') {
        $disabled = ($disabled) ? 'disabled="disabled"' : '';
        return str_repeat(' ', $indent)
            . '<input type="submit" class="button '.$class.'" '.$disabled.' name="fa['.$btn.']['.$provid.']['.$aid.']" title="'.$this->lang['btn_'.$btn].'" value="'.$this->lang['btn_'.$btn].'" />';
    }

} /* fa_manage */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

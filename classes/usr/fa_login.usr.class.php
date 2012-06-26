<?php
/**
 * Federated Login for DokuWiki - handles login form class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Class responsible for rendering the fededated login form.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_login extends fa_base {

    /**
     * Creates the class instance bound to a plugin instance and an authorization provider.
     *
     * @param objref $manager object reference to the admin plugin
     * @param string $cmd name of the command to handle
     * @param string $provid (optional) an authorization provider id
     */
    function __construct(&$manager, $cmd, $provid='') {
        parent::__construct(&$manager, $cmd, $provid);
    }

    /**
     * No pocessing when displaying federated login form.
     * This method is intended to suppress the 'unknown command' message only.
     */
    function process_login() {
        $this->success = true;
        return null;
    }

    function html_login() {
        $this->html_login_service_from();
    }

    function getLoginText() {
        return empty($_SERVER['REMOTE_USER']) ? $this->lang['clicktologin'] : $this->lang['clicktoadd'];
    }

    function getService($pro) {
        // TODO: change to getType() once it is implemented in the fa_provider class
        $id = "fa_" . "openid" /*$pro->getType()*/;
        $hfile = FEDAUTH_PLUGIN . "classes/svc/$id.svc.class.php";
        require_once($hfile);
        $class = $id . "_svc";
        return new $class($pro);
    }

    function html_login_service_from($return=false) {
        global $ID, $conf, $auth;

        $user = $_SERVER['REMOTE_USER'];

        $out = '<div id="fa__authform">'
             . '<p>'.$this->lang['gotlogin'].' '.$this->getLoginText().'</p>'
             . '<form action="'.wl($ID, 'do=fedauth').'" method="post">'
             . '  <fieldset class="hidden">'
             . '    <input type="hidden" name="do" value="fedauth" />'
             . formSecurityToken(false)
             . '  </fieldset>'
             . '  <div id="axwrap__large"><fieldset>'
             . $this->html_providers_list($this->manager->providers->getLarge(), true)
             . '  </fieldset></div>'
             . '  <div id="axwrap__small"><fieldset>'
             . $this->html_providers_list($this->manager->providers->getSmall(), false)
             . '  </fieldset></div>'
             . $this->html_service_signin()
             . $this->html_general_openid()
             . '</form></div>';

        if ($return) return $out;
        print $out;
        return true;
    }

    function html_providers_list(&$source, $large=false) {
        global $ID, $do, $sectok;

        if (!is_array($source)) return '';

        $out = '';

        foreach ($source as $id => $pro) {
            if (!$pro->isEnabled()) continue;

            $btn = $this->cmd;
            $class = $large ? 'btnplarge' : 'btnpsmall';
            $mode  = $large ? PROV_LARGE : PROV_SMALL;
            $class .= $this->provid == $id ? ' selected' : '';

            if ($pro->hasUsername()) {
                $act = $do;
                $cmd = 'select';
            }
            else {
                $act = 'fedauth';
                $cmd = 'signin';
            }

            $out .= '<a href="'.wl($ID, array('do'=> $act, 'sectok' => $sectok)).'&fa['.$cmd.']['.$id.']">'
                 .  $pro->getImageXHTML($mode, $class) . '</a>';
        }
        return $out;
    }

    function html_service_signin() {
        if (!($pro = $this->manager->providers->get($this->provid))) {
            return '';
        }
        if (!$pro->isEnabled() || !$pro->hasUsername()) {
            return '';
        }
        $out = '  <div id="axwrap__enterlogin"><fieldset>'
             . '    <p>'.str_replace('@PROVID@', '<b>'.$pro->getName().'</b>', $this->lang['enterlogin']).'</p>'
             . '<input type="text" name="fa_signinname" class="edit">'
             . '<input type="submit" class="button" name="fa[signin]['.$this->provid.']" value="' . $this->lang['btn_signin'] . '" />'
             . '  </fieldset></div>';

        return $out;
    }

    function html_general_openid() {
        if (!($pro = $this->manager->providers->get('openid'))) {
            return '';
        }
        if (!$pro->isEnabled()) {
            return '';
        }

        $out = '  <div id="axwrap__openid"><fieldset>'
             . '    <p>'.$this->lang['manualopenid'].':</p>'
             . $pro->getImageXHTML(PROV_SMALL, 'manualoid')
             . '<input type="text" name="fa_openidurl" class="edit" value="http://">'
             . '<input type="submit" class="button" name="fa[signin][openid]" value="' . $this->lang['btn_login'] . '" />'
             . '  </fieldset></div>';

        return $out;
    }

} /* fa_login */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */


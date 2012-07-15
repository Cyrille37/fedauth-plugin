<?php
/**
 * Federated Login for DokuWiki - register local account class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

/**
 * Class responsible for registering a local account once user is successfully
 * authenticated but his identity is not associated with any existing local account.
 *
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */
class fa_register extends fa_login {

    /**
     * Creates the class instance bound to a plugin instance and an authentication provider.
     *
     * @param objref $manager object reference to the admin plugin
     * @param string $cmd name of the command to handle
     * @param string $provid (optional) an authentication provider id
     */
    function __construct(&$manager, $cmd, $provid='') {
        parent::__construct(&$manager, $cmd, $provid);
    }

    function process_register() {
        global $conf, $ID;

        $this->success = true;
        if (!($data = $_SESSION[DOKU_COOKIE]['fedauth']['tmpr'])) {
            // cannot register without fedauth temp data, but suggest standard registration
            send_redirect(wl($ID, 'do=register', true, '&'));
        }
        if (!empty($_POST)) {
            // TODO: ensure remote identity is still not associated
            //       with any account; if it is, just login using it
            // attempt account creation
            $_POST['save'] = true; // investigate, why register() requires it
            $conf['autopasswd'] = true; // yup, force auto-password
            if (register()) {
                // account created, save all fedauth data and save the cookie
                @session_start(); // restore session to update it
                $store =& $this->getUserStore();
                $this->manager->cookie->set($_POST['login'], $data['prid'], $data['svcd'], false /*$sticky*/);
                $store->addUserDataEntry($data['prid'], $data['ident']);
                $this->msg($this->success('loginadded', array('@PROVID@' => $data['prnm'])));
                unset($_SESSION[DOKU_COOKIE]['fedauth']['tmpr']);
                send_redirect($this->restoreLocation());
            }
            // no custom message on fail, dokiwiki's register() prints error messages
        }
        return array('msg'=>('<pre>'.print_r($_SESSION[DOKU_COOKIE]['fedauth']['tmpr'], true).'</pre>'),'code'=>2);
    }

    function html_register() {
        global $conf, $ID, $INPUT, $lang;

        $data = $_SESSION[DOKU_COOKIE]['fedauth']['tmpr'];
        $prep = $this->_prepareFormData($data);

        $form = new Doku_Form(array('id' => 'dw__register'));
        $form->startFieldset($lang['btn_register']);
        $form->addHidden('do', 'fedauth');
        $form->addHidden('fa[register]', '');
        $form->addHidden('provid', $data['prid']);

        $form->addElement(form_makeTextField('login', $prep['login'], $lang['user'], '', 'block', array('size'=>'50')));
        $form->addElement(form_makeTextField('fullname', $prep['fullname'], $lang['fullname'], '', 'block', array('size'=>'50')));
        $form->addElement(form_makeTextField('email', $prep['email'], $lang['email'], '', 'block', array('size'=>'50')));
        $form->addElement(form_makeButton('submit', '', $lang['btn_register']));
        $form->endFieldset();

        $out = $this->manager->locale_xhtml('register');
        $out = str_replace('@PROVID@', $data['prnm'], $out);
        $out = str_replace('@REGFORM@', '<div class="centeralign">'.NL.$form->getForm().'</div>'.NL, $out);

        echo $out;
    }

    function _prepareFormData($data) {
        if (!empty($_POST)) {
            return array('login' => $_POST['login'], 'email' => $_POST['email'], 'fullname' => $_POST['fullname']);
        }
        return array('login' => $data['nickname'], 'email' => $data['email'], 'fullname' => $data['fullname']);
    }

} /* fa_register */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

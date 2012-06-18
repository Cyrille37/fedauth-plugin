/**
 * Federated Login for DokuWiki - admin javascript
 *
 * @copyright  2012 Aoi Karasu
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

var fa_manager = {
    /**
     *
     */
    init : function() {
        if (jQuery('#fedauth__manager').length === 0) {
            return;
        }
        /* Bind action for each details button click */
        jQuery('#fedauth__manager input[name^="fa[details]"]').prop('type','button').click(function() {
            var pid = jQuery(this).attr('name')
            pid = pid.substring(pid.lastIndexOf('[')+1, pid.lastIndexOf(']'));
            if (jQuery(this).attr('collapse') == 'yes') {
                jQuery(this).removeAttr('collapse');
                jQuery('#fa__det_'+pid+' .details').toggle();
                return false;
            }
            fa_manager.loadinfo(this, pid);
            return false;
        });
        /* Bind action for each toggle button click */
        jQuery('#fedauth__manager input[name="fa[toggle]"]').prop('type','button').click(function() {
             var did = jQuery(this).closest('div[id^="fa__"]').attr('id');
             did = did.substring(did.lastIndexOf('_')+1);
             fa_manager.toggleproviders(this, did);
             return false;
        });
    },

    /**
     * Loads the current authorization service info.
     */
    loadinfo: function (sender, target) {
        jQuery('#fa__det_'+target)
            .html('<img src="'+DOKU_BASE+'lib/images/throbber.gif" alt="..." style="margin: 10px;"/>')
            .load(
                FEDAUTH_BASE + 'ajax.php',
                jQuery('#fa__large form').serialize()+'&ajax=details&fa[details]['+target+']',
                function() { 
                    jQuery(sender).attr('collapse', 'yes');
                    jQuery('#fa__det_'+target+' .details').toggle(); 
                }
            );
        return false;
    },

    /**
     * Toggles the enabled state of providers from the target list.
     */
    toggleproviders: function (sender, target) {
        var data = decodeURIComponent(jQuery('#fa__'+target+' form').serialize())+'&ajax=toggle&fa[toggle]';
        jQuery('#axwrap__'+target)
            .html('<img src="'+DOKU_BASE+'lib/images/throbber.gif" alt="..." style="margin: 10px;"/>')
            .load(
                FEDAUTH_BASE + 'ajax.php',
                data,
                function() {
/*                    jQuery(sender).attr('collapse', 'yes');
                    jQuery('#fa__det_'+target+' .details').toggle();*/
                }

            );
        return false;
    }
};

jQuery(fa_manager.init);

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

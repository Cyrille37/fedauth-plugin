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

        jQuery('#fedauth__manager input[name^="fa[details]"]').each(function() {
            var pid = jQuery(this).attr('name')
            pid = pid.substring(pid.lastIndexOf('[')+1, pid.lastIndexOf(']'));
            jQuery(this).prop('type','button').click(function() {
                if (jQuery(this).attr('collapse') == 'yes') {
                    jQuery(this).removeAttr('collapse');
                    jQuery('#fa__det_'+pid+' .details').toggle();
                    return false;
                }
                fa_manager.loadinfo(this, pid);
                return false;
            });
        });
    },

    /**
     * Loads the current authorization service info
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
    }
};

jQuery(fa_manager.init);

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

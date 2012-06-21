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
     * Binds actions to the form buttons.
     */
    init : function() {
        if (jQuery('#fedauth__manager').length === 0) {
            return;
        }
        /* Bind action for each details button click */
        fa_manager.bindaction('details', function(target, id) {
            if (jQuery(target).attr('collapse') == 'yes') {
                jQuery(target).removeAttr('collapse');
                jQuery('#fa__det_'+id+' .details').toggle();
                return;
            }
            fa_manager.loadinfo(target, id);
        });

        /* Bind action for each move and transfer buttons click */
        fa_manager.bindaction('movedn', fa_manager.providerslidedown);
        fa_manager.bindaction('moveup', fa_manager.providerslideup);
        fa_manager.bindaction('uselarge', fa_manager.provideruselarge);
        fa_manager.bindaction('usesmall', fa_manager.providerusesmall);

        /* Bind action for each toggle button click */
        fa_manager.bindaction('toggle', function(target) {
             var did = jQuery(target).closest('div[id^="fa__"]').attr('id');
             did = did.substring(did.lastIndexOf('_')+1);
             fa_manager.toggleproviders(target, did);
        });
    },

    /**
     * Binds a click event handler to all buttons matching action name.
     *
     * @param string actname the action name
     * @param function handler the click event handler function
     * @param string id (optional) associated identifier
     */
    bindaction: function (actname, handler, id) {
        jQuery('#fedauth__manager input[name^="fa['+actname+']"]')
            .prop('type','button')
            .unbind('click')
            .click(function() {
                var handle = typeof id == 'undefined' ? fa_manager._stripprovid(this) : id;
                handler(this, handle);
                return false;
            });
    },

    /**
     * Loads the current authorization service info.
     */
    loadinfo: function (sender, target) {
        jQuery('#fa__det_'+target)
            .html(fa_manager._throbber(10))
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
            .html(fa_manager._throbber())
            .load(
                FEDAUTH_BASE + 'ajax.php',
                data,
                fa_manager.init
            );
        return false;
    },

    /**
     * Moves up a provider item in the list with the slide effect.
     */
    providerslideup: function (sender, target) {
        fa_manager._prepareitem(sender);
        jQuery
            .ajax(FEDAUTH_BASE + 'ajax.php',
                { data: jQuery('#fa__large form').serialize()+'&ajax=moveup&fa[moveup]['+target+']' })
            .success(function(data) {
                var ret = jQuery.parseJSON(data);
                if (!ret['success']) return;
                var cur = jQuery(sender).parent();
                var prv = cur.prev();
                prv.before(cur);
                fa_manager._switchclass(prv);
                fa_manager._switchclass(cur);
                fa_manager._easeitem(sender);
            })
            .error(function() { alert("AJAX error!"); fa_manager._easeitem(sender); });
    },

    /**
     * Moves down a provider item in the list with the slide effect.
     */
    providerslidedown: function (sender, target) {
        fa_manager._prepareitem(sender);
        jQuery
            .ajax(FEDAUTH_BASE + 'ajax.php',
                { data: jQuery('#fa__large form').serialize()+'&ajax=movedn&fa[movedn]['+target+']' })
            .success(function(data) {
                var ret = jQuery.parseJSON(data);
                if (!ret['success']) return;
                var cur = jQuery(sender).parent();
                var prv = cur.next();
                prv.after(cur);
                fa_manager._switchclass(prv);
                fa_manager._switchclass(cur);
                fa_manager._easeitem(sender);
            })
            .error(function() { alert("AJAX error!"); fa_manager._easeitem(sender); });
    },

    /**
     * Transfers a provider item to the large buttons list with scroll and fadein effect.
     */
    provideruselarge: function (sender, target) {
        fa_manager._prepareitem(sender);
        jQuery
            .ajax(FEDAUTH_BASE + 'ajax.php',
                { data: jQuery('#fa__large form').serialize()+'&ajax=uselarge&fa[uselarge]['+target+']' })
            .success(function(data) {
                var ret = jQuery.parseJSON(data);
                if (!ret['success']) return;
                var cur = jQuery(sender).parent();
                cur.slideUp(function() {
                    var owner = jQuery('#axwrap__large').last();
                    owner.append(cur);
                    fa_manager._updateitem(sender, target, ret, fa_manager.providerusesmall);
                    cur.slideDown();
                    fa_manager._easeitem(sender);
                    fa_manager._easeitem(jQuery('#axwrap__small fieldset input[type="button"]').get(0));
                });
            })
            .error(function() { alert("AJAX error!"); fa_manager._easeitem(sender); });
    },

    /**
     * Transfers a provider item to the small buttons list with scroll and fadein effect.
     */
    providerusesmall: function (sender, target) {
        fa_manager._prepareitem(sender);
        jQuery
            .ajax(FEDAUTH_BASE + 'ajax.php',
                { data: jQuery('#fa__large form').serialize()+'&ajax=usesmall&fa[usesmall]['+target+']' })
            .success(function(data) {
                var ret = jQuery.parseJSON(data);
                if (!ret['success']) return;
                var cur = jQuery(sender).parent();
                cur.slideUp(function() {
                    var owner = jQuery('#axwrap__small').last();
                    owner.append(cur);
                    fa_manager._updateitem(sender, target, ret, fa_manager.provideruselarge);
                    cur.slideDown();
                    fa_manager._easeitem(sender);
                    fa_manager._easeitem(jQuery('#axwrap__large fieldset input[type="button"]').get(0));
                });
            })
            .error(function() { alert("AJAX error!"); fa_manager._easeitem(sender); });
    },

    /**
     * Removes the throbber image and enables action buttons for all items in the sender's scope,
     * and disables the move up and move down buttons for first and last item respectively.
     */
    _easeitem: function (sender) {
       jQuery(sender).siblings('img').remove();
       jQuery(sender).parent().parent().find('input[type="button"]').removeAttr('disabled');
       /* ensure move up/dn buttons are disabled for first/last item */
       var fsets = jQuery(sender).parent().parent().children('fieldset');
       fsets.first().children('input[name^="fa[moveup"]').attr('disabled','disabled');
       fsets.last().children('input[name^="fa[movedn"]').attr('disabled','disabled');
    },

    /**
     * Displays the throbber image and disables action buttons for all items in the sender's scope.
     */
    _prepareitem: function (sender) {
       jQuery(sender).siblings('div[class="legend"]').after(fa_manager._throbber());
       jQuery(sender).parent().parent().find('input[type="button"]').attr('disabled','disabled');
    },

    /**
     * Switches the position-in-list CSS class for a provider item to the opposite one.
     */
    _switchclass: function(src) {
       if (src.hasClass('even')) {
           src.removeClass('even');
       } else if (src.hasClass('disabledeven')) {
           src.removeClass('disabledeven').addClass('disabled');
       } else if (src.hasClass('disabled')) {
           src.removeClass('disabled').addClass('disabledeven');
       } else {
           src.addClass('even');
       }
    },

    /**
     * Updates the transfer button and the position-in-list CSS class for a provider item to its sibling opposite.
     *
     * @param object src buttom DOM object
     * @param string target the provider identifier
     * @param object data object with new button XHTML attributes
     * @param function clickhandler new button click hander function
     */
    _updateitem: function (src, target, data, clickhandler) {
        jQuery(src).attr('name', data.name).attr('title', data.title).attr('value', data.value)
                   .unbind('click').click(function() { clickhandler(this, target); return false; });
        fa_manager._updateeven('small');
        fa_manager._updateeven('large');
    },

    _updateeven: function(listname) {
        var even = true;
        jQuery('#axwrap__'+listname).children('fieldset').each(function() {
           var it = jQuery(this);
           var disabled = it.is('.disabled, .disabledeven') ? 'disabled' : '';
           even = !even;
           it.removeClass('even disabled disabledeven').addClass(disabled+(even ? 'even' : ''));
        });
    },

    /**
     * Strips the provider id from a DOM element's name attribute.
     */
    _stripprovid: function (elem) {
        var pid = jQuery(elem).attr('name');
        return pid.substring(pid.lastIndexOf('[')+1, pid.lastIndexOf(']'));
    },

    /**
     * Builds the throbber image XHTML tag.
     */
    _throbber: function (margin, valign) {
        margin = typeof margin !== 'undefined' ? new String(margin) : '0';
        valign = typeof valign !== 'undefined' ? valign : 'middle';
        return '<img src="'+DOKU_BASE+'lib/images/throbber.gif" alt="..." style="margin: '+margin+'px; vertical-align: '+valign+';"/>';
    }
};

jQuery(fa_manager.init);

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

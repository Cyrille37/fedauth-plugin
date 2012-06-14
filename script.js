/**
 * Federated Login for DokuWiki - utility javascript
 *
 * @copyright  2012 Aoi Karasu
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

var FEDAUTH_BASE = DOKU_BASE + 'lib/plugins/fedauth/';

/* DOKUWIKI:include_once js/aoihelper.js */

/* consider removing - start */
if (typeof jQuery == 'undefined') {
    if ((jAoi.getQueryVar('do') == 'admin') && (jAoi.getQueryVar('page') == 'fedauth')) {
          alert('The script on this page requires jQuery, which has not been detected. Some functionality will be broken.');
    }
}
/* consider removing - end */

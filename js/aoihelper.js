/**
 * Federated Login for DokuWiki - AoiHelper javascript class
 *
 * @copyright  2012 Aoi Karasu
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @link       http://www.dokuwiki.org/plugin:fedauth
 * @author     Aoi Karasu <aoikarasu@gmail.com>
 */

function jAoiHelperClass() {
    this.qvars = new Array();
    this.init = function(self) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            self.qvars[pair[0]] = pair[1];
        }
    };
    this.getQueryVar = function(qvar) {
        return this.qvars[qvar];
    };
    this.init(this);
}
var jAoi = new jAoiHelperClass();

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

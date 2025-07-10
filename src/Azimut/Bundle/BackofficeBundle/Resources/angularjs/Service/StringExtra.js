/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-01-15 10:11:09
 *
 ******************************************************************************
 *
 * global utility functions for Strings
 *
 */


'use strict';

angular.module('azimutBackoffice.service')

.factory('StringExtra', [
'$log',
function($log) {

    $log = $log.getInstance('StringExtra');

    var factory = this;

    factory.replaceAccent = function(str) {
        var search = 'ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ø,Ø,Å,Á,À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ú,Ù,Û,Ü,Ÿ,Ç,Æ,Œ'.split(',');
        var replace = 'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,o,O,A,A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,U,U,U,U,Y,C,AE,OE'.split(',');

        for(var i = 0; i < search.length; i++){
            str = str.replace(new RegExp(search[i], 'g'), replace[i]);
        }

        return str;
    }

    return {
        replaceAccent: factory.replaceAccent,

        // Removes accents, replace non alphanumeric characters by "-" and compress successive "-"
        slugify: function (str) {
            str = factory.replaceAccent(str.toLowerCase());
            return str.replace(/[^a-z0-9]+/g, '-');
        }
    }
}]);

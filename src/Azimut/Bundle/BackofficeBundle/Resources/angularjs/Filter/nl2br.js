/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-17 11:57:15
 *
 ******************************************************************************
 *
 * Transforms new lines in a text to <br /> tags
 *
 */

'use strict';

angular.module('azimutBackoffice.filter')

.filter('nl2br', ['$sce', function($sce) {
    return function(text) {
        return text ? $sce.trustAsHtml(text.replace(/\n/g, '<br />')) : '';
    };
}]);

/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-22 16:34:21
 ******************************************************************************
 *
 * Widget proxy for external applications in iframe (without angular js)
 *
 */

'use strict';

$( document ).ready(function() {
    if (undefined == window.azimut) {
        window.azimut = {};
    }

    window.azimut.mediacenterWidgetProxyId = null;

    // visual fixes
    $('div[az-form-entity-js] > div').addClass('input-group');
    $('div[az-form-entity-js] > div > span').addClass('input-group-btn');
    $('div[az-form-entity-js] > img').hide();
    $('.form-help-error').hide();

    // plug widget through iframe
    window.addEventListener("message", function(evt) {
        if (undefined == evt.data[0] || undefined == evt.data[0].mediaType || null == window.azimut.mediacenterWidgetProxyId) {
            return
        };

        // Only handles file type mediacenter proxy
        if ('file' != window.azimut.mediacenterWidgetProxyType) {
            return;
        }

        // insert value in form
        $('#' + window.azimut.mediacenterWidgetProxyId).val(evt.data[0].id);
        $('#' + window.azimut.mediacenterWidgetProxyId + '-label').val(evt.data[0].name);

        // handle image thumb
        if (undefined != evt.data[0].thumb) {
            $('#' + window.azimut.mediacenterWidgetProxyId + '-img').attr('src', Routing.generate('azimut_mediacenter_backoffice_file_proxy_thumb',{ filepath: evt.data[0].thumb, size: 'xs' }));
            $('#' + window.azimut.mediacenterWidgetProxyId + '-img').show();
        }
        else {
            $('#' + window.azimut.mediacenterWidgetProxyId + '-img').hide();
        }

    }, false);

    $('div[az-form-entity-js] .entity-js-browse-button').on('click', function() {
        // set active widget proxy
        window.azimut.mediacenterWidgetProxyId = this.id.substr(0, this.id.lastIndexOf('-button'));
        window.azimut.mediacenterWidgetProxyType = 'file';

        // call widget through iframe
        parent.postMessage('showMediacenterWidget', '*');
    });

    $('div[az-form-entity-js] .entity-js-remove-button').on('click', function() {
        var baseId = this.id.substr(0, this.id.lastIndexOf('-remove-button'));

        // erase value in form
        $('#' + baseId).val(null);
        $('#' + baseId + '-label').val(null);

        // handle image thumb
        $('#' + baseId + '-img').attr('src', 'data:image/gif;base64,R0lGODlhAQABAAAAACwAAAAAAQABAAA=');
        $('#' + baseId + '-img').hide();
    });
});

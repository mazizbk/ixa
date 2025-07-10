/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-09-02 12:17:12
 *
 ******************************************************************************
 *
 * FormsBag object
 * To be used for form binding with backend
 * The object contains form data, errors, definitions, etc
 *
 * Usage:
 *
 * var forms = new FormsBag();
 *
 * forms.data.myformid = {
 *     myfield1: 'value1',
 *     myfield2: 'value2'
 * };
 *
 * forms.params.myformid = {
 *     submitActive: true,
 *     submitLabel: 'my submit label',
 *     submitAction: function() {
 *         // action when form submited
 *     }
 * };

 * forms.widgets.mywidgetid = {
 *     onShow: function() {
 *         //action triggered on widget button click
 *     },
 *     containerId: 'azimutDemoWidget', // id of the widget root DOM element
 *     callbackName: 'azimutMediacenterChooseMediaDeclinations', //callback function name for widget
 *     params: {
 *         // widget parameters
 *         statePrefix: 'backoffice.demo.prefix' // prefix to widget routes
 *     }
 * }
 *
 */

'use strict';

angular.module('azimutBackoffice.service')

.factory('FormsBag', function () {
    return function() {
        this.data = {}, // data binded to forms
        this.params = {}, // parameters of forms, like submit function and submit label
        this.widgets = {}, // parameters for entity widgets (like mediacenter, cms)
        this.errors = {}, // form error description object
        this.infos = {}, // live validation info on form
        this.values = {}, // contains affectables values for compounds checkboxes
        this.files = {} // upload files data
    }
});

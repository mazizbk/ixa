/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-28 15:35:13
 */

'use strict';

angular.module('azimutFrontoffice.controller')

.controller('FrontofficeZoneDetailController', [
'$log', '$scope', '$rootScope', 'FormsBag', 'FrontofficeSiteFactory', 'ArrayExtra', '$state', '$stateParams', 'NotificationService', '$timeout', '$window', 'FrontofficeZoneAttachment',
function($log, $scope, $rootScope, FormsBag, FrontofficeSiteFactory, ArrayExtra, $state, $stateParams, NotificationService, $timeout, $window, FrontofficeZoneAttachment) {
    $log = $log.getInstance('FrontofficeZoneDetailController');

    $scope.$parent.showContentView = true;
    $scope.mainContentLoading();

    $scope.formZoneTemplateUrl = Routing.generate('azimut_frontoffice_backoffice_jsview_zone_form');
    $scope.formZoneCmsFileAttachmentUrl = Routing.generate('azimut_frontoffice_backoffice_jsview_zone_cms_file_attachment');

    $scope.zonePanelParams = {
        collapsed: true
    }

    var zoneId = $stateParams.zoneId;

    $scope.zone = null;

    $scope.formLocale = $rootScope.locale;

    $scope.forms = new FormsBag();

    $scope.nextZoneAttachment = null;

    $scope.breadcrumb = {
        elements: []
    };

    $scope.baseCmsWidgetUrl = $state.current.name;

    $scope.$on('$viewContentLoaded', function() {
        if(-1 != $state.current.name.indexOf('.widget_edit_file')) {
            $('#azimutCmsEditWidget').show();
            $scope.baseCmsWidgetUrl = $state.$current.parent.name;
        }

        if(-1 != $state.current.name.indexOf('.widget_select_file')) {
            $('#azimutCmsSelectWidget').show();
            $scope.baseCmsWidgetUrl = $state.$current.parent.name;
        }
    });

    //Fetch the complete version of the zone, with all fields
    FrontofficeSiteFactory.getZone(zoneId, 'all').then(function(response) {
        var zone = response.data.zone;

        $scope.zone = zone;
        $scope.waitingCmsFilesBufferCount = response.data.waitingCmsFilesBufferCount;

        /*if (!zone.isAllowDeleteAttachments && zone.attachments.length == 1 && zone.acceptedAttachmentTypes.length == 1) {
            $scope.$parent.showContentView = false;
            $state.go('backoffice.frontoffice.zone_detail.freecontent', {zoneId: zone.id, file_id: zone.attachments[0].cmsFile.id, cmsFileType: zone.attachments[0].cmsFile.cmsFileType});
        }*/

        // we don't use the real Zone object because we need raw data to be binded into the form
        $scope.forms.data.zone = zone.toFormData();

        $scope.forms.params.zone = {
            submitActive: true,
            submitLabel: Translator.trans('update'),
            cancelLabel: Translator.trans('cancel'),
            submitAction: function() {
                return $scope.saveZone($scope.forms.data.zone);
            },
            cancelAction: function() {
                $state.reload();
            },
            confirmDirtyDataStateChangeMessage: Translator.trans('zone.has.not.been.saved.are.you.sure.you.want.to.continue')
        }

        // set a virtual ending attachment to handle drag'n drop display order setting to max
        if(zone.attachments) {
            var maxDisplayOrder = 1;
            for(var i in $scope.zone.attachments) {
                if($scope.zone.attachments[i].displayOrder > maxDisplayOrder) maxDisplayOrder = $scope.zone.attachments[i].displayOrder;
            }
            $scope.nextZoneAttachment = new FrontofficeZoneAttachment({
                displayOrder: maxDisplayOrder+1
            });
        }

        $scope.forms.data.zone_cms_file_attachment = {
            zone: zoneId
        };

        $scope.forms.widgets.zone_cms_file_attachment_cmsFile = {
            // action that will be triggered on widget button click
            onShow: function() {
                // activate cms widget
                if(-1 == $state.current.name.indexOf('.widget_select_')) $state.go($scope.baseCmsWidgetUrl + '.widget_select_file', $stateParams);
            },
            // DOM container to show on button click
            containerId: 'azimutCmsSelectWidget',
            // callback function name for widget
            callbackName: 'azimutCmsChooseCmsFiles',
            onSet: function(value) {
                //automaticaly create the new attachment when a cms file has been chosen from widget
                $scope.addZoneCmsFileAttachment($scope.forms.data.zone_cms_file_attachment);
            },
            params: {
                acceptedTypes: zone.acceptedAttachmentTypes
            },
            buttonLabel: Translator.trans('add.file'),
            hideLabel: true
        }

        var breadcrumbCurrentFile = angular.copy(zone);

        // find zone name translation
        var translatedZoneName = Translator.trans('zone_names.' + zone.name);
        if (translatedZoneName != 'zone_names.' + zone.name) {
            breadcrumbCurrentFile.name = translatedZoneName;
        }

        do {
            $scope.breadcrumb.elements.unshift(breadcrumbCurrentFile);
        } while(breadcrumbCurrentFile = breadcrumbCurrentFile.parentElement);

        $scope.mainContentLoaded();

    }, function(response) {
        $log.error('Get zone error', response);
        NotificationService.addCriticalError(Translator.trans('notification.error.zone.with.id.%id%.get', { 'id' : $stateParams.id }), response);
        $scope.$parent.showContentView = false;
    });

    var updateZoneCmsFileAttachmentDisplayOrder = function(zoneCmsFileAttachment, newDisplayOrder) {
        if (null == newDisplayOrder) {
            throw 'Trying to set null display order on zoneCmsFileAttachment';
        }
        // update order (only section between old and new displayOrder)
        var startDisplayOrder = zoneCmsFileAttachment.displayOrder;
        var endDisplayOrder = newDisplayOrder;

        // displayOrder has been increased
        if(endDisplayOrder > startDisplayOrder) {
            for(var i=startDisplayOrder+1;i<=endDisplayOrder;i++) {

                var zoneCmsFileAttachmentPropagate = ArrayExtra.findFirstInArray($scope.zone.attachments,{'displayOrder': i});

                // if the next display order does not exist then we reach the limit, we will insert element here
                if(undefined == zoneCmsFileAttachmentPropagate) {
                    newDisplayOrder = i-1;
                    break;
                }
                else zoneCmsFileAttachmentPropagate.displayOrder --;
            }
        }
        // displayOrder has been decreased
        else {
            for(var i=startDisplayOrder-1;i>=endDisplayOrder;i--) {

                var zoneCmsFileAttachmentPropagate = ArrayExtra.findFirstInArray($scope.zone.attachments,{'displayOrder': i});

                // if the next display order does not exist then we reach the limit, we will insert element here
                if(undefined == zoneCmsFileAttachmentPropagate) {
                    newDisplayOrder = i+1;
                    break;
                }
                else zoneCmsFileAttachmentPropagate.displayOrder ++;
            }
        }

        zoneCmsFileAttachment.displayOrder = newDisplayOrder;
    }

    var moveZoneCmsFileAttachment = function(zoneCmsFileAttachment, newDisplayOrder) {
        updateZoneCmsFileAttachmentDisplayOrder(zoneCmsFileAttachment, newDisplayOrder);

        FrontofficeSiteFactory.updateZoneCmsFileAttachment(zoneCmsFileAttachment).then(function(response) {
            $log.info('Update zoneCmsFileAttachment success', response);

        }, function(response) {
            $log.error('Update zoneCmsFileAttachment failed', response);
            NotificationService.addError(Translator.trans('notification.error.zone.cmsfile.attachment.move'), response);
            // TODO : revert action on view
        });
    }

    $scope.moveTopZoneCmsFileAttachment = function(zoneCmsFileAttachment) {
        moveZoneCmsFileAttachment(zoneCmsFileAttachment, 1);
    }

    $scope.moveDownZoneCmsFileAttachment = function(zoneCmsFileAttachment) {
        moveZoneCmsFileAttachment(zoneCmsFileAttachment, zoneCmsFileAttachment.displayOrder+1);
    }

    $scope.moveUpZoneCmsFileAttachment = function(zoneCmsFileAttachment) {
        moveZoneCmsFileAttachment(zoneCmsFileAttachment, zoneCmsFileAttachment.displayOrder-1);
    }

    $scope.moveBottomZoneCmsFileAttachment = function(zoneCmsFileAttachment) {
        var maxDisplayOrder = 1;
        for(var i in $scope.zone.attachments) {
            if($scope.zone.attachments[i].displayOrder > maxDisplayOrder) maxDisplayOrder = $scope.zone.attachments[i].displayOrder;
        }
        moveZoneCmsFileAttachment(zoneCmsFileAttachment, maxDisplayOrder);
    }

    $scope.saveZone = function(zoneData) {
        return FrontofficeSiteFactory.updateZone(zoneData).then(function(response) {
            // remove dirty state on form
            if (undefined != $scope.forms.params.zone.formController) {
                $scope.forms.params.zone.formController.$setPristine();
            }
            // as we don't leave the form page but just hide the panel, we need to update the form data
            var zone = response.data.zone;
            var formController = $scope.forms.params.zone.formController;
            $scope.forms.data.zone = zone.toFormData();
            $scope.forms.params.zone.formController = formController;

            NotificationService.addSuccess(Translator.trans('notification.success.zone.update'));

            $scope.zonePanelParams.collapsed = true;

            // clear form error messages
            delete $scope.forms.errors.zone;
        }, function(response) {
            $log.error('Update zone failed', response);
            NotificationService.addError(Translator.trans('notification.error.zone.update'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.zone = response.data.errors;
            }
        });
    }

    $scope.addZoneCmsFileAttachment = function(zoneCmsFileAttachment) {
        FrontofficeSiteFactory.createZoneCmsFileAttachment(zoneCmsFileAttachment).then(function(response) {

            // HTTP no content = the attachment already exists
            if(204 === response.status) {
                NotificationService.addWarning(Translator.trans('notification.warning.zone.cmsfile.attachment.create.already.exists'));
            }
            else {
                var zoneCmsFileAttachment = response.data.zoneCmsFileAttachment;

                if(zoneCmsFileAttachment.displayOrder != $scope.zone.attachments.length) {
                    // apply the higher display order and store the real order (to be able to update all the other attachments)
                    var zoneCmsFileAttachmentDisplayOrder = zoneCmsFileAttachment.displayOrder;
                    zoneCmsFileAttachment.displayOrder = $scope.zone.attachments.length;

                    // reapply the real order and reorganise other attachment
                    updateZoneCmsFileAttachmentDisplayOrder(zoneCmsFileAttachment, zoneCmsFileAttachmentDisplayOrder);

                }

                NotificationService.addSuccess(Translator.trans('notification.success.zone.cmsfile.attachment.create'));
            }

            // clear form error messages
            delete $scope.forms.errors.zoneCmsFileAttachment;
        }, function(response) {
            // if form validation failed and root error exists, display the message (ex: max attachments reached)
            if (undefined != response.data.errors) {
                NotificationService.addError(response.data.errors.errors[0]);
                return;
            }

            $log.error('Create zoneCmsFileAttachment failed', response);
            NotificationService.addError(Translator.trans('notification.error.zone.cmsfile.attachment.create'), response);

            // display form error messages
            if(undefined != response.data.errors) {
                $scope.forms.errors.zoneCmsFileAttachment = response.data.errors;
            }
        });
    }

    $scope.removeZoneCmsFileAttachment = function(zoneCmsFileAttachment) {
        var zoneCmsFileAttachmentToRemove = zoneCmsFileAttachment;
        FrontofficeSiteFactory.deleteZoneCmsFileAttachment(zoneCmsFileAttachment).then(function(response) {
            var emptyDisplayOrder = zoneCmsFileAttachmentToRemove.displayOrder;

            $scope.zone.attachments.splice($scope.zone.attachments.indexOf(zoneCmsFileAttachmentToRemove), 1);

            for(var i=0; i<$scope.zone.attachments.length; i++) {
                if($scope.zone.attachments[i].displayOrder > emptyDisplayOrder) {
                    $scope.zone.attachments[i].displayOrder --;
                }
            }

            NotificationService.addSuccess(Translator.trans('notification.success.zone.cmsfile.attachment.delete'));
        }, function(response) {
            $log.error('Create zoneCmsFileAttachment failed', response);
            NotificationService.addError(Translator.trans('notification.error.zone.cmsfile.attachment.delete', response));
        });
    }

    $scope.$on('dropEvent', function(evt, dragged, dropped, droppedFiles) {
        $log.log('Zone attachment dropEvent');

        //check supported dropped type
        if(!(dropped instanceof FrontofficeZoneAttachment)) {
            $log.log("This drop event (zone attachment dropEvent) is not concerned by dropped element");
            return;
        }

        if(!dragged) return;

        //check supported dragged type
        if(!(dragged instanceof FrontofficeZoneAttachment)) {
            var draggedElementType = dragged.constructor.name?dragged.constructor.name:'unknown type';
            $log.warn('The element type "'+draggedElementType+'" cannot be dropped on element type "'+dropped.constructor.name+'"');
            return;
        }

        //prevent elements from being dropped on themselves
        if(dragged == dropped) {
            $log.log("Element dropped on itself. Aborting.");
            return;
        }

        var newDisplayOrder = dropped.displayOrder;

        if(dragged.displayOrder < newDisplayOrder) newDisplayOrder--;

        moveZoneCmsFileAttachment(dragged, newDisplayOrder);
    });

    $scope.openZonePreview = function(zone) {
        var pageFullSlug = zone.pageFullSlug;

        if (!angular.isString(pageFullSlug)) {
            if (undefined == pageFullSlug[$rootScope.locale]) {
                for (locale in pageFullSlug) {
                    pageFullSlug = pageFullSlug[locale];
                }
            }
            else {
                pageFullSlug = pageFullSlug[$rootScope.locale];
            }
        }

        $window.open(zone.siteUri + Routing.generate('azimut_frontoffice', {'path': pageFullSlug}), 'azimut.pagepreview','menubar=no,status=no,scrollbars=yes');
    }

    $scope.editCmsFile = function(cmsFile) {
        // activate cms edit widget
        $state.go($scope.baseCmsWidgetUrl + '.widget_edit_file', { 'file_id': cmsFile.id });
        $('#azimutCmsEditWidget').show();
    }
}]);

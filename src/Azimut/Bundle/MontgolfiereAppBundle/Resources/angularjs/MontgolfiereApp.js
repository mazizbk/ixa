(function(){
    'use strict';

    angular.module('montgolfiereApp', ['azimutBackoffice'])
        .run(['BackofficeExternalAppFactory', function(BackofficeExternalAppFactory) {
            BackofficeExternalAppFactory.addAppDefinition({
                menuTitle: Translator.trans('montgolfiere.backoffice.clients.app_name'),
                menuIcon: 'glyphicon-pro glyphicon-pro-building',
                menuDisplayOrder: 20,
                shortName: 'clients',
                url: Routing.generate('azimut_montgolfiere_app_backoffice_clients_homepage', null, true)
            });
            BackofficeExternalAppFactory.addAppDefinition({
                menuTitle: Translator.trans('montgolfiere.backoffice.questions.app_name'),
                menuIcon: 'glyphicon glyphicon-question-sign',
                menuDisplayOrder: 21,
                shortName: 'questions',
                url: Routing.generate('azimut_montgolfiere_app_backoffice_questions_homepage', null, true)
            });
            BackofficeExternalAppFactory.addAppDefinition({
                menuTitle: Translator.trans('montgolfiere.backoffice.campaigns.app_name'),
                menuIcon: 'glyphicon-pro glyphicon-pro-notes-2',
                menuDisplayOrder: 22,
                shortName: 'campaigns',
                url: Routing.generate('azimut_montgolfiere_app_backoffice_campaigns_homepage', null, true)
            });
            BackofficeExternalAppFactory.addAppDefinition({
                menuTitle: Translator.trans('montgolfiere.backoffice.settings.app_name'),
                menuIcon: 'glyphicon-pro glyphicon-pro-wrench',
                menuDisplayOrder: 22,
                shortName: 'settings',
                url: Routing.generate('azimut_montgolfiere_app_backoffice_settings_index', null, true)
            });
            BackofficeExternalAppFactory.addAppDefinition({
                menuTitle: Translator.trans('montgolfiere.backoffice.consultants.app_name'),
                menuIcon: 'glyphicon-pro glyphicon-pro-old-man',
                menuDisplayOrder: 22,
                shortName: 'consultants',
                url: Routing.generate('azimut_montgolfiere_app_backoffice_consultants_homepage', null, true)
            });
        }])
    ;
    angular.module('azimutBackoffice').requires.push('montgolfiereApp');

})();

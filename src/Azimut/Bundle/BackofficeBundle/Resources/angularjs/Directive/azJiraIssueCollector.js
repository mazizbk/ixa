/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-01-25 10:37:44
 *
 ******************************************************************************
 *
 * Triggers Jira issue collector form in modal
 * Adds $log service traces in description field
 *
 *
 * Usage :
 *
 *     <a az-jira-issue-collector>Report bug</a>
 *
 */

 'use strict';

 angular.module('azimutBackoffice.directive')

 .directive('azJiraIssueCollector', [
 '$http', '$log',
 function($http, $log) {
     return {
         link: function(scope, element, attrs) {

            if (!window.ATL_JQ_PAGE_PROPS) {
                $.ajax({
                    url: scope.jira_issue_collector_url,
                    type: "get",
                    cache: true,
                    dataType: "script"
                });
            }

            window.ATL_JQ_PAGE_PROPS =  {
                "triggerFunction": function(showCollectorDialog) {
                    element.click(function(e) {
                        e.preventDefault();
                        showCollectorDialog();
                    });
                },
                // initial default field values
                fieldValues: {
                    description : "" // TODO: inject traces : $log.history.join("\n")
                }
            };

        }
     };
 }]);

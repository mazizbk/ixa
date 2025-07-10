/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-08-27 10:35:40
 *
 ******************************************************************************
 *
 * This service takes an angular expression representing a nested model and instanciate
 * each child object, except the last one
 * Input : an angular expression and its scope
 *
 */

'use strict';

angular.module('azimutBackoffice.service')

.factory('NestedModelInstanciator', [
'$log', '$parse',
function($log, $parse) {
    $log = $log.getInstance('NestedModelInstanciator');

    return {
        instanciate: function (modelExpression, $scope) {
            // Initialize subobjects in formdata
            // if the model associated to the field is not instantiated
            if(undefined == $scope.$eval(modelExpression)) {
                // Convert array notation to object notation
                modelExpression = modelExpression.replace("['",".");
                modelExpression = modelExpression.replace("']['",".");
                modelExpression = modelExpression.replace("']","");

                var t_modelExpression = modelExpression.split('.');

                var parentModelExpression = '';
                var parentObject = $scope;

                // Loop over all parent object of the model and check if they exist
                for(var i=0;i<t_modelExpression.length-1;i++) {
                    var subobjectModelExpression = '';
                    if('' != parentModelExpression) subobjectModelExpression = parentModelExpression+'.';
                    subobjectModelExpression += t_modelExpression[i];

                    // Initialize object if it does not exist
                    if(undefined == $scope.$eval(subobjectModelExpression)) {
                        var model = $parse(subobjectModelExpression);
                        model.assign($scope, {});
                    }

                    if('' != parentModelExpression) parentModelExpression += '.';
                    parentModelExpression += t_modelExpression[i];
                    parentObject = $scope.$eval(parentModelExpression);
                }
            }

            return null;
        }
    }
}]);

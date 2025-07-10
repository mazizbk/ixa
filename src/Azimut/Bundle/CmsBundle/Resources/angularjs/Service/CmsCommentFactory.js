/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-18 10:49:14
 */

'use strict';

angular.module('azimutCms.service')

.factory('CmsCommentFactory', [
'$log', '$http', '$q', 'CmsFile',
function($log, $http, $q, CmsFile) {
    $log = $log.getInstance('CmsCommentFactory');

    var factory = this;
    factory.urlPrefix = 'azimut_cms_api_';

    return {
        getComments: function(cmsFileId) {
            var routeParams = null;
            if (null != cmsFileId) {
                routeParams = { cmsFileId: cmsFileId };
            }
            return $http.get(Routing.generate(factory.urlPrefix + 'get_comments', routeParams)).then(function(response) {
                for (var i = 0; i < response.data.comments.length; i++) {
                    response.data.comments[i].cmsFile = new CmsFile(response.data.comments[i].cmsFile);
                }
                return response;
            });
        },

        createComment: function(commentData) {
            return $http.post(Routing.generate(factory.urlPrefix + 'post_comments'), { comment: commentData }).then(function(response) {
                response.data.comment.cmsFile = new CmsFile(response.data.comment.cmsFile);
                return response;
            });
        },

        getComment: function(id, cmsFileId) {
            var routeParams = { id: id };
            if (null != cmsFileId) {
                routeParams.cmsFileId = cmsFileId;
            }
            return $http.get(Routing.generate(factory.urlPrefix + 'get_comment', routeParams)).then(function(response) {
                response.data.comment.cmsFile = new CmsFile(response.data.comment.cmsFile);
                return response;
            });
        },

        updateComment: function(commentData) {
            var commentApiData = angular.copy(commentData);

            var commentId = commentApiData.id;
            delete commentApiData.id;
            delete commentApiData.createdAt;
            delete commentApiData.cmsFile;

            return $http.put(Routing.generate(factory.urlPrefix + 'put_comment', { id: commentId }), { comment: commentApiData }).then(function(response) {
                response.data.comment.cmsFile = new CmsFile(response.data.comment.cmsFile);
                return response;
            });
        },

        deleteComment: function(comment) {
            return $http.delete(Routing.generate(factory.urlPrefix + 'delete_comment', { id: comment.id }));
        },

        validateComment: function(comment) {
            var commentPatch = {
                isVisible: true
            };

            return $http({
                method: 'PATCH',
                url: Routing.generate(factory.urlPrefix + 'patch_comment', { id: comment.id }),
                data: { comment: commentPatch }
            }).then(function(response) {
                response.data.comment.cmsFile = new CmsFile(response.data.comment.cmsFile);
                return response;
            });
        },

        unvalidateComment: function(comment) {
            var commentPatch = {
                isVisible: false
            };

            return $http({
                method: 'PATCH',
                url: Routing.generate(factory.urlPrefix + 'patch_comment', { id: comment.id }),
                data: { comment: commentPatch }
            }).then(function(response) {
                response.data.comment.cmsFile = new CmsFile(response.data.comment.cmsFile);
                return response;
            });
        }
    }
}]);

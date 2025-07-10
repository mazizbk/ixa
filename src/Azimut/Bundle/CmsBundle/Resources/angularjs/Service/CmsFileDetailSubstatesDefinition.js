/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-27 15:57:36
 */

'use strict';
angular.module('azimutCms.service')

.constant('CmsFileDetailSubstatesDefinition', [
    {
        name: 'comment_list',
        url: '/comments',
        templateUrl: Routing.generate('azimut_cms_backoffice_jsview_comment_list'),
        controller: 'CmsCommentListController'
    },
    {
        name: 'comment_new',
        url: '/new_comment',
        templateUrl: Routing.generate('azimut_cms_backoffice_jsview_new_comment'),
        controller: 'CmsNewCommentController'
    },
    {
        name: 'comment_detail',
        url: '/comments/:comment_id',
        templateUrl: Routing.generate('azimut_cms_backoffice_jsview_comment_detail'),
        controller: 'CmsCommentDetailController'
    },
    {
        name: 'product_item_list',
        url: '/product_items',
        templateUrl: Routing.generate('azimut_cms_backoffice_jsview_product_item_list'),
        controller: 'CmsProductItemListController'
    },
    {
        name: 'product_item_new',
        url: '/new_product_item',
        templateUrl: Routing.generate('azimut_cms_backoffice_jsview_new_product_item'),
        controller: 'CmsNewProductItemController'
    },
    {
        name: 'product_item_detail',
        url: '/product_items/:product_item_id',
        templateUrl: Routing.generate('azimut_cms_backoffice_jsview_product_item_detail'),
        controller: 'CmsProductItemDetailController'
    }
]);

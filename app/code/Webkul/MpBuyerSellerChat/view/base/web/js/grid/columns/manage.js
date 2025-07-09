define([
    './column',
    'jquery', ,
    'Webkul_MpBuyerSellerChat/js/emoji.min'
], function (Column, $, emojify) {
    'use strict';
    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },
        getFieldHandler: function (row) {
            return false;
        }
    });
});

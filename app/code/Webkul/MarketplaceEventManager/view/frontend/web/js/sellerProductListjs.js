/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceEventManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
    'mage/calendar'
], function ($, $t, alert) {
    'use strict';
    $.widget('mage.sellerProductListjs', {
        _create: function () {
            var self = this;
            $(".wk-mp-body").dateRange({
                'dateFormat':'mm/dd/yy',
                'from': {
                    'id': 'special-from-date'
                },
                'to': {
                    'id': 'special-to-date'
                }
            });

            $('body').delegate('.mp-edit','click',function () {
                var dicision=confirm($t(" Are you sure you want to edit this event ? "));
                if (dicision === true) {
                    var $url=$(this).attr('data-url');
                    window.location = $url;
                }
            });
            $('#mass-delete-butn').click(function (e) {
                var flag = 0;
                $('.mpcheckbox').each(function () {
                    if (this.checked === true) {
                        flag =1;
                    }
                });
                if (flag === 0) {
                    alert({content : $t(' No Checkbox is checked ')});
                    return false;
                } else {
                    var dicisionapp=confirm($t(" Are you sure you want to delete these events ? "));
                    if (dicisionapp === true) {
                        $('#form-customer-product-new').submit();
                    } else {
                        return false;
                    }
                }
            });

            $('#mpselecctall').click(function (event) {
                if (this.checked) {
                    $('.mpcheckbox').each(function () {
                        this.checked = true;
                    });
                } else {
                    $('.mpcheckbox').each(function () {
                        this.checked = false;
                    });
                }
            });

            $('.mp-delete').click(function () {
                var dicisionapp=confirm($t(" Are you sure you want to delete this event ? "));
                if (dicisionapp === true) {
                    var $url=$(this).attr('data-url');
                    window.location = $url;
                }
            });
        }
    });
    return $.mage.sellerProductListjs;
});

/**
 * Webkul_MarketplaceEventManager MarketplaceEventManager.setAttribute
 * @category  Webkul
 * @package   Webkul_MarketplaceEventManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 
 /*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";
    $.widget('ticketbooking.setattr', {
        _create: function () {
            var attribute = this.options;
            $('body').trigger('contentUpdated');
            var length = $('input[name="product[event_start_date]"]').length;
            if (attribute.productType != 'etickets') {
                $('div[data-index="ticket-booking"]').remove();
            } 
            $('div[data-index="is_mp_event"]').remove();
            $('[data-index=ticket-booking]').on('click', function () { 
                setTimeout(() => {
                    $('div[data-index="is_mp_event"]').remove();                    
                }, 500);
            });
            if (!attribute.productId) {
                attribute.eventFrom = '';
                attribute.eventTo = '';
            }
            var isset = false;
            $('[data-index=ticket-booking]').on('click', function () {
                if (!isset) {
                    $('input[name="product[event_start_date]"]').parents('.admin__field').hide();
                    $('input[name="product[event_end_date]"]').parents('.admin__field').hide();
                    $('input[name="product[event_start_date_tmp]"]').attr('value', attribute.eventFrom).css('width','200px');
                    $('input[name="product[event_end_date_tmp]"]').attr('value', attribute.eventTo).css('width','200px');
                    $('.ui-datepicker-trigger').click(function () {
                        $(this).prev('input').focus();
                    });     
                    if ($('input[name="product[event_end_date_tmp]"]').length) {
                        isset = true;       
                    }                    
                }

            });
            if (length > 0) {
                if (!isset) {
                    $('input[name="product[event_start_date]"]').parents('.admin__field').hide();
                    $('input[name="product[event_end_date]"]').parents('.admin__field').hide();

                    $('input[name="product[event_start_date_tmp]"]').attr('value', attribute.eventFrom).css('width','200px');
                    $('input[name="product[event_end_date_tmp]"]').attr('value', attribute.eventTo).css('width','200px');
                    if ($('input[name="product[event_end_date_tmp]"]').length) {
                        isset = true;       
                    }

                    $('.ui-datepicker-trigger').click(function () {
                        $(this).prev('input').focus();
                    });
                }
            } else {
                $('div[data-index="ticket-booking"]').on('click', function (event) {
                    if (!isset) {
                        $('input[name="product[event_start_date]"]').parents('.admin__field').hide();
                        $('input[name="product[event_end_date]"]').parents('.admin__field').hide();

                        $('input[name="product[event_start_date_tmp]"]').attr('value', attribute.eventFrom).css('width','200px');
                        $('input[name="product[event_end_date_tmp]"]').attr('value', attribute.eventTo).css('width','200px');
                        if ($('input[name="product[event_end_date_tmp]"]').length) {
                            isset = true;       
                        }

                        $('.ui-datepicker-trigger').click(function () {
                            $(this).prev('input').focus();
                        });
                        $(this).off(event);
                    }
                });
            }
        }
    });
    return $.ticketbooking.setattr;
});
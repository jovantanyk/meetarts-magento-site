/**
 * Landofcoder
 *
 * @category   Landofcoder
 * @package    Lof_LayeredNavigation
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */
/*define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'jquery/ui',
    'Lof_LayeredNavigation/js/layer'
], function($, priceUltil) {
    "use strict";*/

    define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'mage/template',
    'jquery/ui',
    'jquery/ui-modules/widgets/slider',
    'Lof_LayeredNavigation/js/layer',
    'Magento_Ui/js/modal/modal',
    'Lof_LayeredNavigation/js/price/slider'
], function ($, priceUltil, mageTemplate) {
"use strict";
    $.widget('lof.layerSlider', $.lof.layer, {
        options: {
            sliderElement: '#lof_price_slider',
            textElementmin_show: '#price_range_text .min_show',
            textElementmax_show: '#price_range_text .max_show'
        },
        _create: function () {
            var self = this;
            jQuery(this.options.sliderElement).slider({
                min: self.options.priceMin,
                max: self.options.priceMax,
                values: [self.options.selectedFrom, self.options.selectedTo],
                slide: function( event, ui ) {
                    self.showText(ui.values[0], ui.values[1]);
                    $(this.options.textElementmin_show).html(ui.values[0]);
                    $(this.options.textElementmax_show).html(ui.values[1]);
                },
                change: function(event, ui) {
                    self.ajaxSubmit(self.getUrl(ui.values[0], ui.values[1]));
                }
            });
            this.showText(this.options.selectedFrom, this.options.selectedTo);
        },

        getUrl: function(from, to){
            return this.options.ajaxUrl.replace(encodeURI('{price_start}'), from).replace(encodeURI('{price_end}'), to);
        },

        showText: function(from, to){
            $(this.options.textElementmin_show).html(from);
            $(this.options.textElementmax_show).html(to);
        },

        formatPrice: function(value) {
            return priceUltil.formatPrice(value, this.options.priceFormat);
        }
    });

    return $.lof.layerSlider;
});

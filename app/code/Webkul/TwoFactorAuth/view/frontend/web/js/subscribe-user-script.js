/**
 * @category   Webkul
 * @package    Webkul_TwoFactorAuth
 * @author     Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

/*jshint jquery:true*/
define([
    'jquery',
], function($) {
    'use strict';
    var globalThis;
    $.widget('pushNotification.subscribeUser', {
        _create: function() {}
    });
    return $.pushNotification.subscribeUser;
});
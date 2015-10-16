define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    var customer = customerData.get('customer');
    console.log('getting customer: ',customer());

    var cart = customerData.get('cart');
    console.log('getting cart: ', cart());

});

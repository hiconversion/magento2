define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    var hicUserData = customerData.get('hicuserdata');
    var hicCartData = customerData.get('hiccartdata');
     
    hicUserData.subscribe(function(user) {
        console.log('user data has updated:',user);

    });
    

    hicCartData.subscribe(function(cart) {
        console.log('cart data has updated:',cart);
    });

});

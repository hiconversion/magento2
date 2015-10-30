define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    function writeWindowObject(name, data) {
        if (data && !$.isArray(data) && !$.isEmptyObject(data)) {
            window.__hic = window.__hic || {};
            window.__hic.data = window.__hic.data || {};
            window.__hic.data[name] = data;
        } 
    }

    var hicUserData = customerData.get('hicuserdata');
    var user = hicUserData();
    var hicCartData = customerData.get('hiccartdata');
    var cart = hicCartData();

    writeWindowObject('userObserver', hicUserData);
    writeWindowObject('cartObserver', hicCartData);
    
    writeWindowObject('user', user);
    writeWindowObject('cart', cart);

     
    hicUserData.subscribe(function(user) {
        writeWindowObject('user', user);
    });
    

    hicCartData.subscribe(function(cart) {
        writeWindowObject('cart', cart);
    });

});

define([
    'jquery',
    'Magento_Customer/js/customer-data',
    ], function ($, customerData) {
    'use strict';
    
    function writeWindowObject(name, data)
    {
        if (data && !$.isArray(data) && !$.isEmptyObject(data)) {
            window.__hic = window.__hic || {};
            window.__hic.data = window.__hic.data || {};
            window.__hic.data[name] = data;
        }
    }
    
    function isEnabled(dataObject)
    {
        return !(dataObject && dataObject.disabled);
    }
    
    var hicUserData = customerData.get('hic-user-data');
    var user = hicUserData();
    var hicCartData = customerData.get('hic-cart-data');
    var cart = hicCartData();
    if ($.isEmptyObject(user) || $.isEmptyObject(cart)) {
        try {
            customerData.reload(['hic-user-data', 'hic-cart-data']);
        } catch (ex) {}
    }
    
    if (isEnabled(user)) {
        writeWindowObject('userObserver', hicUserData);
        writeWindowObject('cartObserver', hicCartData);
    
        writeWindowObject('user', user);
        writeWindowObject('cart', cart);
    
    
        hicUserData.subscribe(function (user) {
            writeWindowObject('user', user);
        });
    
    
        hicCartData.subscribe(function (cart) {
            writeWindowObject('cart', cart);
        });
    }
    });
require(['jquery', 'Magento_Ui/js/modal/alert', 'mage/translate'], function ($, alert, $t) {
    window.activateHicAccount = function (endpoint, url_id, email_id, pw_id) {
        url_id = $('[data-ui-id="' + url_id + '"]').val();
        email_id = $('[data-ui-id="' + email_id + '"]').val();
        pw_id = $('[data-ui-id="' + pw_id + '"]').val();

        /* Remove previous success message if present */
        if ($(".hic-activation-success-message")) {
            $(".hic-activation-success-message").remove();
        }

        /* Basic field validation */
        var errors = [];

        if (!url_id) {
            errors.push($t("Please enter your site url"));
        }

        if (!email_id) {
            errors.push($t("Please enter an email"));
        }

        if (!pw_id) {
            errors.push($t('Please enter a password'));
        }

        if (errors.length > 0) {
            alert({
                title: $t('HiConversion Account Activation Failed'),
                content:  errors.join('<br />')
            });
            return false;
        }

        $(this).text($t("We're activating your account...")).attr('disabled', true);

        var self = this;
        $.post(endpoint, {
            site_url: url_id,
            email: email_id,
            password: pw_id
        }).done(function () {
            $('<div class="message message-success hic-activation-success-message">' + $t("Your account was successfully activated.") + '</div>').insertAfter(self);
            setTimeout(function() {location.reload();}, 500);
        }).fail(function (xhr) {
            if (xhr && xhr.responseJSON && xhr.responseJSON.kind === 'exists') {
                return alert({
                    title: $t('HiConversion Account Activation Failed'),
                    content: $t('The email you entered already exists in our system. If you would like to add a new site, please do so from your HiConversion Account.')
                });
            }
            alert({
                title: $t('HiConversion Account Activation Failed'),
                content: $t('Your HiConversion account could not be activated. Please ensure you have entered a valid site url, email address, and password.')
            });
        }).always(function () {
            $(self).text($t("Activate HiConversion Account")).attr('disabled', false);
        });
    }

    // HiC Link Account
    window.linkHicAccount = function (endpoint, url_id, email_id) {
        url_id = $('[data-ui-id="' + url_id + '"]').val();
        email_id = $('[data-ui-id="' + email_id + '"]').val();

        /* Remove previous success message if present */
        if ($(".hic-link-success-message")) {
            $(".hic-link-success-message").remove();
        }

        /* Basic field validation */
        var errors = [];

        if (!url_id) {
            errors.push($t("Please enter your site url"));
        }

        if (!email_id) {
            errors.push($t("Please enter an email"));
        }

        if (errors.length > 0) {
            alert({
                title: $t('HiConversion Account Linking Failed'),
                content:  errors.join('<br />')
            });
            return false;
        }

        $(this).text($t("We're retrieving your site ID now...")).attr('disabled', true);

        var self = this;
        $.post(endpoint, {
            site_url: url_id,
            email: email_id
        }).done(function () {
            $('<div class="message message-success hic-link-success-message">' + $t("Your account was successfully activated.") + '</div>').insertAfter(self);
            setTimeout(function() {location.reload();}, 500);
        }).fail(function (xhr) {
            if (xhr && xhr.status === 404) {
                return alert({
                    title: $t('HiConversion Account Linking Failed'),
                    content: $t('We could not find the specified site. Please verify the site url and email address and try again.')
                });
            }
            alert({
                title: $t('HiConversion Account Linking Failed'),
                content: $t('Your HiConversion account could not be linked. Please ensure you have entered a valid site url and email address that has access to that site in Hiconversion.')
            });
        }).always(function () {
            $(self).text($t("Get Site ID")).attr('disabled', false);
        });
    }
});
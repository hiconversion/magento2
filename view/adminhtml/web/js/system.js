require(['jquery', 'Magento_Ui/js/modal/alert', 'mage/translate'], function ($, alert, $t) {
    window.activateHicAccount = function (endpoint) {
        var wrapper = $(this).closest('tbody');

        url_id = wrapper.find('input[id*=_site_url]').val();
        email_id = wrapper.find('input[id*=_email]').val();
        pw_id = wrapper.find('input[id*=_password]').val();

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
                title: $t('Account Activation Failed'),
                content: errors.join('<br />')
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
            $(self).parent().append('<div class="message message-success hic-activation-success-message">' + $t("Your account was successfully activated.") + '</div>');
            setTimeout(function () { location.reload(); }, 200);
        }).fail(function (xhr) {
            if (xhr && xhr.responseJSON && xhr.responseJSON.kind === 'exists') {
                return alert({
                    title: $t('Account Activation Failed'),
                    content: $t('The email you entered already exists in our system. If you would like to add a new site, please do so from your account.')
                });
            }
            alert({
                title: $t('Account Activation Failed'),
                content: $t('Your account could not be activated. Please ensure you have entered a valid site url, email address, and password.')
            });
        }).always(function () {
            $(self).text($t("Activate Account")).attr('disabled', false);
        });
    }

    // Link Account
    window.linkHicAccount = function (endpoint) {
        var wrapper = $(this).closest('tbody');

        url_id = wrapper.find('input[id*=_site_url]').val();
        email_id = wrapper.find('input[id*=_email]').val();

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
                title: $t('Get Site Id Failed'),
                content: errors.join('<br />')
            });
            return false;
        }

        $(this).text($t("We're retrieving your site ID now...")).attr('disabled', true);

        var self = this;
        $.post(endpoint, {
            site_url: url_id,
            email: email_id
        }).done(function () {
            $(self).parent().append('<div class="message message-success hic-link-success-message">' + $t("Your Site Id was retrieved. Refreshing configuration now...") + '</div>');
            setTimeout(function () { location.reload(); }, 200);
        }).fail(function (xhr) {
            if (xhr && xhr.status === 404) {
                return alert({
                    title: $t('Get Site Id Failed'),
                    content: $t('We could not find the specified site. Please verify the site url and email address and try again.')
                });
            }
            alert({
                title: $t('Get Site Id Failed'),
                content: $t('Your HiConversion account could not be linked. Please ensure you have entered a valid site url and email address that has access to that site in Hiconversion.')
            });
        }).always(function () {
            $(self).text($t("Get Site ID")).attr('disabled', false);
        });
    }

    //validate account
    window.validateHicAccount = function (endpoint) {
        var wrapper = $(this).closest('tbody');

        url_id = wrapper.find('input[id*=_site_url]').val();
        email_id = wrapper.find('input[id*=_email]').val();
        site_id = wrapper.find('input[id*=_site_id]').val();

        /* Remove previous success message if present */
        if ($(".hic-validate-success-message")) {
            $(".hic-validate-success-message").remove();
        }

        /* Basic field validation */
        var errors = [];

        if (!url_id) {
            errors.push($t("Please enter your site url"));
        }

        if (!email_id) {
            errors.push($t("Please enter an email"));
        }

        if (!site_id) {
            errors.push($t("Please enter a site ID"));
        }

        if (errors.length > 0) {
            alert({
                title: $t('HiConversion Account Validation Failed'),
                content: errors.join('<br />')
            });
            return false;
        }

        $(this).text($t("We're validating your account now...")).attr('disabled', true);

        var self = this;
        $.post(endpoint, {
            site_url: url_id,
            email: email_id,
            site_id: site_id
        }).done(function (result) {
            if (result) {
                $(self).parent().append('<div class="message message-success hic-validate-success-message">' + $t("Your account is valid.") + '</div>');
            } else {
                alert({
                    title: $t('HiConversion Account Validation Failed'),
                    content: $t('Your account is not valid or completely configured. Please check that everything is setup correctly here: <a href="https://h30.hiconversion.net/admin/site/tag">Go to HiConversion Setup</a>')
                });
            }
        }).fail(function (xhr) {
            if (xhr && xhr.status === 404) {
                return alert({
                    title: $t('HiConversion Account Validation Failed'),
                    content: $t('We could not find the specified site. Please verify the site url and email address and try again.')
                });
            }
            alert({
                title: $t('HiConversion Account Validation Failed'),
                content: $t('Your HiConversion account could not be validated. Please ensure you have entered a valid site url and email address that has access to that site in Hiconversion.')
            });
        }).always(function () {
            $(self).text($t("Validate")).attr('disabled', false);
        });
    }
});
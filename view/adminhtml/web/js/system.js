require(['jquery', 'Magento_Ui/js/modal/alert', 'mage/translate', 'mage/validation'], function ($, alert, $t) {

    window.showHicCreateAccountFields = function (ev) {
        ev.preventDefault();
        var wrapper = $(this).closest('fieldset');

        wrapper.find('tr[id*=_link_validate]').hide();
        wrapper.find('tr[id*=_site_id]').hide();
        wrapper.find('.hic-account-info-label').hide();

        wrapper.find('tr[id*=_create_account]').show();
        wrapper.find('.create-hic-account-label').show();
    
    }

    window.showHicGetIdFields = function (ev) {
        ev.preventDefault();
        var wrapper = $(this).closest('fieldset');

        wrapper.find('tr[id*=_link_validate]').show();
        wrapper.find('tr[id*=_site_id]').show();
        wrapper.find('.hic-account-info-label').show();

        wrapper.find('tr[id*=_create_account]').hide();
        wrapper.find('.create-hic-account-label').hide();
    
    }

    window.activateHicAccount = function (endpoint) {
        var wrapper = $(this).closest('tbody');

        var $site_url = wrapper.find('input[id*=_site_url]');
        var $email = wrapper.find('input[id*=_email]');

        var site_url = $site_url.val();
        var email = $email.val();
        var pw = wrapper.find('input[id*=_password]').val();

        var site_id_tr = wrapper.find('tr[id*=_site_id]');
        var site_id_input = wrapper.find('input[id*=_site_id]');
        var create_account_button = wrapper.find('tr[id*=_create_account] button');
        var create_account_note = wrapper.find('tr[id*=_create_account] .note');


        /* Remove previous success message if present */
        if ($(".hic-activation-success-message")) {
            $(".hic-activation-success-message").remove();
        }

        /* Basic field validation */
        var errors = [];

        if (!site_url) {
            errors.push($t("Please enter your site url"));
        }

        if (!email) {
            errors.push($t("Please enter an email"));
        }

        if (!pw) {
            errors.push($t('Please enter a password'));
        }

        if (errors.length > 0) {
            alert({
                title: $t('Account Activation Failed'),
                content: errors.join('<br />')
            });
            return false;
        }

        var validator = $(this).closest('form').validate();
        
        if (!validator.element($site_url) || !validator.element($email)) {
            return false;
        }

        $(this).text($t("We're activating your account...")).attr('disabled', true);

        var self = this;
        $.post(endpoint, {
            site_url: site_url,
            email: email,
            password: pw
        }).done(function (response) {
            if (response && response.result === 'success' && response.external && response.external.length) {
                $(self).parent().append('<div class="message message-success hic-activation-success-message">' + $t("Your account was successfully activated.") + '<br/><b>' + $t('You need to save config before continuing.') + '</b></div>');
                site_id_input.val(response.external);
                site_id_tr.show();
                create_account_button.hide();
                create_account_note.hide();
            } else {
                return alert({
                    title: $t('Account Activation Failed'),
                    content: $t('There was a problem creating your account. Please visit HiConversion to resolve the issue.')
                });
            }
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
            $(self).text($t("Create HiConversion Account")).attr('disabled', false);
        });
    }

    // Link Account
    window.linkHicAccount = function (endpoint) {
        var wrapper = $(this).closest('tbody');

        var $site_url = wrapper.find('input[id*=_site_url]');
        var $email = wrapper.find('input[id*=_email]');

        var site_url = $site_url.val();
        var email = $email.val();

        var site_id_input = wrapper.find('input[id*=_site_id]');

        /* Remove previous success message if present */
        if ($(".hic-link-success-message")) {
            $(".hic-link-success-message").remove();
        }

        /* Basic field validation */
        var errors = [];

        if (!site_url) {
            errors.push($t("Please enter your site url"));
        }

        if (!email) {
            errors.push($t("Please enter an email"));
        }

        if (errors.length > 0) {
            alert({
                title: $t('Get Site Id Failed'),
                content: errors.join('<br />')
            });
            return false;
        }

        var validator = $(this).closest('form').validate();

        if (!validator.element($site_url) || !validator.element($email)) {
            return false;
        }

        $(this).text($t("We're retrieving your site ID now...")).attr('disabled', true);

        var self = this;
        $.post(endpoint, {
            site_url: site_url,
            email: email
        }).done(function (newId) {
            if (newId && newId.length) {
                $(self).parent().append('<div class="message message-success hic-link-success-message">' + $t('Your Site Id was retrieved.') + '<br/><b>' + $t('You need to save config before continuing.') + '</b></div>');
                site_id_input.val(newId);
            } else {
                alert({
                    title: $t('Get Site Id Failed'),
                    content: $t('Your site id could not be retrieved. Please ensure you have entered a valid site url and email address that has access to that site in Hiconversion.')
                });
            }
        }).fail(function (xhr) {
            if (xhr && xhr.status === 404) {
                return alert({
                    title: $t('Get Site Id Failed'),
                    content: $t('We could not find the specified site. Please verify the site url and email address and try again.')
                });
            }
            alert({
                title: $t('Get Site Id Failed'),
                content: $t('Your site id could not be retrieved. Please ensure you have entered a valid site url and email address that has access to that site in Hiconversion.')
            });
        }).always(function () {
            $(self).text($t("Get Site ID")).attr('disabled', false);
        });
    }

    //validate account
    window.validateHicAccount = function (endpoint) {
        var wrapper = $(this).closest('tbody');

        var $site_url = wrapper.find('input[id*=_site_url]');
        var $email = wrapper.find('input[id*=_email]');

        var site_url = $site_url.val();
        var email = $email.val();
        var site_id = wrapper.find('input[id*=_site_id]').val();

        /* Remove previous success message if present */
        if ($(".hic-validate-success-message")) {
            $(".hic-validate-success-message").remove();
        }

        /* Basic field validation */
        var errors = [];

        if (!site_url) {
            errors.push($t("Please enter your site url"));
        }

        if (!email) {
            errors.push($t("Please enter an email"));
        }

        if (!site_id) {
            errors.push($t("Please enter a site ID"));
        }

        if (errors.length > 0) {
            alert({
                title: $t('Account Validation Failed'),
                content: errors.join('<br />')
            });
            return false;
        }

        var validator = $(this).closest('form').validate();

        if (!validator.element($site_url) || !validator.element($email)) {
            return false;
        }

        $(this).text($t("We're validating your account now...")).attr('disabled', true);

        var self = this;
        $.post(endpoint, {
            site_url: site_url,
            email: email,
            site_id: site_id
        }).done(function (result) {
            if (result && result.status === 'valid') {
                $(self).parent().append('<div class="message message-success hic-validate-success-message">' + $t("Your account is valid.") + '</div>');
            } else {
                alert({
                    title: $t('Account Validation Failed'),
                    content: $t(result.msg)
                });
            }
        }).fail(function (xhr) {
            if (xhr && xhr.status === 404) {
                return alert({
                    title: $t('Account Validation Failed'),
                    content: $t('We could not find the specified site. Please verify the site url and email address and try again.')
                });
            }
            alert({
                title: $t('Account Validation Failed'),
                content: $t('Your account could not be validated. Please ensure you have entered a valid site url and email address that has access to that site in Hiconversion.')
            });
        }).always(function () {
            $(self).text($t("Validate")).attr('disabled', false);
        });
    }
});
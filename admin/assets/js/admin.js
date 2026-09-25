/**
 * Okito Cookie Consent — Admin JS
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        var i18n = window.okito_ajax || {};

        // Auto-focus website key input on settings page
        $('#okito_website_key').on('focus', function() {
            $(this).select();
        });

        // Test Connection.
        $('#okito-test-connection').on('click', function() {
            var btn = $(this);
            var resultDiv = $('#okito-connection-result');
            var websiteKey = $('#okito_website_key').val();

            if (!websiteKey) {
                resultDiv.html('<span style="color:#dc3232;">' + i18n.please_enter_key + '</span>').show();
                return;
            }

            btn.prop('disabled', true).text(i18n.testing);
            resultDiv.html('<span style="color:#666;">' + i18n.testing_connection + '</span>').show();

            $.post(i18n.url, {
                action: 'okito_test_connection',
                nonce: i18n.nonce,
                website_key: websiteKey
            }, function(response) {
                btn.prop('disabled', false).text(i18n.test_connection);
                if (response.success) {
                    resultDiv.html('<span style="color:#46b450;">' + response.data + '</span>');
                } else {
                    resultDiv.html('<span style="color:#dc3232;">' + response.data + '</span>');
                }
            }).fail(function() {
                btn.prop('disabled', false).text(i18n.test_connection);
                resultDiv.html('<span style="color:#dc3232;">' + i18n.network_error + '</span>');
            });
        });

        // Clear Cache.
        $('#okito-clear-cache').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).text(i18n.clearing);

            $.post(i18n.url, {
                action: 'okito_clear_cache',
                nonce: i18n.nonce
            }, function(response) {
                btn.prop('disabled', false).text(i18n.clear_cache);
                if (response.success && response.data) {
                    alert(response.data);
                }
            }).fail(function() {
                btn.prop('disabled', false).text(i18n.clear_cache);
            });
        });
    });
})(jQuery);

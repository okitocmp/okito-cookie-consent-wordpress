<?php
/**
 * Okito Settings Page
 * 
 * @package OkitoCookieConsent
 */

if (!defined('ABSPATH')) {
    exit;
}

$okito_website_key = get_option('okito_website_key', '');
$okito_enabled = get_option('okito_enabled', false);

// Handle form save
if (
    current_user_can('manage_options') &&
    isset($_POST['okito_save_settings'], $_POST['_wpnonce']) &&
    wp_verify_nonce(
        sanitize_text_field(wp_unslash($_POST['_wpnonce'])),
        'okito_settings_save'
    )
) {
    $okito_website_key = isset($_POST['okito_website_key']) ? sanitize_text_field(wp_unslash($_POST['okito_website_key'])) : '';
    $okito_enabled = !empty($_POST['okito_enabled']);

    update_option('okito_website_key', $okito_website_key);
    update_option('okito_enabled', $okito_enabled);

    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved!', 'okito-cookie-consent') . '</p></div>';
}

// Result of "Connect with Okito" (display only).
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$okito_connected = isset($_GET['okito_connected']) ? sanitize_key(wp_unslash($_GET['okito_connected'])) : '';
?>

<div class="wrap okito-settings">
    <h1>
        <span class="dashicons dashicons-shield-alt" style="font-size:28px;margin-right:8px;color:#134a98;"></span>
        <?php esc_html_e('Okito Settings', 'okito-cookie-consent'); ?>
    </h1>
    <hr class="wp-header-end">

    <?php if ('1' === $okito_connected): ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Connected to Okito. The cookie banner is now enabled on your site.', 'okito-cookie-consent'); ?></p></div>
    <?php elseif ('0' === $okito_connected): ?>
        <div class="notice notice-error is-dismissible"><p><?php esc_html_e('The Okito connection could not be verified. Please try again.', 'okito-cookie-consent'); ?></p></div>
    <?php endif; ?>

    <div class="okito-card" style="max-width:700px;margin:16px 0;">
        <h2 style="margin-top:0;"><?php esc_html_e('Connect with Okito', 'okito-cookie-consent'); ?></h2>
        <p><?php esc_html_e('Sign in to Okito (or create a free account), choose this site and you are sent back here with the banner enabled. No copying of keys needed.', 'okito-cookie-consent'); ?></p>
        <a href="<?php echo esc_url(OkitoCookieConsent::connect_url()); ?>" class="button button-primary button-hero">
            <?php echo empty($okito_website_key) ? esc_html__('Connect with Okito', 'okito-cookie-consent') : esc_html__('Reconnect with Okito', 'okito-cookie-consent'); ?>
        </a>
        <p class="description" style="margin-top:12px;"><?php esc_html_e('Or enter your Website Key manually below.', 'okito-cookie-consent'); ?></p>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field('okito_settings_save'); ?>

        <table class="form-table" role="presentation">
            <!-- Website Key -->
            <tr>
                <th scope="row">
                    <label for="okito_website_key"><?php esc_html_e('Website Key', 'okito-cookie-consent'); ?></label>
                </th>
                <td>
                    <input type="text" id="okito_website_key" name="okito_website_key" 
                           value="<?php echo esc_attr($okito_website_key); ?>" 
                           class="regular-text" 
                           placeholder="e.g. your-domain-com-abc123" />
                    <button type="button" id="okito-test-connection" class="button" style="margin-left:8px;">
                        <?php esc_html_e('Test Connection', 'okito-cookie-consent'); ?>
                    </button>
                    <p class="description">
                        <?php esc_html_e('Your unique Website Key from the Okito dashboard.', 'okito-cookie-consent'); ?>
                        <a href="<?php echo esc_url('https://app.okito.com/setup'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Get your key →', 'okito-cookie-consent'); ?></a>
                    </p>
                    <div id="okito-connection-result" style="margin-top:8px;display:none;"></div>
                </td>
            </tr>

            <!-- Enable/Disable -->
            <tr>
                <th scope="row"><?php esc_html_e('Cookie Banner', 'okito-cookie-consent'); ?></th>
                <td>
                    <fieldset>
                        <label for="okito_enabled">
                            <input type="checkbox" id="okito_enabled" name="okito_enabled" value="1" 
                                   <?php checked($okito_enabled); ?> />
                            <?php esc_html_e('Enable cookie consent banner on this website', 'okito-cookie-consent'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('When enabled, the Okito CDN script is injected into every page. The banner design and behavior are configured in the Okito dashboard.', 'okito-cookie-consent'); ?>
                        </p>
                    </fieldset>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="submit" name="okito_save_settings" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'okito-cookie-consent'); ?>" />
            <button type="button" id="okito-clear-cache" class="button" style="margin-left:8px;">
                <?php esc_html_e('Clear Cache', 'okito-cookie-consent'); ?>
            </button>
        </p>
    </form>

    <?php if (!empty($okito_website_key)): ?>
        <div class="okito-card" style="margin-top:24px;max-width:700px;">
            <h3><?php esc_html_e('Injected Script Preview', 'okito-cookie-consent'); ?></h3>
            <pre style="background:#f6f7f7;padding:12px;border:1px solid #ddd;border-radius:4px;overflow-x:auto;"><code>&lt;script src="<?php echo esc_url(OKITO_CDN_BASE_URL . '/js/' . $okito_website_key); ?>" async&gt;&lt;/script&gt;</code></pre>
            <p class="description">
                <?php esc_html_e('This script is added to <head> on every public page when the banner is enabled.', 'okito-cookie-consent'); ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="okito-card" style="margin-top:16px;max-width:700px;background:#f8f9fa;">
        <h3><?php esc_html_e('How it works', 'okito-cookie-consent'); ?></h3>
        <ol>
            <li>
                <?php
                echo wp_kses(
                    __('Create an account at <a href="https://app.okito.com" target="_blank" rel="noopener noreferrer">app.okito.com</a>', 'okito-cookie-consent'),
                    array(
                        'a' => array(
                            'href'   => array(),
                            'target' => array(),
                            'rel'    => array(),
                        ),
                    )
                );
                ?>
            </li>
            <li><?php esc_html_e('Add your website and customize your banner in the Okito dashboard', 'okito-cookie-consent'); ?></li>
            <li><?php esc_html_e('Copy your Website Key and paste it above', 'okito-cookie-consent'); ?></li>
            <li><?php esc_html_e('Enable the banner — done! 🎉', 'okito-cookie-consent'); ?></li>
        </ol>
        <p>
            <strong><?php esc_html_e('Features included:', 'okito-cookie-consent'); ?></strong>
            GDPR · CCPA · LGPD · IAB TCF v2.3 · Google Consent Mode v2 · 
            <?php esc_html_e('Auto-language detection · Script blocking · Cookie scanner', 'okito-cookie-consent'); ?>
        </p>
    </div>
</div>

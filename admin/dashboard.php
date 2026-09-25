<?php
/**
 * Okito Admin Dashboard
 * 
 * @package OkitoCookieConsent
 */

if (!defined('ABSPATH')) {
    exit;
}

$okito_website_key = get_option('okito_website_key', '');
$okito_enabled = get_option('okito_enabled', false);
$okito_is_configured = !empty($okito_website_key);
?>

<div class="wrap okito-dashboard">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-shield-alt" style="font-size:28px;margin-right:8px;color:#134a98;"></span>
        <?php esc_html_e('Okito Cookie Consent', 'okito-cookie-consent'); ?>
    </h1>
    <hr class="wp-header-end">

    <?php if (!$okito_is_configured): ?>
        <!-- ──────────────── SETUP WIZARD ──────────────── -->
        <div class="notice notice-warning" style="border-left-color:#134a98;">
            <p>
                <strong><?php esc_html_e('Setup Required', 'okito-cookie-consent'); ?></strong><br>
                <?php esc_html_e('Connect your Okito account to start showing cookie consent banners.', 'okito-cookie-consent'); ?>
            </p>
        </div>

        <div class="okito-card" style="max-width:700px;">
            <h2><?php esc_html_e('Connect with Okito', 'okito-cookie-consent'); ?></h2>
            <p><?php esc_html_e('Sign in to Okito (or create a free account), choose this site and you are sent back here with the banner enabled. No copying of keys needed.', 'okito-cookie-consent'); ?></p>
            <a href="<?php echo esc_url(OkitoCookieConsent::connect_url()); ?>" class="button button-primary button-hero">
                <?php esc_html_e('Connect with Okito', 'okito-cookie-consent'); ?>
            </a>
        </div>

        <div class="okito-card" style="max-width:700px;margin-top:16px;">
            <h2><?php esc_html_e('Or set it up manually', 'okito-cookie-consent'); ?></h2>
            <ol class="okito-setup-steps">
                <li>
                    <strong><?php esc_html_e('Get your Website Key', 'okito-cookie-consent'); ?></strong><br>
                    <p><?php esc_html_e('Log in to the Okito dashboard, add your website, and copy the Website Key.', 'okito-cookie-consent'); ?></p>
                    <a href="<?php echo esc_url('https://app.okito.com/setup'); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary">
                        <?php esc_html_e('Open Okito Dashboard →', 'okito-cookie-consent'); ?>
                    </a>
                </li>
                <li style="margin-top:20px;">
                    <strong><?php esc_html_e('Paste it here', 'okito-cookie-consent'); ?></strong><br>
                    <p><?php esc_html_e('Go to Settings and enter your Website Key to activate the cookie banner.', 'okito-cookie-consent'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=okito-settings')); ?>" class="button button-primary">
                        <?php esc_html_e('Go to Settings', 'okito-cookie-consent'); ?>
                    </a>
                </li>
            </ol>
        </div>

    <?php else: ?>
        <!-- ──────────────── CONFIGURED DASHBOARD ──────────────── -->
        <div class="okito-status-bar" style="display:flex;gap:24px;margin:16px 0;align-items:center;">
            <div>
                <strong><?php esc_html_e('Status:', 'okito-cookie-consent'); ?></strong>
                <?php if ($okito_enabled): ?>
                    <span style="color:#46b450;font-weight:bold;">● <?php esc_html_e('Active', 'okito-cookie-consent'); ?></span>
                <?php else: ?>
                    <span style="color:#dc3232;font-weight:bold;">● <?php esc_html_e('Inactive', 'okito-cookie-consent'); ?></span>
                    — <a href="<?php echo esc_url(admin_url('admin.php?page=okito-settings')); ?>"><?php esc_html_e('Enable now', 'okito-cookie-consent'); ?></a>
                <?php endif; ?>
            </div>
            <div>
                <strong><?php esc_html_e('Website Key:', 'okito-cookie-consent'); ?></strong>
                <code><?php echo esc_html(substr($okito_website_key, 0, 20) . (strlen($okito_website_key) > 20 ? '…' : '')); ?></code>
            </div>
        </div>

        <?php if ($okito_enabled): ?>
            <div class="notice notice-success" style="border-left-color:#81bc44;">
                <p>
                    ✅ <?php esc_html_e('Your Okito cookie consent banner is live! Visitors will see it on their first visit.', 'okito-cookie-consent'); ?>
                </p>
            </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-top:20px;">
            <!-- Manage Banner Card -->
            <div class="okito-card">
                <h3>🎨 <?php esc_html_e('Manage Banner', 'okito-cookie-consent'); ?></h3>
                <p><?php esc_html_e('Customize your banner design, position, colors, and content in the Okito dashboard.', 'okito-cookie-consent'); ?></p>
                <a href="<?php echo esc_url('https://app.okito.com/banner-builder'); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary">
                    <?php esc_html_e('Open Banner Builder →', 'okito-cookie-consent'); ?>
                </a>
            </div>

            <!-- Analytics Card -->
            <div class="okito-card">
                <h3>📊 <?php esc_html_e('View Analytics', 'okito-cookie-consent'); ?></h3>
                <p><?php esc_html_e('Track consent rates, visitor interactions, and compliance metrics.', 'okito-cookie-consent'); ?></p>
                <a href="<?php echo esc_url('https://app.okito.com/dashboard'); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary">
                    <?php esc_html_e('Open Dashboard →', 'okito-cookie-consent'); ?>
                </a>
            </div>

            <!-- Cookie Scanner Card -->
            <div class="okito-card">
                <h3>🔍 <?php esc_html_e('Cookie Scanner', 'okito-cookie-consent'); ?></h3>
                <p><?php esc_html_e('Scan your website for cookies and categorize them automatically.', 'okito-cookie-consent'); ?></p>
                <a href="<?php echo esc_url('https://app.okito.com/cookie-manager'); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary">
                    <?php esc_html_e('Scan Cookies →', 'okito-cookie-consent'); ?>
                </a>
            </div>

            <!-- Settings Card -->
            <div class="okito-card">
                <h3>⚙️ <?php esc_html_e('Plugin Settings', 'okito-cookie-consent'); ?></h3>
                <p><?php esc_html_e('Update your Website Key or toggle the banner on/off.', 'okito-cookie-consent'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=okito-settings')); ?>" class="button button-secondary">
                    <?php esc_html_e('Settings', 'okito-cookie-consent'); ?>
                </a>
            </div>
        </div>

        <!-- Script Integration Info -->
        <div class="okito-card" style="margin-top:20px;">
            <h3>🔗 <?php esc_html_e('Script Integration', 'okito-cookie-consent'); ?></h3>
            <p><?php esc_html_e('The following script is automatically injected into your site\'s <head> tag:', 'okito-cookie-consent'); ?></p>
            <pre style="background:#f6f7f7;padding:12px;border:1px solid #ddd;border-radius:4px;overflow-x:auto;"><code>&lt;script src="<?php echo esc_url(OKITO_CDN_BASE_URL . '/js/' . $okito_website_key); ?>" async&gt;&lt;/script&gt;</code></pre>
            <p class="description">
                <?php esc_html_e('This single script handles everything: banner display, consent storage, Google Consent Mode, IAB TCF v2.3, and automatic language detection.', 'okito-cookie-consent'); ?>
            </p>
        </div>

    <?php endif; ?>

    <!-- Help Section -->
    <div class="okito-card" style="margin-top:24px;background:#f8f9fa;">
        <h3><?php esc_html_e('Need Help?', 'okito-cookie-consent'); ?></h3>
        <p>
            <a href="<?php echo esc_url('https://app.okito.com'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Okito Dashboard', 'okito-cookie-consent'); ?></a> · 
            <a href="<?php echo esc_url('https://okito.com'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Website', 'okito-cookie-consent'); ?></a>
        </p>
    </div>
</div>

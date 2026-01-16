<?php
/**
 * WPEL Documentation Page
 *
 * Provides comprehensive documentation about the plugin features and usage.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPEL_Documentation {

    /**
     * Render the documentation page
     */
    public static function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'wpel'));
        }

        $settings = get_option('wpel_settings', array());
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'overview';

        ?>
        <div class="wrap wpel-docs-wrap">
            <div class="wpel-docs-header">
                <div class="wpel-docs-header-content">
                    <h1>
                        <span class="dashicons dashicons-shield"></span>
                        <?php esc_html_e('WP Exception Logger', 'wpel'); ?>
                    </h1>
                    <p class="wpel-version">Version <?php echo esc_html(WPEL_VERSION); ?></p>
                </div>
                <div class="wpel-docs-header-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpel-logs')); ?>" class="button button-primary">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php esc_html_e('View Logs', 'wpel'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpel-settings')); ?>" class="button">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <?php esc_html_e('Settings', 'wpel'); ?>
                    </a>
                </div>
            </div>

            <nav class="wpel-docs-nav">
                <a href="<?php echo esc_url(add_query_arg('tab', 'overview')); ?>"
                   class="wpel-nav-item <?php echo $active_tab === 'overview' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-info-outline"></span>
                    <?php esc_html_e('Overview', 'wpel'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'setup')); ?>"
                   class="wpel-nav-item <?php echo $active_tab === 'setup' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php esc_html_e('Setup Guide', 'wpel'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'features')); ?>"
                   class="wpel-nav-item <?php echo $active_tab === 'features' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-star-filled"></span>
                    <?php esc_html_e('Features', 'wpel'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'logging')); ?>"
                   class="wpel-nav-item <?php echo $active_tab === 'logging' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php esc_html_e('Logging API', 'wpel'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'json-checker')); ?>"
                   class="wpel-nav-item <?php echo $active_tab === 'json-checker' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-media-code"></span>
                    <?php esc_html_e('JSON Checker', 'wpel'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'notifications')); ?>"
                   class="wpel-nav-item <?php echo $active_tab === 'notifications' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-email-alt"></span>
                    <?php esc_html_e('Notifications', 'wpel'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'troubleshooting')); ?>"
                   class="wpel-nav-item <?php echo $active_tab === 'troubleshooting' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-sos"></span>
                    <?php esc_html_e('Troubleshooting', 'wpel'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab', 'status')); ?>"
                   class="wpel-nav-item <?php echo $active_tab === 'status' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-heart"></span>
                    <?php esc_html_e('System Status', 'wpel'); ?>
                </a>
            </nav>

            <div class="wpel-docs-content">
                <?php
                switch ($active_tab) {
                    case 'setup':
                        self::render_setup_tab();
                        break;
                    case 'features':
                        self::render_features_tab();
                        break;
                    case 'logging':
                        self::render_logging_tab();
                        break;
                    case 'json-checker':
                        self::render_json_checker_tab();
                        break;
                    case 'notifications':
                        self::render_notifications_tab();
                        break;
                    case 'troubleshooting':
                        self::render_troubleshooting_tab();
                        break;
                    case 'status':
                        self::render_status_tab();
                        break;
                    default:
                        self::render_overview_tab();
                        break;
                }
                ?>
            </div>

            <div class="wpel-docs-footer">
                <p>
                    <span class="dashicons dashicons-shield"></span>
                    <?php esc_html_e('WP Exception Logger - Laravel-like logging system for WordPress', 'wpel'); ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Render Overview Tab
     */
    private static function render_overview_tab() {
        ?>
        <div class="wpel-docs-section">
            <div class="wpel-welcome-banner">
                <div class="wpel-welcome-icon">
                    <span class="dashicons dashicons-shield-alt"></span>
                </div>
                <div class="wpel-welcome-content">
                    <h2><?php esc_html_e('Welcome to WP Exception Logger', 'wpel'); ?></h2>
                    <p><?php esc_html_e('A comprehensive Laravel-like logging system for WordPress that helps you track errors, warnings, and important events across your website.', 'wpel'); ?></p>
                </div>
            </div>

            <div class="wpel-cards-grid">
                <div class="wpel-card">
                    <div class="wpel-card-icon wpel-icon-error">
                        <span class="dashicons dashicons-warning"></span>
                    </div>
                    <h3><?php esc_html_e('Error Tracking', 'wpel'); ?></h3>
                    <p><?php esc_html_e('Automatically captures PHP errors, exceptions, fatal errors, and HTTP API failures.', 'wpel'); ?></p>
                </div>

                <div class="wpel-card">
                    <div class="wpel-card-icon wpel-icon-notify">
                        <span class="dashicons dashicons-bell"></span>
                    </div>
                    <h3><?php esc_html_e('Instant Notifications', 'wpel'); ?></h3>
                    <p><?php esc_html_e('Get notified via Slack, email, or webhooks when critical errors occur.', 'wpel'); ?></p>
                </div>

                <div class="wpel-card">
                    <div class="wpel-card-icon wpel-icon-json">
                        <span class="dashicons dashicons-media-code"></span>
                    </div>
                    <h3><?php esc_html_e('JSON File Monitoring', 'wpel'); ?></h3>
                    <p><?php esc_html_e('Validates global JSON cache files and auto-repairs them from Statamic API if corrupted.', 'wpel'); ?></p>
                </div>

                <div class="wpel-card">
                    <div class="wpel-card-icon wpel-icon-export">
                        <span class="dashicons dashicons-download"></span>
                    </div>
                    <h3><?php esc_html_e('Export & Retention', 'wpel'); ?></h3>
                    <p><?php esc_html_e('Export logs as CSV/JSON and configure automatic log retention policies.', 'wpel'); ?></p>
                </div>
            </div>

            <div class="wpel-quick-links">
                <h3><?php esc_html_e('Quick Links', 'wpel'); ?></h3>
                <div class="wpel-links-grid">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpel-logs')); ?>" class="wpel-link-card">
                        <span class="dashicons dashicons-list-view"></span>
                        <span><?php esc_html_e('View All Logs', 'wpel'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpel-settings')); ?>" class="wpel-link-card">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <span><?php esc_html_e('Configure Settings', 'wpel'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wpel-json-checker')); ?>" class="wpel-link-card">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <span><?php esc_html_e('Check JSON Files', 'wpel'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg('tab', 'logging')); ?>" class="wpel-link-card">
                        <span class="dashicons dashicons-editor-code"></span>
                        <span><?php esc_html_e('Developer API', 'wpel'); ?></span>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Setup Guide Tab
     */
    private static function render_setup_tab() {
        $settings = get_option('wpel_settings', array());
        ?>
        <div class="wpel-docs-section">
            <h2><span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e('Setup Guide', 'wpel'); ?></h2>

            <div class="wpel-info-box wpel-info-primary">
                <span class="dashicons dashicons-info"></span>
                <div>
                    <strong><?php esc_html_e('Quick Start', 'wpel'); ?></strong>
                    <p><?php esc_html_e('Follow these steps to configure WPEL for your site. Most features work out of the box!', 'wpel'); ?></p>
                </div>
            </div>

            <div class="wpel-feature-list">
                <!-- Step 1 -->
                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-info">
                            <span style="color:#fff;font-weight:bold;font-size:18px;">1</span>
                        </span>
                        <h3><?php esc_html_e('Configure Notification Channels', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <p><?php esc_html_e('Set up at least one notification channel to receive alerts:', 'wpel'); ?></p>

                        <h4><?php esc_html_e('Slack Webhook Setup', 'wpel'); ?></h4>
                        <ol class="wpel-numbered-list">
                            <li>
                                <strong><?php esc_html_e('Create Slack App', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Go to api.slack.com/apps and create a new app', 'wpel'); ?></p>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Enable Incoming Webhooks', 'wpel'); ?></strong>
                                <p><?php esc_html_e('In your app settings, enable "Incoming Webhooks"', 'wpel'); ?></p>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Add Webhook to Channel', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Click "Add New Webhook to Workspace" and select a channel', 'wpel'); ?></p>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Copy Webhook URL', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Copy the webhook URL and paste it in WPEL Settings', 'wpel'); ?></p>
                            </li>
                        </ol>

                        <div class="wpel-code-block">
                            <code><?php esc_html_e('Example: https://hooks.slack.com/services/TXXXXX/BXXXXX/your-webhook-token', 'wpel'); ?></code>
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-success">
                            <span style="color:#fff;font-weight:bold;font-size:18px;">2</span>
                        </span>
                        <h3><?php esc_html_e('Configure JSON Checker (For Statamic Integration)', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <p><?php esc_html_e('If you use Statamic for content management, configure the API URL in your .env file:', 'wpel'); ?></p>

                        <div class="wpel-code-example">
                            <div class="wpel-code-header">
                                <span class="wpel-badge wpel-badge-default">.env</span>
                                <code><?php esc_html_e('Environment Variables', 'wpel'); ?></code>
                            </div>
                            <pre><code># Statamic API Configuration
STATAMIC_API_URL=https://your-statamic-site.com/api

# Optional: API Token for authenticated requests
STATAMIC_API_TOKEN=your-api-token-here</code></pre>
                        </div>

                        <p><?php esc_html_e('The JSON Checker monitors these files:', 'wpel'); ?></p>
                        <table class="wpel-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('File', 'wpel'); ?></th>
                                    <th><?php esc_html_e('API Endpoint', 'wpel'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><code>globalsetting.json</code></td><td><code>/api/global-sets/jovie_global_setting</code></td></tr>
                                <tr><td><code>seogloablsetting.json</code></td><td><code>/api/global-sets/seo_global_settings</code></td></tr>
                                <tr><td><code>globalchildcareservices.json</code></td><td><code>/api/global-sets/childcare_services</code></td></tr>
                                <tr><td><code>globalmicrosites.json</code></td><td><code>/api/global-sets/microsites</code></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-warning">
                            <span style="color:#fff;font-weight:bold;font-size:18px;">3</span>
                        </span>
                        <h3><?php esc_html_e('Configure Capture Settings', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <p><?php esc_html_e('Choose what types of errors to capture in Settings:', 'wpel'); ?></p>
                        <table class="wpel-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Option', 'wpel'); ?></th>
                                    <th><?php esc_html_e('Recommended', 'wpel'); ?></th>
                                    <th><?php esc_html_e('Description', 'wpel'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php esc_html_e('PHP Errors', 'wpel'); ?></td>
                                    <td><span class="wpel-badge wpel-badge-success"><?php esc_html_e('ON', 'wpel'); ?></span></td>
                                    <td><?php esc_html_e('Captures notices, warnings, and errors', 'wpel'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('PHP Exceptions', 'wpel'); ?></td>
                                    <td><span class="wpel-badge wpel-badge-success"><?php esc_html_e('ON', 'wpel'); ?></span></td>
                                    <td><?php esc_html_e('Captures uncaught exceptions', 'wpel'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Fatal Errors', 'wpel'); ?></td>
                                    <td><span class="wpel-badge wpel-badge-success"><?php esc_html_e('ON', 'wpel'); ?></span></td>
                                    <td><?php esc_html_e('Captures fatal errors via shutdown handler', 'wpel'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('HTTP API Failures', 'wpel'); ?></td>
                                    <td><span class="wpel-badge wpel-badge-success"><?php esc_html_e('ON', 'wpel'); ?></span></td>
                                    <td><?php esc_html_e('Captures failed wp_remote_* calls', 'wpel'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('Cron Failures', 'wpel'); ?></td>
                                    <td><span class="wpel-badge wpel-badge-success"><?php esc_html_e('ON', 'wpel'); ?></span></td>
                                    <td><?php esc_html_e('Captures WP-Cron failures', 'wpel'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php esc_html_e('WP_Error Objects', 'wpel'); ?></td>
                                    <td><span class="wpel-badge wpel-badge-warning"><?php esc_html_e('OFF', 'wpel'); ?></span></td>
                                    <td><?php esc_html_e('Can be noisy - enable only if needed', 'wpel'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-export">
                            <span style="color:#fff;font-weight:bold;font-size:18px;">4</span>
                        </span>
                        <h3><?php esc_html_e('Set Log Retention', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <p><?php esc_html_e('Configure how long to keep logs to prevent database bloat:', 'wpel'); ?></p>
                        <ul class="wpel-check-list">
                            <li><span class="dashicons dashicons-yes"></span> <strong><?php esc_html_e('Development:', 'wpel'); ?></strong> <?php esc_html_e('7-14 days', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <strong><?php esc_html_e('Staging:', 'wpel'); ?></strong> <?php esc_html_e('14-30 days', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <strong><?php esc_html_e('Production:', 'wpel'); ?></strong> <?php esc_html_e('30-90 days', 'wpel'); ?></li>
                        </ul>
                        <p><?php esc_html_e('Logs are automatically purged daily via WP-Cron.', 'wpel'); ?></p>
                    </div>
                </div>
            </div>

            <div class="wpel-cta-box">
                <a href="<?php echo esc_url(admin_url('admin.php?page=wpel-settings')); ?>" class="button button-primary button-hero">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e('Go to Settings', 'wpel'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render Features Tab
     */
    private static function render_features_tab() {
        ?>
        <div class="wpel-docs-section">
            <h2><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e('Plugin Features', 'wpel'); ?></h2>

            <div class="wpel-feature-list">
                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-success">
                            <span class="dashicons dashicons-database"></span>
                        </span>
                        <h3><?php esc_html_e('Database Logging', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <p><?php esc_html_e('All logs are stored in a dedicated database table with the following structure:', 'wpel'); ?></p>
                        <table class="wpel-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Column', 'wpel'); ?></th>
                                    <th><?php esc_html_e('Type', 'wpel'); ?></th>
                                    <th><?php esc_html_e('Description', 'wpel'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><code>id</code></td><td>BIGINT</td><td><?php esc_html_e('Auto-increment primary key', 'wpel'); ?></td></tr>
                                <tr><td><code>type</code></td><td>VARCHAR(20)</td><td><?php esc_html_e('Log type: error, warning, info, success', 'wpel'); ?></td></tr>
                                <tr><td><code>message</code></td><td>TEXT</td><td><?php esc_html_e('Log message', 'wpel'); ?></td></tr>
                                <tr><td><code>context</code></td><td>LONGTEXT</td><td><?php esc_html_e('JSON-encoded context data', 'wpel'); ?></td></tr>
                                <tr><td><code>created_at</code></td><td>DATETIME</td><td><?php esc_html_e('Timestamp (UTC)', 'wpel'); ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-warning">
                            <span class="dashicons dashicons-warning"></span>
                        </span>
                        <h3><?php esc_html_e('Auto-Capture Sources', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <ul class="wpel-check-list">
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('PHP Errors (notices, warnings, errors)', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('PHP Exceptions (uncaught)', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Fatal Errors (shutdown handler)', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('HTTP API Failures', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('WP-Cron Failures', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('WP_Error Objects (optional)', 'wpel'); ?></li>
                        </ul>
                    </div>
                </div>

                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-info">
                            <span class="dashicons dashicons-shield"></span>
                        </span>
                        <h3><?php esc_html_e('Security Features', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <ul class="wpel-check-list">
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Automatic redaction of sensitive data (passwords, tokens, API keys)', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Bearer token scrubbing in string values', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Deduplication to prevent log flooding', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Configurable log retention period', 'wpel'); ?></li>
                        </ul>

                        <h4><?php esc_html_e('Redacted Keys', 'wpel'); ?></h4>
                        <p><?php esc_html_e('The following keys are automatically redacted from context data:', 'wpel'); ?></p>
                        <div class="wpel-code-block">
                            <code>password, passwd, pwd, secret, token, api_key, apikey, auth, authorization, credit_card, card_number, cvv, ssn</code>
                        </div>
                    </div>
                </div>

                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-export">
                            <span class="dashicons dashicons-admin-tools"></span>
                        </span>
                        <h3><?php esc_html_e('File Logging', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <p><?php esc_html_e('Optional file logging creates daily log files in JSONL format:', 'wpel'); ?></p>
                        <div class="wpel-code-block">
                            <code>/wp-content/wpel-logs/YYYY-MM-DD.log</code>
                        </div>
                        <p><?php esc_html_e('Each line is a JSON object with timestamp, type, message, and context.', 'wpel'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Logging API Tab
     */
    private static function render_logging_tab() {
        ?>
        <div class="wpel-docs-section">
            <h2><span class="dashicons dashicons-editor-code"></span> <?php esc_html_e('Developer Logging API', 'wpel'); ?></h2>

            <div class="wpel-api-section">
                <h3><?php esc_html_e('Helper Functions', 'wpel'); ?></h3>
                <p><?php esc_html_e('Use these functions anywhere in your theme or plugin to log messages:', 'wpel'); ?></p>

                <div class="wpel-code-example">
                    <div class="wpel-code-header">
                        <span class="wpel-badge wpel-badge-error"><?php esc_html_e('Error', 'wpel'); ?></span>
                        <code>wpel_log_error()</code>
                    </div>
                    <pre><code>&lt;?php
// Log an error with context
wpel_log_error('Payment gateway timeout', array(
    'order_id' => 1234,
    'gateway' => 'stripe',
    'response_code' => 504
));

// Log an error with Slack notification
wpel_log_error('Critical database error', $context, true);</code></pre>
                </div>

                <div class="wpel-code-example">
                    <div class="wpel-code-header">
                        <span class="wpel-badge wpel-badge-warning"><?php esc_html_e('Warning', 'wpel'); ?></span>
                        <code>wpel_log_warning()</code>
                    </div>
                    <pre><code>&lt;?php
// Log a warning
wpel_log_warning('API rate limit approaching', array(
    'current_usage' => 950,
    'limit' => 1000
));</code></pre>
                </div>

                <div class="wpel-code-example">
                    <div class="wpel-code-header">
                        <span class="wpel-badge wpel-badge-info"><?php esc_html_e('Info', 'wpel'); ?></span>
                        <code>wpel_log_info()</code>
                    </div>
                    <pre><code>&lt;?php
// Log informational message
wpel_log_info('User login successful', array(
    'user_id' => get_current_user_id(),
    'ip' => $_SERVER['REMOTE_ADDR']
));</code></pre>
                </div>

                <div class="wpel-code-example">
                    <div class="wpel-code-header">
                        <span class="wpel-badge wpel-badge-success"><?php esc_html_e('Success', 'wpel'); ?></span>
                        <code>wpel_log_success()</code>
                    </div>
                    <pre><code>&lt;?php
// Log success message
wpel_log_success('Order completed successfully', array(
    'order_id' => 5678,
    'total' => 99.99
));</code></pre>
                </div>

                <div class="wpel-code-example">
                    <div class="wpel-code-header">
                        <span class="wpel-badge wpel-badge-default"><?php esc_html_e('Generic', 'wpel'); ?></span>
                        <code>wpel_log()</code>
                    </div>
                    <pre><code>&lt;?php
// Generic log function
wpel_log('custom_type', 'Custom message', $context, $slack_alert);</code></pre>
                </div>
            </div>

            <div class="wpel-api-section">
                <h3><?php esc_html_e('Function Parameters', 'wpel'); ?></h3>
                <table class="wpel-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Parameter', 'wpel'); ?></th>
                            <th><?php esc_html_e('Type', 'wpel'); ?></th>
                            <th><?php esc_html_e('Description', 'wpel'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>$message</code></td>
                            <td>string</td>
                            <td><?php esc_html_e('The log message (required)', 'wpel'); ?></td>
                        </tr>
                        <tr>
                            <td><code>$context</code></td>
                            <td>array|object|WP_Error</td>
                            <td><?php esc_html_e('Additional context data (optional)', 'wpel'); ?></td>
                        </tr>
                        <tr>
                            <td><code>$slack_alert</code></td>
                            <td>bool</td>
                            <td><?php esc_html_e('Force Slack notification (default: false)', 'wpel'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="wpel-api-section">
                <h3><?php esc_html_e('Available Filters', 'wpel'); ?></h3>
                <table class="wpel-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Filter', 'wpel'); ?></th>
                            <th><?php esc_html_e('Description', 'wpel'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>wpel_log_types</code></td>
                            <td><?php esc_html_e('Customize permitted log types', 'wpel'); ?></td>
                        </tr>
                        <tr>
                            <td><code>wpel_sensitive_keys</code></td>
                            <td><?php esc_html_e('Add/remove keys to redact', 'wpel'); ?></td>
                        </tr>
                        <tr>
                            <td><code>wpel_sanitize_context</code></td>
                            <td><?php esc_html_e('Filter context before storage', 'wpel'); ?></td>
                        </tr>
                        <tr>
                            <td><code>wpel_should_notify</code></td>
                            <td><?php esc_html_e('Dynamic notification logic', 'wpel'); ?></td>
                        </tr>
                        <tr>
                            <td><code>wpel_notification_payload</code></td>
                            <td><?php esc_html_e('Modify webhook payload', 'wpel'); ?></td>
                        </tr>
                        <tr>
                            <td><code>wpel_file_log_dir</code></td>
                            <td><?php esc_html_e('Override file log directory', 'wpel'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="wpel-api-section">
                <h3><?php esc_html_e('Available Actions', 'wpel'); ?></h3>
                <div class="wpel-code-example">
                    <pre><code>&lt;?php
// Fired after every log entry is added
add_action('wpel_log_added', function($type, $message, $context, $log_id) {
    // Your custom logic here
    // e.g., send to external monitoring service
}, 10, 4);</code></pre>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render JSON Checker Tab
     */
    private static function render_json_checker_tab() {
        $checker = WPEL_Global_JSON_Checker::instance();
        $file_map = $checker->get_file_handle_map();
        ?>
        <div class="wpel-docs-section">
            <h2><span class="dashicons dashicons-media-code"></span> <?php esc_html_e('Global JSON File Checker', 'wpel'); ?></h2>

            <div class="wpel-info-box wpel-info-primary">
                <span class="dashicons dashicons-info"></span>
                <div>
                    <strong><?php esc_html_e('What does it do?', 'wpel'); ?></strong>
                    <p><?php esc_html_e('The JSON Checker monitors your global JSON cache files for validity. If a file becomes corrupted or contains invalid JSON, it automatically fetches fresh data from the Statamic API and repairs the file.', 'wpel'); ?></p>
                </div>
            </div>

            <div class="wpel-feature-item">
                <div class="wpel-feature-header">
                    <span class="wpel-feature-icon wpel-icon-info">
                        <span class="dashicons dashicons-list-view"></span>
                    </span>
                    <h3><?php esc_html_e('Monitored Files', 'wpel'); ?></h3>
                </div>
                <div class="wpel-feature-body">
                    <table class="wpel-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('File Name', 'wpel'); ?></th>
                                <th><?php esc_html_e('Statamic Handle', 'wpel'); ?></th>
                                <th><?php esc_html_e('Purpose', 'wpel'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>globalsetting.json</code></td>
                                <td><code>jovie_global_setting</code></td>
                                <td><?php esc_html_e('Global page settings and content', 'wpel'); ?></td>
                            </tr>
                            <tr>
                                <td><code>seogloablsetting.json</code></td>
                                <td><code>seo_global_settings</code></td>
                                <td><?php esc_html_e('SEO meta settings', 'wpel'); ?></td>
                            </tr>
                            <tr>
                                <td><code>globalchildcareservices.json</code></td>
                                <td><code>childcare_services</code></td>
                                <td><?php esc_html_e('Service definitions', 'wpel'); ?></td>
                            </tr>
                            <tr>
                                <td><code>globalmicrosites.json</code></td>
                                <td><code>microsites</code></td>
                                <td><?php esc_html_e('Microsite configuration', 'wpel'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="wpel-feature-item">
                <div class="wpel-feature-header">
                    <span class="wpel-feature-icon wpel-icon-success">
                        <span class="dashicons dashicons-update"></span>
                    </span>
                    <h3><?php esc_html_e('How It Works', 'wpel'); ?></h3>
                </div>
                <div class="wpel-feature-body">
                    <ol class="wpel-numbered-list">
                        <li>
                            <strong><?php esc_html_e('Scheduled Check', 'wpel'); ?></strong>
                            <p><?php esc_html_e('A cron job runs every hour to check all global JSON files.', 'wpel'); ?></p>
                        </li>
                        <li>
                            <strong><?php esc_html_e('Validation', 'wpel'); ?></strong>
                            <p><?php esc_html_e('Each file is checked for: existence, non-empty content, valid JSON syntax, and proper data structure.', 'wpel'); ?></p>
                        </li>
                        <li>
                            <strong><?php esc_html_e('Auto-Repair', 'wpel'); ?></strong>
                            <p><?php esc_html_e('If a file is invalid, fresh data is fetched from the Statamic API and the file is replaced.', 'wpel'); ?></p>
                        </li>
                        <li>
                            <strong><?php esc_html_e('Notification', 'wpel'); ?></strong>
                            <p><?php esc_html_e('A Slack notification is sent with details about which file was repaired and what the original error was.', 'wpel'); ?></p>
                        </li>
                    </ol>
                </div>
            </div>

            <div class="wpel-feature-item">
                <div class="wpel-feature-header">
                    <span class="wpel-feature-icon wpel-icon-notify">
                        <span class="dashicons dashicons-format-chat"></span>
                    </span>
                    <h3><?php esc_html_e('Slack Notifications', 'wpel'); ?></h3>
                </div>
                <div class="wpel-feature-body">
                    <p><?php esc_html_e('When a JSON file is found invalid and repaired, you receive a detailed Slack notification:', 'wpel'); ?></p>

                    <div class="wpel-slack-preview">
                        <div class="wpel-slack-message" style="border-left-color: #f59e0b;">
                            <strong style="font-size: 16px;">&#9888;&#65039; JSON File Invalid - Auto Fixed</strong>
                            <p style="margin: 12px 0;"><code>globalsetting.json</code> was found to be invalid and has been automatically fixed!</p>
                            <table style="font-size: 13px; margin-top: 10px;">
                                <tr><td style="padding: 4px 20px 4px 0;"><strong>Error Found:</strong></td><td>Syntax error, malformed JSON</td></tr>
                                <tr><td style="padding: 4px 20px 4px 0;"><strong>Status:</strong></td><td>&#9989; Fixed Successfully</td></tr>
                            </table>
                        </div>
                    </div>

                    <p style="margin-top: 16px;"><?php esc_html_e('If auto-repair fails, you get a critical alert:', 'wpel'); ?></p>

                    <div class="wpel-slack-preview">
                        <div class="wpel-slack-message" style="border-left-color: #dc2626;">
                            <strong style="font-size: 16px;">&#128680; JSON File Invalid - Repair FAILED</strong>
                            <p style="margin: 12px 0;"><code>globalsetting.json</code> was found to be invalid and could NOT be repaired!</p>
                            <p style="color: #dc2626;"><strong>Manual intervention required.</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wpel-feature-item">
                <div class="wpel-feature-header">
                    <span class="wpel-feature-icon wpel-icon-warning">
                        <span class="dashicons dashicons-admin-settings"></span>
                    </span>
                    <h3><?php esc_html_e('Requirements', 'wpel'); ?></h3>
                </div>
                <div class="wpel-feature-body">
                    <p><?php esc_html_e('For the JSON Checker to work properly, ensure:', 'wpel'); ?></p>
                    <ul class="wpel-check-list">
                        <li><span class="dashicons dashicons-yes"></span> <code>STATAMIC_API_URL</code> <?php esc_html_e('is configured in your .env file', 'wpel'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Slack webhook URL is configured in WPEL Settings (for notifications)', 'wpel'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('WordPress cron is functioning properly', 'wpel'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Cache directory is writable by PHP', 'wpel'); ?></li>
                    </ul>
                </div>
            </div>

            <div class="wpel-cta-box">
                <a href="<?php echo esc_url(admin_url('admin.php?page=wpel-json-checker')); ?>" class="button button-primary button-hero">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Go to JSON Checker Dashboard', 'wpel'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render Notifications Tab
     */
    private static function render_notifications_tab() {
        $settings = get_option('wpel_settings', array());
        ?>
        <div class="wpel-docs-section">
            <h2><span class="dashicons dashicons-email-alt"></span> <?php esc_html_e('Notification Channels', 'wpel'); ?></h2>

            <div class="wpel-channels-grid">
                <div class="wpel-channel-card">
                    <div class="wpel-channel-icon" style="background: #4A154B;">
                        <span class="dashicons dashicons-format-chat"></span>
                    </div>
                    <h3><?php esc_html_e('Slack', 'wpel'); ?></h3>
                    <p><?php esc_html_e('Receive instant notifications in your Slack channel with formatted messages and context data.', 'wpel'); ?></p>
                    <?php if (!empty($settings['notify_slack_webhook_url'])): ?>
                        <span class="wpel-channel-status wpel-status-active">
                            <span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Configured', 'wpel'); ?>
                        </span>
                    <?php else: ?>
                        <span class="wpel-channel-status wpel-status-inactive">
                            <span class="dashicons dashicons-warning"></span> <?php esc_html_e('Not configured', 'wpel'); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="wpel-channel-card">
                    <div class="wpel-channel-icon" style="background: #EA4335;">
                        <span class="dashicons dashicons-email"></span>
                    </div>
                    <h3><?php esc_html_e('Email', 'wpel'); ?></h3>
                    <p><?php esc_html_e('Get email notifications for logged events matching your configured log types.', 'wpel'); ?></p>
                    <?php if (!empty($settings['notify_email'])): ?>
                        <span class="wpel-channel-status wpel-status-active">
                            <span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html($settings['notify_email']); ?>
                        </span>
                    <?php else: ?>
                        <span class="wpel-channel-status wpel-status-inactive">
                            <span class="dashicons dashicons-warning"></span> <?php esc_html_e('Not configured', 'wpel'); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="wpel-channel-card">
                    <div class="wpel-channel-icon" style="background: #2563EB;">
                        <span class="dashicons dashicons-rest-api"></span>
                    </div>
                    <h3><?php esc_html_e('Webhook', 'wpel'); ?></h3>
                    <p><?php esc_html_e('Send log data to any external service via HTTP POST with JSON payload.', 'wpel'); ?></p>
                    <?php if (!empty($settings['notify_webhook_url'])): ?>
                        <span class="wpel-channel-status wpel-status-active">
                            <span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Configured', 'wpel'); ?>
                        </span>
                    <?php else: ?>
                        <span class="wpel-channel-status wpel-status-inactive">
                            <span class="dashicons dashicons-warning"></span> <?php esc_html_e('Not configured', 'wpel'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="wpel-feature-item">
                <div class="wpel-feature-header">
                    <span class="wpel-feature-icon wpel-icon-info">
                        <span class="dashicons dashicons-filter"></span>
                    </span>
                    <h3><?php esc_html_e('Notification Triggers', 'wpel'); ?></h3>
                </div>
                <div class="wpel-feature-body">
                    <p><?php esc_html_e('Notifications are sent based on your configured log types:', 'wpel'); ?></p>
                    <div class="wpel-log-types">
                        <?php
                        $notify_types = $settings['notify_types'] ?? array('error', 'warning');
                        $all_types = array('error', 'warning', 'info', 'success');
                        foreach ($all_types as $type):
                            $is_active = in_array($type, $notify_types);
                        ?>
                        <span class="wpel-log-type <?php echo $is_active ? 'active' : 'inactive'; ?>">
                            <span class="wpel-badge wpel-badge-<?php echo esc_attr($type); ?>"><?php echo esc_html(ucfirst($type)); ?></span>
                            <?php if ($is_active): ?>
                                <span class="dashicons dashicons-yes"></span>
                            <?php else: ?>
                                <span class="dashicons dashicons-no-alt"></span>
                            <?php endif; ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="wpel-feature-item">
                <div class="wpel-feature-header">
                    <span class="wpel-feature-icon wpel-icon-success">
                        <span class="dashicons dashicons-format-chat"></span>
                    </span>
                    <h3><?php esc_html_e('Slack Message Format', 'wpel'); ?></h3>
                </div>
                <div class="wpel-feature-body">
                    <p><?php esc_html_e('Slack notifications include:', 'wpel'); ?></p>
                    <div class="wpel-slack-preview">
                        <div class="wpel-slack-message">
                            <strong>[WPEL] ERROR:</strong> Payment gateway timeout
                            <pre>{
    "order_id": 1234,
    "gateway": "stripe",
    "response_code": 504
}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wpel-cta-box">
                <a href="<?php echo esc_url(admin_url('admin.php?page=wpel-settings')); ?>" class="button button-primary button-hero">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e('Configure Notifications', 'wpel'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render Troubleshooting Tab
     */
    private static function render_troubleshooting_tab() {
        ?>
        <div class="wpel-docs-section">
            <h2><span class="dashicons dashicons-sos"></span> <?php esc_html_e('Troubleshooting & FAQ', 'wpel'); ?></h2>

            <div class="wpel-feature-list">
                <!-- FAQ 1 -->
                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-error">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                        <h3><?php esc_html_e('Slack notifications are not being sent', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <p><strong><?php esc_html_e('Possible causes:', 'wpel'); ?></strong></p>
                        <ul class="wpel-check-list">
                            <li><span class="dashicons dashicons-arrow-right-alt"></span> <?php esc_html_e('Webhook URL is not configured or invalid', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-arrow-right-alt"></span> <?php esc_html_e('Log type is not in the "Notify Types" list', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-arrow-right-alt"></span> <?php esc_html_e('Slack app permissions are not configured correctly', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-arrow-right-alt"></span> <?php esc_html_e('Outgoing HTTP requests are blocked by server firewall', 'wpel'); ?></li>
                        </ul>
                        <p><strong><?php esc_html_e('Solutions:', 'wpel'); ?></strong></p>
                        <ol class="wpel-numbered-list">
                            <li>
                                <strong><?php esc_html_e('Verify webhook URL', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Go to Settings and ensure the Slack Webhook URL is correct', 'wpel'); ?></p>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Check notify types', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Ensure "error" and "warning" are checked in Notify Types', 'wpel'); ?></p>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Test webhook manually', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Use the test code below to verify the webhook works', 'wpel'); ?></p>
                            </li>
                        </ol>
                        <div class="wpel-code-example">
                            <div class="wpel-code-header">
                                <span class="wpel-badge wpel-badge-info"><?php esc_html_e('Test Code', 'wpel'); ?></span>
                            </div>
                            <pre><code>&lt;?php
// Add this temporarily to test Slack notifications
add_action('init', function() {
    if (isset($_GET['test_wpel_slack'])) {
        wpel_log_error('Test error message', array('test' => true), true);
        echo 'Slack notification sent!';
        exit;
    }
});
// Visit: yoursite.com/?test_wpel_slack=1</code></pre>
                        </div>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-warning">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                        <h3><?php esc_html_e('JSON Checker is not auto-repairing files', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <p><strong><?php esc_html_e('Possible causes:', 'wpel'); ?></strong></p>
                        <ul class="wpel-check-list">
                            <li><span class="dashicons dashicons-arrow-right-alt"></span> <code>STATAMIC_API_URL</code> <?php esc_html_e('is not configured in .env', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-arrow-right-alt"></span> <?php esc_html_e('Statamic API is not accessible from WordPress server', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-arrow-right-alt"></span> <?php esc_html_e('Cache directory is not writable', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-arrow-right-alt"></span> <?php esc_html_e('WP-Cron is not running', 'wpel'); ?></li>
                        </ul>
                        <p><strong><?php esc_html_e('Solutions:', 'wpel'); ?></strong></p>
                        <ol class="wpel-numbered-list">
                            <li>
                                <strong><?php esc_html_e('Check .env configuration', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Ensure STATAMIC_API_URL is set correctly', 'wpel'); ?></p>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Test API connection', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Use the Cache Management plugin to test the Statamic connection', 'wpel'); ?></p>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Check directory permissions', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Ensure the cache/global directory is writable (755 or 775)', 'wpel'); ?></p>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Verify WP-Cron', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Check System Status tab to see if cron is scheduled', 'wpel'); ?></p>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-info">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                        <h3><?php esc_html_e('Logs are not appearing in the database', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <p><strong><?php esc_html_e('Possible causes:', 'wpel'); ?></strong></p>
                        <ul class="wpel-check-list">
                            <li><span class="dashicons dashicons-arrow-right-alt"></span> <?php esc_html_e('Database table was not created on activation', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-arrow-right-alt"></span> <?php esc_html_e('Deduplication is filtering out repeated errors', 'wpel'); ?></li>
                            <li><span class="dashicons dashicons-arrow-right-alt"></span> <?php esc_html_e('Capture settings are disabled', 'wpel'); ?></li>
                        </ul>
                        <p><strong><?php esc_html_e('Solutions:', 'wpel'); ?></strong></p>
                        <ol class="wpel-numbered-list">
                            <li>
                                <strong><?php esc_html_e('Check database table', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Go to System Status tab - table should show "Exists"', 'wpel'); ?></p>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Reactivate plugin', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Deactivate and reactivate the plugin to recreate the table', 'wpel'); ?></p>
                            </li>
                            <li>
                                <strong><?php esc_html_e('Check dedup settings', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Set deduplication period to 0 to disable temporarily', 'wpel'); ?></p>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-success">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                        <h3><?php esc_html_e('How do I manually trigger the JSON check?', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <p><?php esc_html_e('There are two ways to manually trigger a JSON check:', 'wpel'); ?></p>
                        <ol class="wpel-numbered-list">
                            <li>
                                <strong><?php esc_html_e('Admin Dashboard', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Go to WPEL Logs > JSON Checker and click "Check & Repair JSON Files Now"', 'wpel'); ?></p>
                            </li>
                            <li>
                                <strong><?php esc_html_e('WP-CLI', 'wpel'); ?></strong>
                                <p><?php esc_html_e('Run the cron event manually via WP-CLI', 'wpel'); ?></p>
                            </li>
                        </ol>
                        <div class="wpel-code-example">
                            <div class="wpel-code-header">
                                <span class="wpel-badge wpel-badge-default"><?php esc_html_e('WP-CLI Command', 'wpel'); ?></span>
                            </div>
                            <pre><code>wp cron event run wpel_check_global_json</code></pre>
                        </div>
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-export">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                        <h3><?php esc_html_e('How do I add custom sensitive keys to redact?', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <p><?php esc_html_e('Use the wpel_sensitive_keys filter to add your own keys:', 'wpel'); ?></p>
                        <div class="wpel-code-example">
                            <div class="wpel-code-header">
                                <span class="wpel-badge wpel-badge-info"><?php esc_html_e('Code Example', 'wpel'); ?></span>
                            </div>
                            <pre><code>&lt;?php
// Add to your theme's functions.php or a custom plugin
add_filter('wpel_sensitive_keys', function($keys) {
    // Add your custom sensitive keys
    $keys[] = 'custom_api_key';
    $keys[] = 'private_token';
    $keys[] = 'bank_account';
    return $keys;
});</code></pre>
                        </div>
                    </div>
                </div>

                <!-- FAQ 6 -->
                <div class="wpel-feature-item">
                    <div class="wpel-feature-header">
                        <span class="wpel-feature-icon wpel-icon-notify">
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                        <h3><?php esc_html_e('Can I send logs to external services?', 'wpel'); ?></h3>
                    </div>
                    <div class="wpel-feature-body">
                        <p><?php esc_html_e('Yes! Use the wpel_log_added action hook to send logs to any external service:', 'wpel'); ?></p>
                        <div class="wpel-code-example">
                            <div class="wpel-code-header">
                                <span class="wpel-badge wpel-badge-info"><?php esc_html_e('Code Example', 'wpel'); ?></span>
                            </div>
                            <pre><code>&lt;?php
// Send logs to external monitoring service
add_action('wpel_log_added', function($type, $message, $context, $log_id) {
    // Only send errors to external service
    if ($type !== 'error') return;

    wp_remote_post('https://your-monitoring-service.com/api/logs', array(
        'body' => json_encode(array(
            'type' => $type,
            'message' => $message,
            'context' => $context,
            'log_id' => $log_id,
            'site' => home_url(),
        )),
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer YOUR_API_KEY',
        ),
    ));
}, 10, 4);</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wpel-info-box wpel-info-primary" style="margin-top: 24px;">
                <span class="dashicons dashicons-info"></span>
                <div>
                    <strong><?php esc_html_e('Need more help?', 'wpel'); ?></strong>
                    <p><?php esc_html_e('Check the System Status tab to diagnose issues, or enable file logging to capture detailed error information.', 'wpel'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Status Tab
     */
    private static function render_status_tab() {
        global $wpdb;
        $table = wpel_table_name();
        $settings = get_option('wpel_settings', array());

        // Get stats
        $total_logs = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $error_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE type = %s", 'error'));
        $warning_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE type = %s", 'warning'));
        $today_logs = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s",
            gmdate('Y-m-d 00:00:00')
        ));

        // Cron status
        $purge_next = wp_next_scheduled('wpel_purge_old_logs');
        $json_check_next = wp_next_scheduled('wpel_check_global_json');

        // File logging
        $log_dir = WP_CONTENT_DIR . '/wpel-logs';
        $log_file_exists = file_exists($log_dir . '/' . gmdate('Y-m-d') . '.log');

        // JSON Checker path
        $checker = WPEL_Global_JSON_Checker::instance();
        $cache_path = $checker->get_global_cache_path();

        ?>
        <div class="wpel-docs-section">
            <h2><span class="dashicons dashicons-heart"></span> <?php esc_html_e('System Status', 'wpel'); ?></h2>

            <div class="wpel-stats-grid">
                <div class="wpel-stat-card">
                    <div class="wpel-stat-value"><?php echo number_format($total_logs); ?></div>
                    <div class="wpel-stat-label"><?php esc_html_e('Total Logs', 'wpel'); ?></div>
                </div>
                <div class="wpel-stat-card wpel-stat-error">
                    <div class="wpel-stat-value"><?php echo number_format($error_count); ?></div>
                    <div class="wpel-stat-label"><?php esc_html_e('Errors', 'wpel'); ?></div>
                </div>
                <div class="wpel-stat-card wpel-stat-warning">
                    <div class="wpel-stat-value"><?php echo number_format($warning_count); ?></div>
                    <div class="wpel-stat-label"><?php esc_html_e('Warnings', 'wpel'); ?></div>
                </div>
                <div class="wpel-stat-card wpel-stat-today">
                    <div class="wpel-stat-value"><?php echo number_format($today_logs); ?></div>
                    <div class="wpel-stat-label"><?php esc_html_e('Today', 'wpel'); ?></div>
                </div>
            </div>

            <div class="wpel-status-sections">
                <div class="wpel-status-section">
                    <h3><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e('Configuration Status', 'wpel'); ?></h3>
                    <table class="wpel-status-table">
                        <tbody>
                            <tr>
                                <td><?php esc_html_e('Database Table', 'wpel'); ?></td>
                                <td>
                                    <?php if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table): ?>
                                        <span class="wpel-status-ok"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Exists', 'wpel'); ?></span>
                                    <?php else: ?>
                                        <span class="wpel-status-error"><span class="dashicons dashicons-warning"></span> <?php esc_html_e('Missing', 'wpel'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('File Logging', 'wpel'); ?></td>
                                <td>
                                    <?php if (!empty($settings['enable_file_logging'])): ?>
                                        <span class="wpel-status-ok"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Enabled', 'wpel'); ?></span>
                                    <?php else: ?>
                                        <span class="wpel-status-neutral"><span class="dashicons dashicons-minus"></span> <?php esc_html_e('Disabled', 'wpel'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Log Directory', 'wpel'); ?></td>
                                <td>
                                    <?php if (is_writable($log_dir) || is_writable(dirname($log_dir))): ?>
                                        <span class="wpel-status-ok"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Writable', 'wpel'); ?></span>
                                    <?php else: ?>
                                        <span class="wpel-status-warning"><span class="dashicons dashicons-warning"></span> <?php esc_html_e('Not writable', 'wpel'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Cache Directory', 'wpel'); ?></td>
                                <td>
                                    <?php if (is_writable($cache_path) || is_writable(dirname($cache_path))): ?>
                                        <span class="wpel-status-ok"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Writable', 'wpel'); ?></span>
                                    <?php else: ?>
                                        <span class="wpel-status-warning"><span class="dashicons dashicons-warning"></span> <?php esc_html_e('Not writable', 'wpel'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Retention Period', 'wpel'); ?></td>
                                <td><?php echo esc_html(($settings['retention_days'] ?? 30) . ' ' . __('days', 'wpel')); ?></td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Deduplication Period', 'wpel'); ?></td>
                                <td><?php echo esc_html(($settings['dedup_period'] ?? 24) . ' ' . __('hours', 'wpel')); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="wpel-status-section">
                    <h3><span class="dashicons dashicons-clock"></span> <?php esc_html_e('Scheduled Tasks', 'wpel'); ?></h3>
                    <table class="wpel-status-table">
                        <tbody>
                            <tr>
                                <td><?php esc_html_e('Log Purge Cron', 'wpel'); ?></td>
                                <td>
                                    <?php if ($purge_next): ?>
                                        <span class="wpel-status-ok">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                            <?php echo esc_html(sprintf(__('Next: %s', 'wpel'), date('Y-m-d H:i:s', $purge_next))); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="wpel-status-warning"><span class="dashicons dashicons-warning"></span> <?php esc_html_e('Not scheduled', 'wpel'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('JSON Check Cron', 'wpel'); ?></td>
                                <td>
                                    <?php if ($json_check_next): ?>
                                        <span class="wpel-status-ok">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                            <?php echo esc_html(sprintf(__('Next: %s', 'wpel'), date('Y-m-d H:i:s', $json_check_next))); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="wpel-status-warning"><span class="dashicons dashicons-warning"></span> <?php esc_html_e('Not scheduled', 'wpel'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="wpel-status-section">
                    <h3><span class="dashicons dashicons-visibility"></span> <?php esc_html_e('Capture Sources', 'wpel'); ?></h3>
                    <table class="wpel-status-table">
                        <tbody>
                            <?php
                            $sources = array(
                                'capture_php_errors' => __('PHP Errors', 'wpel'),
                                'capture_php_exceptions' => __('PHP Exceptions', 'wpel'),
                                'capture_shutdown_fatal' => __('Fatal Errors', 'wpel'),
                                'capture_http_api_failures' => __('HTTP API Failures', 'wpel'),
                                'capture_wp_errors' => __('WP_Error Objects', 'wpel'),
                                'capture_cron_failures' => __('Cron Failures', 'wpel'),
                            );
                            foreach ($sources as $key => $label):
                                $enabled = !empty($settings[$key]);
                            ?>
                            <tr>
                                <td><?php echo esc_html($label); ?></td>
                                <td>
                                    <?php if ($enabled): ?>
                                        <span class="wpel-status-ok"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Enabled', 'wpel'); ?></span>
                                    <?php else: ?>
                                        <span class="wpel-status-neutral"><span class="dashicons dashicons-minus"></span> <?php esc_html_e('Disabled', 'wpel'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="wpel-status-section">
                    <h3><span class="dashicons dashicons-bell"></span> <?php esc_html_e('Notification Channels', 'wpel'); ?></h3>
                    <table class="wpel-status-table">
                        <tbody>
                            <tr>
                                <td><?php esc_html_e('Slack Webhook', 'wpel'); ?></td>
                                <td>
                                    <?php if (!empty($settings['notify_slack_webhook_url'])): ?>
                                        <span class="wpel-status-ok"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Configured', 'wpel'); ?></span>
                                    <?php else: ?>
                                        <span class="wpel-status-neutral"><span class="dashicons dashicons-minus"></span> <?php esc_html_e('Not configured', 'wpel'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Email', 'wpel'); ?></td>
                                <td>
                                    <?php if (!empty($settings['notify_email'])): ?>
                                        <span class="wpel-status-ok"><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html($settings['notify_email']); ?></span>
                                    <?php else: ?>
                                        <span class="wpel-status-neutral"><span class="dashicons dashicons-minus"></span> <?php esc_html_e('Not configured', 'wpel'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Webhook URL', 'wpel'); ?></td>
                                <td>
                                    <?php if (!empty($settings['notify_webhook_url'])): ?>
                                        <span class="wpel-status-ok"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Configured', 'wpel'); ?></span>
                                    <?php else: ?>
                                        <span class="wpel-status-neutral"><span class="dashicons dashicons-minus"></span> <?php esc_html_e('Not configured', 'wpel'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Statamic API URL', 'wpel'); ?></td>
                                <td>
                                    <?php
                                    $api_url = getenv('STATAMIC_API_URL') ?: ($_ENV['STATAMIC_API_URL'] ?? get_option('cmd_statamic_api_url', ''));
                                    if (!empty($api_url)): ?>
                                        <span class="wpel-status-ok"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Configured', 'wpel'); ?></span>
                                    <?php else: ?>
                                        <span class="wpel-status-neutral"><span class="dashicons dashicons-minus"></span> <?php esc_html_e('Not configured', 'wpel'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="wpel-feature-item" style="margin-top: 24px;">
                <div class="wpel-feature-header">
                    <span class="wpel-feature-icon wpel-icon-info">
                        <span class="dashicons dashicons-info-outline"></span>
                    </span>
                    <h3><?php esc_html_e('Environment Information', 'wpel'); ?></h3>
                </div>
                <div class="wpel-feature-body">
                    <table class="wpel-table">
                        <tbody>
                            <tr><td><strong><?php esc_html_e('PHP Version', 'wpel'); ?></strong></td><td><?php echo esc_html(PHP_VERSION); ?></td></tr>
                            <tr><td><strong><?php esc_html_e('WordPress Version', 'wpel'); ?></strong></td><td><?php echo esc_html(get_bloginfo('version')); ?></td></tr>
                            <tr><td><strong><?php esc_html_e('Plugin Version', 'wpel'); ?></strong></td><td><?php echo esc_html(WPEL_VERSION); ?></td></tr>
                            <tr><td><strong><?php esc_html_e('Active Theme', 'wpel'); ?></strong></td><td><?php echo esc_html(wp_get_theme()->get('Name')); ?></td></tr>
                            <tr><td><strong><?php esc_html_e('Cache Path', 'wpel'); ?></strong></td><td><code style="font-size:12px;"><?php echo esc_html($cache_path); ?></code></td></tr>
                            <tr><td><strong><?php esc_html_e('Log Path', 'wpel'); ?></strong></td><td><code style="font-size:12px;"><?php echo esc_html($log_dir); ?></code></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
}

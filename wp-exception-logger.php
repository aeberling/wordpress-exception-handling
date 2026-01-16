<?php
/**
 * Plugin Name:       WP Exception Logger (WPEL)
 * Description:       Laravel-like logging system for WordPress: DB + optional file logging, notifications, and auto-capture of errors.
 * Version:           1.0.0
 * Author:            Your Name
 * Text Domain:       wpel
 */

if (!defined('ABSPATH')) {
  exit;
}

define('WPEL_VERSION', '1.0.0');
define('WPEL_PLUGIN_FILE', __FILE__);
define('WPEL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPEL_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Return fully qualified table name with prefix.
 */
function wpel_table_name(): string {
  global $wpdb;
  return $wpdb->prefix . 'wpel_logs';
}

/**
 * Activation hook: create table and schedule retention.
 */
function wpel_activate() {
  global $wpdb;

  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  $charset_collate = $wpdb->get_charset_collate();
  $table = wpel_table_name();

  $sql = "CREATE TABLE {$table} (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    type VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    context LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY type_idx (type),
    KEY created_idx (created_at)
  ) {$charset_collate};";

  dbDelta($sql);

  // Default settings
  $defaults = array(
    'enable_file_logging' => false,
    'retention_days' => 30,
    'dedup_period' => 24,
    'notify_slack_webhook_url' => '',
    'notify_webhook_url' => '',
    'notify_email' => '',
    'notify_types' => array('error', 'warning'),
    'capture_php_errors' => true,
    'capture_php_exceptions' => true,
    'capture_shutdown_fatal' => true,
    'capture_http_api_failures' => true,
    'capture_wp_errors' => false,
    'capture_cron_failures' => true,
  );
  add_option('wpel_settings', $defaults);

  if (!wp_next_scheduled('wpel_purge_old_logs')) {
    wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'wpel_purge_old_logs');
  }
}
register_activation_hook(__FILE__, 'wpel_activate');

/**
 * Deactivation hook: unschedule cron.
 */
function wpel_deactivate() {
  $timestamp = wp_next_scheduled('wpel_purge_old_logs');
  if ($timestamp) {
    wp_unschedule_event($timestamp, 'wpel_purge_old_logs');
  }

  // Unschedule Global JSON Checker cron
  $json_checker_timestamp = wp_next_scheduled('wpel_check_global_json');
  if ($json_checker_timestamp) {
    wp_unschedule_event($json_checker_timestamp, 'wpel_check_global_json');
  }
}
register_deactivation_hook(__FILE__, 'wpel_deactivate');

// Silence direct access to includes
if (!file_exists(WPEL_PLUGIN_DIR . 'includes')) {
  mkdir(WPEL_PLUGIN_DIR . 'includes');
}

require_once WPEL_PLUGIN_DIR . 'includes/helpers.php';
require_once WPEL_PLUGIN_DIR . 'includes/class-wpel-logger.php';
require_once WPEL_PLUGIN_DIR . 'includes/class-wpel-settings.php';
require_once WPEL_PLUGIN_DIR . 'includes/class-wpel-admin.php';
require_once WPEL_PLUGIN_DIR . 'includes/class-wpel-plugin.php';
require_once WPEL_PLUGIN_DIR . 'includes/class-wpel-global-json-checker.php';
require_once WPEL_PLUGIN_DIR . 'includes/class-wpel-documentation.php';

// Bootstrap
WPEL_Plugin::instance();

// Initialize Global JSON Checker
add_action('init', function() {
    WPEL_Global_JSON_Checker::instance()->init();
});


/**
 * Enqueue admin CSS for WPEL pages.
 */
function wpel_enqueue_admin_assets($hook) {
  // Only load on plugin pages
  if (strpos($hook, 'wpel') === false) {
      return;
  }

  wp_enqueue_style(
      'wpel-admin-css',
      WPEL_PLUGIN_URL . 'assets/css/admin.css',
      array(),
      WPEL_VERSION
  );

  // Add dashicons for documentation page
  wp_enqueue_style('dashicons');
}
add_action('admin_enqueue_scripts', 'wpel_enqueue_admin_assets');

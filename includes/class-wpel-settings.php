<?php
if (!defined('ABSPATH')) { exit; }

class WPEL_Settings {
  public static function register() {
    register_setting('wpel_settings_group', 'wpel_settings', array(
      'type' => 'array',
      'sanitize_callback' => array(__CLASS__, 'sanitize'),
      'default' => array(),
    ));

    add_settings_section('wpel_main', __('Logging & Notifications', 'wpel'), function () {
      echo '<p>' . esc_html__('Configure logging destinations, notifications, and retention.', 'wpel') . '</p>';
    }, 'wpel_settings');

    add_settings_field('enable_file_logging', __('Enable File Logging', 'wpel'), array(__CLASS__, 'field_checkbox'), 'wpel_settings', 'wpel_main', array(
      'key' => 'enable_file_logging',
      'label' => __('Also write logs to /wp-content/wpel-logs', 'wpel')
    ));

    add_settings_field('retention_days', __('Retention (days)', 'wpel'), array(__CLASS__, 'field_number'), 'wpel_settings', 'wpel_main', array(
      'key' => 'retention_days',
      'min' => 1,
      'step' => 1,
    ));

    add_settings_field('notify_types', __('Notify on Types', 'wpel'), array(__CLASS__, 'field_checklist'), 'wpel_settings', 'wpel_main', array(
      'key' => 'notify_types',
      'choices' => array('error' => 'Error', 'warning' => 'Warning', 'info' => 'Info', 'success' => 'Success'),
    ));

    add_settings_field('notify_email', __('Notification Email', 'wpel'), array(__CLASS__, 'field_text'), 'wpel_settings', 'wpel_main', array(
      'key' => 'notify_email',
      'placeholder' => 'alerts@example.com',
    ));

    add_settings_field('notify_slack_webhook_url', __('Slack Webhook URL', 'wpel'), array(__CLASS__, 'field_text'), 'wpel_settings', 'wpel_main', array(
      'key' => 'notify_slack_webhook_url',
      'placeholder' => 'https://hooks.slack.com/services/...',
    ));

    add_settings_field('notify_webhook_url', __('Generic Webhook URL', 'wpel'), array(__CLASS__, 'field_text'), 'wpel_settings', 'wpel_main', array(
      'key' => 'notify_webhook_url',
      'placeholder' => 'https://example.com/webhook',
    ));

    add_settings_section('wpel_capture', __('Capture Sources', 'wpel'), function () {
      echo '<p>' . esc_html__('Enable automatic capture of events from WordPress and PHP.', 'wpel') . '</p>';
    }, 'wpel_settings');

    add_settings_field('capture_php_errors', __('PHP Errors', 'wpel'), array(__CLASS__, 'field_checkbox'), 'wpel_settings', 'wpel_capture', array(
      'key' => 'capture_php_errors', 'label' => __('Capture PHP errors (notices/warnings).', 'wpel')
    ));
    add_settings_field('capture_php_exceptions', __('PHP Exceptions', 'wpel'), array(__CLASS__, 'field_checkbox'), 'wpel_settings', 'wpel_capture', array(
      'key' => 'capture_php_exceptions', 'label' => __('Capture uncaught exceptions.', 'wpel')
    ));
    add_settings_field('capture_shutdown_fatal', __('Shutdown Fatal', 'wpel'), array(__CLASS__, 'field_checkbox'), 'wpel_settings', 'wpel_capture', array(
      'key' => 'capture_shutdown_fatal', 'label' => __('Capture fatal errors on shutdown.', 'wpel')
    ));
    add_settings_field('capture_http_api_failures', __('HTTP API Failures', 'wpel'), array(__CLASS__, 'field_checkbox'), 'wpel_settings', 'wpel_capture', array(
      'key' => 'capture_http_api_failures', 'label' => __('Capture wp_remote_*() failures.', 'wpel')
    ));
    add_settings_field('capture_wp_errors', __('WP_Error Added', 'wpel'), array(__CLASS__, 'field_checkbox'), 'wpel_settings', 'wpel_capture', array(
      'key' => 'capture_wp_errors', 'label' => __('Capture when WP_Error objects receive errors.', 'wpel')
    ));
    add_settings_field('capture_cron_failures', __('Cron Failures', 'wpel'), array(__CLASS__, 'field_checkbox'), 'wpel_settings', 'wpel_capture', array(
      'key' => 'capture_cron_failures', 'label' => __('Capture failures to spawn WP-Cron.', 'wpel')
    ));
  }

  public static function sanitize($input) {
    $out = array();
    $out['enable_file_logging'] = !empty($input['enable_file_logging']);
    $out['retention_days'] = isset($input['retention_days']) ? max(1, (int)$input['retention_days']) : 30;

    $choices = array('error','warning','info','success');
    $out['notify_types'] = array_values(array_intersect($choices, isset($input['notify_types']) && is_array($input['notify_types']) ? array_map('strval', $input['notify_types']) : array()));

    $out['notify_email'] = isset($input['notify_email']) ? sanitize_email($input['notify_email']) : '';
    $out['notify_slack_webhook_url'] = isset($input['notify_slack_webhook_url']) ? esc_url_raw($input['notify_slack_webhook_url']) : '';
    $out['notify_webhook_url'] = isset($input['notify_webhook_url']) ? esc_url_raw($input['notify_webhook_url']) : '';

    $flags = array('capture_php_errors','capture_php_exceptions','capture_shutdown_fatal','capture_http_api_failures','capture_wp_errors','capture_cron_failures');
    foreach ($flags as $f) {
      $out[$f] = !empty($input[$f]);
    }
    return $out;
  }

  // Field renderers
  public static function field_checkbox($args) {
    $settings = get_option('wpel_settings', array());
    $key = $args['key'];
    $label = isset($args['label']) ? $args['label'] : '';
    $checked = !empty($settings[$key]) ? 'checked' : '';
    echo '<label><input type="checkbox" name="wpel_settings[' . esc_attr($key) . ']" value="1" ' . $checked . ' /> ' . esc_html($label) . '</label>';
  }

  public static function field_number($args) {
    $settings = get_option('wpel_settings', array());
    $key = $args['key'];
    $val = isset($settings[$key]) ? (int)$settings[$key] : 30;
    $min = isset($args['min']) ? (int)$args['min'] : 1;
    $step = isset($args['step']) ? (int)$args['step'] : 1;
    echo '<input type="number" class="regular-text" name="wpel_settings[' . esc_attr($key) . ']" value="' . esc_attr($val) . '" min="' . esc_attr($min) . '" step="' . esc_attr($step) . '">';
  }

  public static function field_text($args) {
    $settings = get_option('wpel_settings', array());
    $key = $args['key'];
    $val = isset($settings[$key]) ? (string)$settings[$key] : '';
    $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
    echo '<input type="text" class="regular-text" name="wpel_settings[' . esc_attr($key) . ']" value="' . esc_attr($val) . '" placeholder="' . esc_attr($placeholder) . '">';
  }

  public static function field_checklist($args) {
    $settings = get_option('wpel_settings', array());
    $key = $args['key'];
    $selected = isset($settings[$key]) && is_array($settings[$key]) ? $settings[$key] : array();

    foreach ($args['choices'] as $value => $label) {
      $checked = in_array($value, $selected, true) ? 'checked' : '';
      echo '<label style="display:inline-block;margin-right:12px;"><input type="checkbox" name="wpel_settings[' . esc_attr($key) . '][]" value="' . esc_attr($value) . '" ' . $checked . '> ' . esc_html($label) . '</label>';
    }
  }
}

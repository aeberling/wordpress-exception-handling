<?php
if (!defined('ABSPATH')) { exit; }

class WPEL_Logger {
  private static $instance = null;

  public static function instance(): self {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function log(string $type, string $message, $context = array(), bool $slack_alert = false) {
    $type = strtolower($type);
    $allowed_types = apply_filters('wpel_log_types', array('error','warning','info','success'));
    if (!in_array($type, $allowed_types, true)) {
      $type = 'info';
    }

    $sanitized = $this->sanitize_context($context);
    $message = $this->augment_message_with_file_context($message, $sanitized);
    $payload_json = !empty($sanitized) ? wp_json_encode($sanitized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

    global $wpdb;
    $table = wpel_table_name();

    $inserted = $wpdb->insert(
      $table,
      array(
        'type' => $type,
        'message' => wp_strip_all_tags($message),
        'context' => $payload_json,
        'created_at' => current_time('mysql', 1),
      ),
      array('%s', '%s', '%s', '%s')
    );

    $log_id = $inserted ? (int)$wpdb->insert_id : 0;

    // Optional file logging
    $settings = get_option('wpel_settings', array());
    if (!empty($settings['enable_file_logging'])) {
      $this->file_log($type, $message, $sanitized);
    }

    /**
     * Extensibility: Fire an action when a log is added.
     */
    do_action('wpel_log_added', $type, $message, $sanitized, $log_id);

    // Notifications
    $this->maybe_notify($type, $message, $sanitized, $slack_alert);

    return $log_id;
  }

  private function sanitize_context($context) {
    // Normalize to array
    if (is_wp_error($context)) {
      $context = array(
        'code' => $context->get_error_code(),
        'message' => $context->get_error_message(),
        'data' => $context->get_error_data(),
        'all' => $context->errors,
      );
    } elseif (is_object($context)) {
      $context = json_decode(wp_json_encode($context), true);
    } elseif (!is_array($context)) {
      $context = array('value' => (string)$context);
    }

    $patterns = apply_filters('wpel_sensitive_keys', array(
      'password', 'pass', 'pwd',
      'secret', 'token', 'access_token', 'refresh_token',
      'api_key', 'apikey', 'api-key',
      'authorization', 'cookie', 'set-cookie',
      'session', 'credit_card', 'cc',
      'client_secret', 'private_key', 'ssh_key',
    ));

    $scrub = function (&$value, $key) use (&$scrub, $patterns) {
      $key_lower = strtolower((string)$key);
      foreach ($patterns as $p) {
        if (strpos($key_lower, $p) !== false) {
          $value = '[REDACTED]';
          return;
        }
      }
      if (is_array($value)) {
        array_walk($value, $scrub);
      } elseif (is_string($value)) {
        // Scrub common bearer tokens within strings
        $value = preg_replace('/(Bearer\s+)[A-Za-z0-9\-\._~\+\/]+=*/i', '$1[REDACTED]', $value);
        $value = preg_replace('/(Authorization:\s*)(.+)/i', '$1[REDACTED]', $value);
      }
    };

    array_walk($context, $scrub);

    /**
     * Filter the sanitized context before saving.
     */
    return apply_filters('wpel_sanitize_context', $context);
  }

  private function augment_message_with_file_context(string $message, array $context): string {
    $fileData = $this->extract_file_context($context);
    if (empty($fileData)) {
      return $message;
    }

    $file = isset($fileData['file']) ? (string)$fileData['file'] : '';
    $line = isset($fileData['line']) ? (int)$fileData['line'] : 0;

    if ($file === '') {
      return $message;
    }

    $suffix = $line > 0 ? sprintf(' (in %s:%d)', $file, $line) : sprintf(' (in %s)', $file);

    if (strpos($message, $suffix) !== false || strpos($message, $file) !== false) {
      return $message;
    }

    return $message . $suffix;
  }

  private function extract_file_context($context) {
    if (!is_array($context)) {
      return null;
    }

    if (isset($context['file'])) {
      return array(
        'file' => $context['file'],
        'line' => isset($context['line']) ? $context['line'] : null,
      );
    }

    foreach ($context as $value) {
      if (is_array($value)) {
        $found = $this->extract_file_context($value);
        if ($found) {
          return $found;
        }
      }
    }

    return null;
  }

  private function file_log(string $type, string $message, array $context) {
    $dir = apply_filters('wpel_file_log_dir', WP_CONTENT_DIR . '/wp-content' === WP_CONTENT_DIR ? WP_CONTENT_DIR . '/wpel-logs' : WP_CONTENT_DIR . '/wpel-logs');
    if (!file_exists($dir)) {
      wp_mkdir_p($dir);
    }
    $file = apply_filters('wpel_file_log_path', $dir . '/' . gmdate('Y-m-d') . '.log');

    $line = array(
      'ts' => gmdate('c'),
      'type' => $type,
      'message' => $message,
      'context' => $context,
    );
    $json = wp_json_encode($line, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json) {
      // Best effort, suppress warnings
      @file_put_contents($file, $json . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
  }

  private function maybe_notify(string $type, string $message, array $context, bool $slack_alert) {
    $settings = get_option('wpel_settings', array());
    $notify_types = isset($settings['notify_types']) && is_array($settings['notify_types']) ? $settings['notify_types'] : array('error','warning');

    $should = in_array($type, $notify_types, true);
    $should = apply_filters('wpel_should_notify', $should, $type, $message, $context, $settings);
    if (!$should && !$slack_alert) return;

    $text = sprintf('[WPEL] %s: %s', strtoupper($type), $message);
    $payload = array(
      'type' => $type,
      'message' => $message,
      'context' => $context,
      'site' => home_url(),
      'ts' => gmdate('c'),
    );
    $payload = apply_filters('wpel_notification_payload', $payload, $type, $message, $context);

    // Slack webhook
    $should_slack = $slack_alert && !empty($settings['notify_slack_webhook_url']);
    if ($should_slack) {
      $slack_body = array('text' => $text . (empty($context) ? '' : "\n```" . wp_json_encode($context, JSON_PRETTY_PRINT) . "```"));
      wp_remote_post($settings['notify_slack_webhook_url'], array(
        'timeout' => 5,
        'headers' => array('Content-Type' => 'application/json'),
        'body' => wp_json_encode($slack_body),
      ));
    }

    // Generic webhook
    if ($should && !empty($settings['notify_webhook_url'])) {
      wp_remote_post($settings['notify_webhook_url'], array(
        'timeout' => 5,
        'headers' => array('Content-Type' => 'application/json'),
        'body' => wp_json_encode($payload),
      ));
    }

    // Email
    if ($should && !empty($settings['notify_email']) && is_email($settings['notify_email'])) {
      $subject = sprintf('[WPEL] %s on %s', ucfirst($type), wp_parse_url(home_url(), PHP_URL_HOST));
      $body = $text . "\n\n" . (!empty($context) ? wp_json_encode($context, JSON_PRETTY_PRINT) : '');
      wp_mail($settings['notify_email'], $subject, $body);
    }
  }

  public function purge_old_logs() {
    $settings = get_option('wpel_settings', array());
    $days = isset($settings['retention_days']) ? (int)$settings['retention_days'] : 30;
    $days = max(1, $days);

    global $wpdb;
    $table = wpel_table_name();
    $wpdb->query(
      $wpdb->prepare(
        "DELETE FROM {$table} WHERE created_at < (UTC_TIMESTAMP() - INTERVAL %d DAY)",
        $days
      )
    );
  }
}

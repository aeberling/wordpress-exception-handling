<?php
if (!defined('ABSPATH')) { exit; }

class WPEL_Plugin {
  private static $instance = null;

  private $prev_error_handler = null;
  private $prev_exception_handler = null;

  // Track logged errors to prevent duplicates
  private $logged_errors = array();

  // Flag to prevent recursive error handling
  private $is_handling_error = false;

  /**
   * Generate a normalized key for deduplication
   */
  private function get_error_key($type, $message, $file = '', $line = 0, $extra = '') {
    // Normalize file path (lowercase, forward slashes)
    $file = strtolower(str_replace('\\', '/', trim($file)));
    // Normalize message (trim whitespace, remove file/line references that might vary)
    $message = trim($message);
    // Remove any (in filepath:line) suffix that WordPress adds
    $message = preg_replace('/\s*\(in\s+[^)]+\)\s*$/i', '', $message);
    // Remove version info that might vary
    $message = preg_replace('/\s*\(This message was added in version [^)]+\)\s*/i', '', $message);

    return md5($type . ':' . $file . ':' . intval($line) . ':' . $message . ':' . $extra);
  }

  /**
   * Check if error was already logged (within dedup period), mark as logged if not
   */
  private function is_duplicate($error_key) {
    // First check in-memory array (same request)
    if (isset($this->logged_errors[$error_key])) {
      return true;
    }

    // Get dedup period from settings (in hours)
    $settings = get_option('wpel_settings', array());
    $dedup_hours = isset($settings['dedup_period']) ? (int)$settings['dedup_period'] : 24;

    // If dedup is disabled (0), only do in-memory dedup
    if ($dedup_hours <= 0) {
      $this->logged_errors[$error_key] = true;
      return false;
    }

    // Check transient for cross-request deduplication
    $transient_key = 'wpel_dedup_' . substr($error_key, 0, 32);
    $existing = get_transient($transient_key);

    if ($existing !== false) {
      // Already logged within dedup period
      $this->logged_errors[$error_key] = true;
      return true;
    }

    // Mark as logged in memory and set transient
    $this->logged_errors[$error_key] = true;
    set_transient($transient_key, time(), $dedup_hours * HOUR_IN_SECONDS);

    return false;
  }

  public static function instance(): self {
    if (self::$instance === null) {
      self::$instance = new self();
      self::$instance->init();
    }
    return self::$instance;
  }

  public function init() {
    // Admin pages & settings
    add_action('admin_menu', array('WPEL_Admin', 'add_menus'));
    add_action('admin_init', array('WPEL_Settings', 'register'));

    // Retention cron
    add_action('wpel_purge_old_logs', array(WPEL_Logger::instance(), 'purge_old_logs'));

    // Setup error captures immediately (not on plugins_loaded) to catch early warnings
    $this->maybe_setup_captures();
  }

  public function maybe_setup_captures() {
    $settings = get_option('wpel_settings', array());

    if (!empty($settings['capture_php_errors'])) {
      $this->prev_error_handler = set_error_handler(array($this, 'capture_php_error'));
    }

    if (!empty($settings['capture_php_exceptions'])) {
      $this->prev_exception_handler = set_exception_handler(array($this, 'capture_exception'));
    }

    if (!empty($settings['capture_shutdown_fatal'])) {
      register_shutdown_function(array($this, 'capture_shutdown'));
    }

    if (!empty($settings['capture_http_api_failures'])) {
      add_action('http_api_debug', array($this, 'capture_http_api_debug'), 10, 5);
    }

    if (!empty($settings['capture_wp_errors'])) {
      add_action('wp_error_added', array($this, 'capture_wp_error_added'), 10, 4);
    }

    if (!empty($settings['capture_cron_failures'])) {
      add_action('wp_cron_failed', array($this, 'capture_cron_failed'));
    }
  }

  public function capture_php_error($errno, $errstr, $errfile, $errline) {
    // Prevent recursive error handling (if logging causes an error)
    if ($this->is_handling_error) {
      return false;
    }

    // Skip only @ suppressed errors (not when error_reporting is globally off)
    // In PHP 8+, @ sets error_reporting to E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE
    // In PHP 7.x, @ sets it to 0. We detect @ by checking if the specific errno is masked out
    $current_reporting = error_reporting();
    if ($current_reporting !== 0 && !($current_reporting & $errno)) {
      // This specific error type is suppressed (likely by @), skip it
      return false;
    }

    // Create unique key to prevent duplicate logging
    $error_key = $this->get_error_key('php_error', $errstr, $errfile, $errline, $errno);
    if ($this->is_duplicate($error_key)) {
      return true; // Already logged, suppress display
    }

    $type = 'warning';
    switch ($errno) {
      case E_ERROR:
      case E_USER_ERROR:
      case E_RECOVERABLE_ERROR:
        $type = 'error'; break;
      case E_WARNING:
      case E_USER_WARNING:
      case E_COMPILE_WARNING:
      case E_CORE_WARNING:
      case E_STRICT:
        $type = 'warning'; break;
      case E_NOTICE:
      case E_USER_NOTICE:
      case E_DEPRECATED:
      case E_USER_DEPRECATED:
        $type = 'info'; break;
      default:
        $type = 'info'; break;
    }

    // Set flag to prevent recursive handling
    $this->is_handling_error = true;

    try {
      wpel_log($type, $errstr, array(
        'file' => $errfile,
        'line' => $errline,
        'errno' => $errno,
      ));
    } catch (Exception $e) {
      // Silently fail if logging fails
    }

    $this->is_handling_error = false;

    // Call previous handler if exists
    if (is_callable($this->prev_error_handler)) {
      call_user_func($this->prev_error_handler, $errno, $errstr, $errfile, $errline);
    }

    // Return true to suppress PHP's default error display
    return true;
  }

  public function capture_exception($exception) {
    // Prevent recursive handling
    if ($this->is_handling_error) {
      if (is_callable($this->prev_exception_handler)) {
        call_user_func($this->prev_exception_handler, $exception);
      }
      return;
    }

    // Create unique key to prevent duplicate logging
    $error_key = $this->get_error_key('exception', $exception->getMessage(), $exception->getFile(), $exception->getLine());
    if ($this->is_duplicate($error_key)) {
      if (is_callable($this->prev_exception_handler)) {
        call_user_func($this->prev_exception_handler, $exception);
      }
      return;
    }

    $this->is_handling_error = true;

    try {
      wpel_log_error('Uncaught Exception: ' . $exception->getMessage(), array(
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'code' => $exception->getCode(),
        'trace' => wp_debug_backtrace_summary(null, 20),
      ));
    } catch (Exception $e) {
      // Silently fail
    }

    $this->is_handling_error = false;

    if (is_callable($this->prev_exception_handler)) {
      call_user_func($this->prev_exception_handler, $exception);
    }
  }

  public function capture_shutdown() {
    $error = error_get_last();
    if ($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR), true)) {
      // Check if this error was already logged
      $error_key = $this->get_error_key('shutdown', $error['message'], $error['file'], $error['line'], $error['type']);
      if ($this->is_duplicate($error_key)) {
        return;
      }

      $this->is_handling_error = true;

      try {
        wpel_log_error('Fatal Error on shutdown', array(
          'message' => $error['message'],
          'file' => $error['file'],
          'line' => $error['line'],
          'type' => $error['type'],
        ));
      } catch (Exception $e) {
        // Silently fail
      }

      $this->is_handling_error = false;
    }
  }

  public function capture_http_api_debug($response, $type, $class, $args, $url) {
    if ($type !== 'response') return;

    if (is_wp_error($response)) {
      // Deduplication check
      $error_key = $this->get_error_key('http_error', $response->get_error_message(), $url, 0, $response->get_error_code());
      if ($this->is_duplicate($error_key)) {
        return;
      }

      wpel_log_error('HTTP API request failed', array(
        'url' => $url,
        'args' => $args,
        'error' => array(
          'code' => $response->get_error_code(),
          'message' => $response->get_error_message(),
          'data' => $response->get_error_data(),
        ),
      ));
      return;
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code >= 400) {
      // Deduplication check
      $error_key = $this->get_error_key('http_status', 'HTTP ' . $code, $url, 0, $code);
      if ($this->is_duplicate($error_key)) {
        return;
      }

      wpel_log_warning('HTTP API non-2xx response', array(
        'url' => $url,
        'status' => $code,
        'response' => array(
          'headers' => wp_remote_retrieve_headers($response),
          'body_excerpt' => substr((string)wp_remote_retrieve_body($response), 0, 512),
        ),
      ));
    }
  }

  public function capture_wp_error_added($code, $message, $data, $wp_error) {
    // Deduplication check
    $error_key = $this->get_error_key('wp_error', $message, '', 0, $code);
    if ($this->is_duplicate($error_key)) {
      return;
    }

    // This can be noisy; keep as info unless code suggests error.
    $type = stripos((string)$code, 'error') !== false ? 'error' : 'info';
    wpel_log($type, 'WP_Error added: ' . $message, array(
      'code' => $code,
      'data' => $data,
    ));
  }

  public function capture_cron_failed($result = null) {
    // $result is WP_Error
    if (is_wp_error($result)) {
      // Deduplication check
      $error_key = $this->get_error_key('cron_failed', $result->get_error_message(), '', 0, $result->get_error_code());
      if ($this->is_duplicate($error_key)) {
        return;
      }

      wpel_log_error('WP-Cron spawn failed', array(
        'code' => $result->get_error_code(),
        'message' => $result->get_error_message(),
        'data' => $result->get_error_data(),
      ));
    } else {
      // Deduplication check for unknown errors
      $error_key = $this->get_error_key('cron_failed', 'unknown', '', 0, '');
      if ($this->is_duplicate($error_key)) {
        return;
      }

      wpel_log_error('WP-Cron spawn failed (unknown error)', array());
    }
  }
}

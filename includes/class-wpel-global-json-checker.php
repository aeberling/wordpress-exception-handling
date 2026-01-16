<?php
/**
 * WPEL Global JSON Checker
 *
 * Validates global JSON cache files and auto-repairs them from Statamic API if corrupted.
 * Sends Slack notifications when files are corrected.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WPEL_Global_JSON_Checker {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Path to global cache directory
     */
    private $global_cache_path;

    /**
     * Mapping of JSON files to their Statamic API handles
     */
    private $file_handle_map = array(
        'globalsetting.json'          => 'jovie_global_setting',
        'seogloablsetting.json'       => 'seo_global_settings',
        'globalchildcareservices.json' => 'childcare_services',
        'globalmicrosites.json'       => 'microsites',
    );

    /**
     * Get singleton instance
     */
    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->global_cache_path = get_stylesheet_directory() . '/resources/views/landingPages/cache/global';
    }

    /**
     * Initialize hooks
     */
    public function init() {
        // Register cron hook
        add_action('wpel_check_global_json', array($this, 'check_all_global_json_files'));

        // Schedule cron if not already scheduled
        if (!wp_next_scheduled('wpel_check_global_json')) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', 'wpel_check_global_json');
        }

        // Add admin action for manual check
        add_action('wp_ajax_wpel_check_global_json', array($this, 'ajax_check_global_json'));
    }

    /**
     * Unschedule cron on deactivation
     */
    public static function deactivate() {
        $timestamp = wp_next_scheduled('wpel_check_global_json');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'wpel_check_global_json');
        }
    }

    /**
     * Check all global JSON files
     *
     * @return array Results of the check
     */
    public function check_all_global_json_files(): array {
        $results = array(
            'checked' => 0,
            'valid' => 0,
            'invalid' => 0,
            'repaired' => 0,
            'failed' => 0,
            'details' => array(),
        );

        foreach ($this->file_handle_map as $filename => $handle) {
            $file_path = $this->global_cache_path . '/' . $filename;
            $result = $this->check_and_repair_json_file($file_path, $filename, $handle);

            $results['checked']++;
            $results['details'][$filename] = $result;

            if ($result['valid']) {
                $results['valid']++;
            } else {
                $results['invalid']++;
                if ($result['repaired']) {
                    $results['repaired']++;
                } else {
                    $results['failed']++;
                }
            }
        }

        return $results;
    }

    /**
     * Check a single JSON file and repair if needed
     *
     * @param string $file_path Full path to the JSON file
     * @param string $filename Name of the file
     * @param string $handle Statamic API handle
     * @return array Result of the check
     */
    public function check_and_repair_json_file(string $file_path, string $filename, string $handle): array {
        $result = array(
            'file' => $filename,
            'path' => $file_path,
            'exists' => false,
            'valid' => false,
            'error' => null,
            'repaired' => false,
            'repair_error' => null,
        );

        // Check if file exists
        if (!file_exists($file_path)) {
            $result['error'] = 'File does not exist';
            $result = $this->attempt_repair($result, $handle, $filename);
            return $result;
        }

        $result['exists'] = true;

        // Read file contents
        $content = @file_get_contents($file_path);

        if ($content === false) {
            $result['error'] = 'Failed to read file';
            $result = $this->attempt_repair($result, $handle, $filename);
            return $result;
        }

        // Check if content is empty
        if (empty(trim($content))) {
            $result['error'] = 'File is empty';
            $result = $this->attempt_repair($result, $handle, $filename);
            return $result;
        }

        // Validate JSON
        $decoded = json_decode($content, true);
        $json_error = json_last_error();

        if ($json_error !== JSON_ERROR_NONE) {
            $result['error'] = $this->get_json_error_message($json_error);
            $result = $this->attempt_repair($result, $handle, $filename);
            return $result;
        }

        // Additional validation: check if decoded data is reasonable
        if (!is_array($decoded) && !is_object($decoded)) {
            $result['error'] = 'JSON decoded to invalid type (expected array or object)';
            $result = $this->attempt_repair($result, $handle, $filename);
            return $result;
        }

        // File is valid
        $result['valid'] = true;
        return $result;
    }

    /**
     * Attempt to repair a JSON file by fetching from Statamic API
     *
     * @param array $result Current result array
     * @param string $handle Statamic API handle
     * @param string $filename Name of the file
     * @return array Updated result array
     */
    private function attempt_repair(array $result, string $handle, string $filename): array {
        // Log the error
        $this->log_error($filename, $result['error']);

        // Fetch from API
        $api_data = $this->fetch_from_statamic_api($handle);

        if (is_wp_error($api_data)) {
            $result['repair_error'] = $api_data->get_error_message();

            // Send Slack failure notification
            $this->send_slack_failure_notification($filename, $result['error'], $result['repair_error']);

            // Log failure
            $this->log_repair_failure($filename, $result['repair_error']);
            return $result;
        }

        // Save the repaired file
        $save_result = $this->save_json_file($filename, $api_data);

        if ($save_result['success']) {
            $result['repaired'] = true;
            $result['valid'] = true;

            // Send Slack success notification
            $this->send_slack_notification($filename, $result['error']);

            // Log success
            $this->log_repair_success($filename, $result['error']);
        } else {
            $result['repair_error'] = $save_result['error'];

            // Send Slack failure notification
            $this->send_slack_failure_notification($filename, $result['error'], $result['repair_error']);

            // Log failure
            $this->log_repair_failure($filename, $result['repair_error']);
        }

        return $result;
    }

    /**
     * Fetch global set data from Statamic API
     *
     * @param string $handle The Statamic global set handle
     * @return array|WP_Error The fetched data or error
     */
    private function fetch_from_statamic_api(string $handle) {
        $api_url = getenv('STATAMIC_API_URL') ?: ($_ENV['STATAMIC_API_URL'] ?? get_option('cmd_statamic_api_url', ''));
        $api_token = getenv('STATAMIC_API_TOKEN') ?: ($_ENV['STATAMIC_API_TOKEN'] ?? get_option('cmd_statamic_api_token', ''));

        if (empty($api_url)) {
            return new WP_Error('no_api_url', 'Statamic API URL is not configured');
        }

        $url = trailingslashit($api_url) . 'global-sets/' . $handle;

        $headers = array(
            'Accept' => 'application/json',
        );

        if (!empty($api_token)) {
            $headers['Authorization'] = 'Bearer ' . $api_token;
        }

        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => $headers,
            'sslverify' => false,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            return new WP_Error(
                'api_error',
                sprintf('API returned status code %d: %s', $response_code, substr($body, 0, 200))
            );
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_parse_error', 'Failed to parse API response as JSON');
        }

        return $data;
    }

    /**
     * Save JSON data to file
     *
     * @param string $filename The filename
     * @param array $data The data to save
     * @return array Result with success status
     */
    private function save_json_file(string $filename, array $data): array {
        // Ensure directory exists
        if (!file_exists($this->global_cache_path)) {
            if (!wp_mkdir_p($this->global_cache_path)) {
                return array(
                    'success' => false,
                    'error' => 'Failed to create cache directory',
                );
            }
        }

        $file_path = $this->global_cache_path . '/' . $filename;

        // Encode JSON (minified, matching original format)
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return array(
                'success' => false,
                'error' => 'Failed to encode data as JSON',
            );
        }

        // Write file with locking
        $result = @file_put_contents($file_path, $json, LOCK_EX);

        if ($result === false) {
            return array(
                'success' => false,
                'error' => 'Failed to write file',
            );
        }

        return array(
            'success' => true,
            'bytes_written' => $result,
        );
    }

    /**
     * Send Slack notification about repaired file
     *
     * @param string $filename The repaired filename
     * @param string $original_error The original error that was fixed
     */
    private function send_slack_notification(string $filename, string $original_error): void {
        $settings = get_option('wpel_settings', array());
        $webhook_url = $settings['notify_slack_webhook_url'] ?? '';

        if (empty($webhook_url)) {
            return;
        }

        $site_url = home_url();
        $site_name = get_bloginfo('name');

        // Build Slack blocks for better formatting
        $blocks = array(
            array(
                'type' => 'header',
                'text' => array(
                    'type' => 'plain_text',
                    'text' => '⚠️ JSON File Invalid - Auto Fixed',
                    'emoji' => true,
                ),
            ),
            array(
                'type' => 'section',
                'text' => array(
                    'type' => 'mrkdwn',
                    'text' => sprintf(
                        "*`%s`* was found to be invalid and has been automatically fixed!\n\n" .
                        "The file was resynced from Statamic API.",
                        $filename
                    ),
                ),
            ),
            array(
                'type' => 'divider',
            ),
            array(
                'type' => 'section',
                'fields' => array(
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Error Found:*\n" . $original_error,
                    ),
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Status:*\n✅ Fixed Successfully",
                    ),
                ),
            ),
            array(
                'type' => 'section',
                'fields' => array(
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Site:*\n" . $site_name,
                    ),
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Time:*\n" . current_time('Y-m-d H:i:s'),
                    ),
                ),
            ),
            array(
                'type' => 'context',
                'elements' => array(
                    array(
                        'type' => 'mrkdwn',
                        'text' => '🔧 WPEL Global JSON Checker | ' . $site_url,
                    ),
                ),
            ),
        );

        $payload = array(
            'text' => sprintf('⚠️ %s was invalid and has been fixed!', $filename),
            'blocks' => $blocks,
            'username' => 'WPEL JSON Checker',
            'icon_emoji' => ':warning:',
        );

        wp_remote_post($webhook_url, array(
            'timeout' => 10,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode($payload),
        ));
    }

    /**
     * Send Slack notification about failed repair
     *
     * @param string $filename The filename that failed
     * @param string $original_error The original error
     * @param string $repair_error The repair error
     */
    private function send_slack_failure_notification(string $filename, string $original_error, string $repair_error): void {
        $settings = get_option('wpel_settings', array());
        $webhook_url = $settings['notify_slack_webhook_url'] ?? '';

        if (empty($webhook_url)) {
            return;
        }

        $site_url = home_url();
        $site_name = get_bloginfo('name');

        $blocks = array(
            array(
                'type' => 'header',
                'text' => array(
                    'type' => 'plain_text',
                    'text' => '🚨 JSON File Invalid - Repair FAILED',
                    'emoji' => true,
                ),
            ),
            array(
                'type' => 'section',
                'text' => array(
                    'type' => 'mrkdwn',
                    'text' => sprintf(
                        "*`%s`* was found to be invalid and *could NOT be repaired automatically*!\n\n" .
                        "Manual intervention required.",
                        $filename
                    ),
                ),
            ),
            array(
                'type' => 'divider',
            ),
            array(
                'type' => 'section',
                'fields' => array(
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Original Error:*\n" . $original_error,
                    ),
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Repair Error:*\n" . $repair_error,
                    ),
                ),
            ),
            array(
                'type' => 'section',
                'fields' => array(
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Site:*\n" . $site_name,
                    ),
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Time:*\n" . current_time('Y-m-d H:i:s'),
                    ),
                ),
            ),
            array(
                'type' => 'context',
                'elements' => array(
                    array(
                        'type' => 'mrkdwn',
                        'text' => '🔧 WPEL Global JSON Checker | ' . $site_url,
                    ),
                ),
            ),
        );

        $payload = array(
            'text' => sprintf('🚨 CRITICAL: %s is invalid and repair FAILED!', $filename),
            'blocks' => $blocks,
            'username' => 'WPEL JSON Checker',
            'icon_emoji' => ':rotating_light:',
        );

        wp_remote_post($webhook_url, array(
            'timeout' => 10,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode($payload),
        ));
    }

    /**
     * Log error to WPEL
     *
     * @param string $filename The filename with error
     * @param string $error The error message
     */
    private function log_error(string $filename, string $error): void {
        if (function_exists('wpel_log_warning')) {
            wpel_log_warning(
                sprintf('Global JSON file error detected: %s', $filename),
                array(
                    'file' => $filename,
                    'path' => $this->global_cache_path . '/' . $filename,
                    'error' => $error,
                )
            );
        }
    }

    /**
     * Log successful repair to WPEL
     *
     * @param string $filename The repaired filename
     * @param string $original_error The original error
     */
    private function log_repair_success(string $filename, string $original_error): void {
        if (function_exists('wpel_log_success')) {
            wpel_log_success(
                sprintf('Global JSON file repaired: %s', $filename),
                array(
                    'file' => $filename,
                    'original_error' => $original_error,
                    'action' => 'Resynced from Statamic API',
                ),
                true // Send Slack alert
            );
        }
    }

    /**
     * Log repair failure to WPEL
     *
     * @param string $filename The filename
     * @param string $error The repair error
     */
    private function log_repair_failure(string $filename, string $error): void {
        if (function_exists('wpel_log_error')) {
            wpel_log_error(
                sprintf('Failed to repair global JSON file: %s', $filename),
                array(
                    'file' => $filename,
                    'repair_error' => $error,
                ),
                true // Send Slack alert
            );
        }
    }

    /**
     * Get human-readable JSON error message
     *
     * @param int $error_code JSON error code
     * @return string Error message
     */
    private function get_json_error_message(int $error_code): string {
        $errors = array(
            JSON_ERROR_NONE => 'No error',
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters',
            JSON_ERROR_RECURSION => 'Recursive reference found',
            JSON_ERROR_INF_OR_NAN => 'Inf or NaN value found',
            JSON_ERROR_UNSUPPORTED_TYPE => 'Unsupported type',
            JSON_ERROR_INVALID_PROPERTY_NAME => 'Invalid property name',
            JSON_ERROR_UTF16 => 'Malformed UTF-16 characters',
        );

        return $errors[$error_code] ?? 'Unknown JSON error (code: ' . $error_code . ')';
    }

    /**
     * AJAX handler for manual JSON check
     */
    public function ajax_check_global_json(): void {
        // Verify nonce and capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        check_ajax_referer('wpel_nonce', 'nonce');

        $results = $this->check_all_global_json_files();

        wp_send_json_success($results);
    }

    /**
     * Get the global cache path
     *
     * @return string
     */
    public function get_global_cache_path(): string {
        return $this->global_cache_path;
    }

    /**
     * Get the file handle mapping
     *
     * @return array
     */
    public function get_file_handle_map(): array {
        return $this->file_handle_map;
    }

    /**
     * Check a specific file by filename
     *
     * @param string $filename The filename to check
     * @return array|WP_Error Result or error if file not in mapping
     */
    public function check_specific_file(string $filename) {
        if (!isset($this->file_handle_map[$filename])) {
            return new WP_Error('invalid_file', 'File is not in the global JSON file mapping');
        }

        $handle = $this->file_handle_map[$filename];
        $file_path = $this->global_cache_path . '/' . $filename;

        return $this->check_and_repair_json_file($file_path, $filename, $handle);
    }
}

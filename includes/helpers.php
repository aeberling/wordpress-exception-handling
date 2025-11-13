<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Public helper functions for developers.
 * These avoid conflicts with built-in PHP functions by using the wpel_ prefix.
 */

function wpel_log(string $type, string $message, $context = array(), bool $slack_alert = false) {
  return WPEL_Logger::instance()->log($type, $message, $context, $slack_alert);
}

function wpel_log_error(string $message, $context = array(), bool $slack_alert = false) {
  return wpel_log('error', $message, $context, $slack_alert);
}

function wpel_log_warning(string $message, $context = array(), bool $slack_alert = false) {
  return wpel_log('warning', $message, $context, $slack_alert);
}

function wpel_log_info(string $message, $context = array(), bool $slack_alert = false) {
  return wpel_log('info', $message, $context, $slack_alert);
}

function wpel_log_success(string $message, $context = array(), bool $slack_alert = false) {
  return wpel_log('success', $message, $context, $slack_alert);
}

# WP Exception Logger (WPEL)

Laravel-like logging utilities for WordPress with database storage, optional file output, and multiple notification channels.

## Key Features

- **Database-backed logs**: Creates a dedicated `wp_wpel_logs` (with your table prefix) table storing type, message, context JSON, and timestamps.
- **Retention management**: Daily cron job purges entries older than the configured retention window.
- **File logging**: Optional JSON-lines files in `wp-content/wpel-logs` (filterable path/directory) for external shipping or tailing.
- **Context scrubbing**: Automatic redaction of sensitive fields (`password`, `token`, etc.) and bearer tokens before persistence.
- **Log notifications**: Configurable alerts via Slack incoming webhook, generic webhook, and/or email, limited to selected log severities.
- **Developer helpers**: Global functions `wpel_log()`, `wpel_log_error()`, `wpel_log_warning()`, `wpel_log_info()`, and `wpel_log_success()` wrap the logger singleton.
- **Extensibility hooks**: Filters (`wpel_log_types`, `wpel_sensitive_keys`, `wpel_notification_payload`, `wpel_should_notify`, `wpel_file_log_dir`, `wpel_file_log_path`, etc.) and the `wpel_log_added` action for integration.

## Automatic Capture Sources

Enable or disable each capture under **WPEL → Settings → Capture Sources**:

- **PHP errors**: Custom error handler maps PHP error numbers to log levels and records file/line metadata.
- **Uncaught exceptions**: Exception handler logs message, file, line, code, and backtrace summary.
- **Shutdown fatals**: Shutdown hook logs fatal, parse, and core errors detected via `error_get_last()`.
- **HTTP API failures**: Listens to `http_api_debug`; logs WP_Error responses and 4xx/5xx responses with status, headers, and body excerpt.
- **WP_Error additions**: Hooks `wp_error_added` to note when errors are added to WP_Error instances.
- **WP-Cron failures**: Hooks `wp_cron_failed` to capture spawn issues, preserving WP_Error details if available.

## Admin Interface

- **Menu pages**: Adds `WPEL Logs` top-level menu with `Logs` and `Settings` screens for administrators.
- **Log viewer**: Filter by type, search terms, or date range; paginated results show message, JSON context, and UTC timestamp.
- **Bulk actions**: Export logs as CSV or JSON; clear the table via nonce-protected action.
- **Visual cues**: Badge styling for error, warning, info, and success levels; custom admin CSS enqueued on plugin pages.

## Settings

- **File logging toggle**: Decide whether to mirror database records to JSON-line files.
- **Retention days**: Choose how long logs persist before purge (minimum 1 day).
- **Notification destinations**: Configure email, Slack webhook, and generic webhook URLs.
- **Severity filters**: Select which log levels trigger outbound notifications.
- **Capture toggles**: Enable/disable each automatic capture source listed above.

## Cron & Retention

- Schedules a daily `wpel_purge_old_logs` event on activation.
- Runs `WPEL_Logger::purge_old_logs()` to delete rows older than configured retention.
- Unschedules the cron hook on plugin deactivation.

## Developer Notes

- **Database schema**: `id`, `type`, `message`, `context` (LONGTEXT JSON), `created_at`. Indexed by `type` and timestamp.
- **Context format**: Arrays are JSON-encoded; objects normalized via `wp_json_encode`; non-arrays coerced to `['value' => …]`.
- **Message enrichment**: If sanitized context contains `file`/`line`, the log message is suffixed with `in file.php:123` for quicker triage.
- **Error handler chaining**: Custom handlers defer to previously registered handlers when applicable.
- **JSON output**: File logging writes compact JSON per line; context and payloads respect `JSON_UNESCAPED_SLASHES`/`UNICODE`.
- **Translations**: All admin UI strings wrapped in `__()`/`esc_html__()` with the `wpel` text domain.

## Filters & Actions

- `wpel_log_types` — Adjust or extend permitted log levels.
- `wpel_sensitive_keys` — Customize redacted keys during context scrubbing.
- `wpel_sanitize_context` — Final filter before context storage.
- `wpel_should_notify` — Decide dynamically if a notification should fire.
- `wpel_notification_payload` — Alter webhook payload contents.
- `wpel_file_log_dir` / `wpel_file_log_path` — Override filesystem targets.
- `wpel_log_added` (action) — Runs after a log entry is persisted; receives type, message, sanitized context, and database ID.

## Installation & Usage

1. Upload the plugin to `wp-content/plugins/wp-exception-logger/` and activate it.
2. Visit `WPEL Logs → Settings` to adjust retention, capture sources, notifications, and file logging.
3. Use helper functions in your codebase, e.g.:

```php
wpel_log_error('Payment gateway timeout', array(
  'order_id' => 1234,
  'response' => $response,
));
```

4. Monitor logs under `WPEL Logs → Logs`, export as needed, and configure alerts to stay ahead of issues.

<?php
if (!defined('ABSPATH')) { exit; }

class WPEL_Admin {
  public static function add_menus() {
    add_menu_page(
      __('WPEL Logs', 'wpel'),
      __('WPEL Logs', 'wpel'),
      'manage_options',
      'wpel-logs',
      array(__CLASS__, 'render_logs_page'),
      'dashicons-shield',
      66
    );

    add_submenu_page(
      'wpel-logs',
      __('WPEL Settings', 'wpel'),
      __('Settings', 'wpel'),
      'manage_options',
      'wpel-settings',
      array(__CLASS__, 'render_settings_page')
    );
  }

  public static function render_settings_page() {
    if (!current_user_can('manage_options')) {
      wp_die(__('You do not have permission to access this page.', 'wpel'));
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('WP Exception Logger Settings', 'wpel') . '</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('wpel_settings_group');
    do_settings_sections('wpel_settings');
    submit_button();
    echo '</form>';
    echo '</div>';
  }

  public static function render_logs_page() {
    if (!current_user_can('manage_options')) {
      wp_die(__('You do not have permission to access this page.', 'wpel'));
    }

    global $wpdb;
    $table = wpel_table_name();

    // Actions: export, clear
    if (!empty($_GET['wpel_action'])) {
      $action = sanitize_key($_GET['wpel_action']);
      if ($action === 'export' && !empty($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'wpel_export')) {
        $format = isset($_GET['format']) && in_array($_GET['format'], array('csv','json'), true) ? $_GET['format'] : 'csv';
        self::export($format);
        exit;
      }
    }

    if (!empty($_POST['wpel_clear_all']) && check_admin_referer('wpel_clear_all_action', 'wpel_clear_all_nonce')) {
      $wpdb->query("TRUNCATE TABLE {$table}");
      echo '<div class="updated"><p>' . esc_html__('All logs cleared.', 'wpel') . '</p></div>';
    }

    // Filters
    $type = isset($_GET['type']) ? sanitize_key($_GET['type']) : '';
    $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

    $where = 'WHERE 1=1';
    $params = array();
    if ($type && in_array($type, apply_filters('wpel_log_types', array('error','warning','info','success')), true)) {
      $where .= ' AND type = %s';
      $params[] = $type;
    }
    if ($search) {
      $where .= ' AND (message LIKE %s OR context LIKE %s)';
      $like = '%' . $wpdb->esc_like($search) . '%';
      $params[] = $like;
      $params[] = $like;
    }
    if ($date_from) {
      $where .= ' AND created_at >= %s';
      $params[] = $date_from . ' 00:00:00';
    }
    if ($date_to) {
      $where .= ' AND created_at <= %s';
      $params[] = $date_to . ' 23:59:59';
    }

    // Pagination
    $paged = isset($_GET['paged']) ? max(1, (int)$_GET['paged']) : 1;
    $per_page = 20;
    $offset = ($paged - 1) * $per_page;

    $total = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} {$where}", $params));
    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} {$where} ORDER BY id DESC LIMIT %d OFFSET %d", array_merge($params, array($per_page, $offset))));

    $export_nonce = wp_create_nonce('wpel_export');

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">' . esc_html__('WP Exception Logger', 'wpel') . '</h1>';
    echo ' <a class="page-title-action" href="' . esc_url(add_query_arg(array('wpel_action' => 'export', 'format' => 'csv', '_wpnonce' => $export_nonce))) . '">' . esc_html__('Export CSV', 'wpel') . '</a>';
    echo ' <a class="page-title-action" href="' . esc_url(add_query_arg(array('wpel_action' => 'export', 'format' => 'json', '_wpnonce' => $export_nonce))) . '">' . esc_html__('Export JSON', 'wpel') . '</a>';
    echo '<hr class="wp-header-end" />';

    // Filters Form
    echo '<form method="get" style="margin-bottom: 12px;">';
    echo '<input type="hidden" name="page" value="wpel-logs" />';
    echo '<label>' . esc_html__('Type', 'wpel') . ' ';
    echo '<select name="type">';
    echo '<option value="">' . esc_html__('All', 'wpel') . '</option>';
    foreach (apply_filters('wpel_log_types', array('error','warning','info','success')) as $t) {
      echo '<option value="' . esc_attr($t) . '" ' . selected($type, $t, false) . '>' . esc_html(ucfirst($t)) . '</option>';
    }
    echo '</select></label> ';
    echo '<label>' . esc_html__('Search', 'wpel') . ' <input type="search" name="s" value="' . esc_attr($search) . '"></label> ';
    echo '<label>' . esc_html__('From', 'wpel') . ' <input type="date" name="date_from" value="' . esc_attr($date_from) . '"></label> ';
    echo '<label>' . esc_html__('To', 'wpel') . ' <input type="date" name="date_to" value="' . esc_attr($date_to) . '"></label> ';
    submit_button(__('Filter'), 'secondary', '', false);
    echo '</form>';

    // Clear form
    echo '<form method="post" style="margin-bottom: 12px;">';
    wp_nonce_field('wpel_clear_all_action', 'wpel_clear_all_nonce');
    submit_button(__('Clear All Logs', 'wpel'), 'delete', 'wpel_clear_all', false, array('onclick' => "return confirm('Are you sure you want to delete all logs?');"));
    echo '</form>';

    // Table
    echo '<table class="widefat striped">';
    echo '<thead><tr>';
    echo '<th style="width:90px;">' . esc_html__('Type', 'wpel') . '</th>';
    echo '<th>' . esc_html__('Message', 'wpel') . '</th>';
    echo '<th>' . esc_html__('Context', 'wpel') . '</th>';
    echo '<th style="width:170px;">' . esc_html__('Timestamp (UTC)', 'wpel') . '</th>';
    echo '</tr></thead><tbody>';

    if ($rows) {
      foreach ($rows as $r) {
        $context = $r->context ? json_decode($r->context, true) : null;
        echo '<tr>';
        echo '<td><span class="wpel-badge wpel-' . esc_attr($r->type) . '">' . esc_html(ucfirst($r->type)) . '</span></td>';
        echo '<td>' . esc_html($r->message) . '</td>';
        echo '<td><code style="display:block;white-space:pre-wrap;max-height:160px;overflow:auto;">' . esc_html($r->context) . '</code></td>';
        echo '<td>' . esc_html(gmdate('Y-m-d H:i:s', strtotime($r->created_at))) . '</td>';
        echo '</tr>';
      }
    } else {
      echo '<tr><td colspan="4">' . esc_html__('No logs found.', 'wpel') . '</td></tr>';
    }

    echo '</tbody></table>';

    // Pagination links
    $total_pages = (int)ceil($total / $per_page);
    if ($total_pages > 1) {
      $base_url = remove_query_arg('paged');
      echo '<div class="tablenav"><div class="tablenav-pages">';
      echo paginate_links(array(
        'base' => add_query_arg('paged', '%#%', $base_url),
        'format' => '',
        'prev_text' => __('« Prev'),
        'next_text' => __('Next »'),
        'total' => $total_pages,
        'current' => $paged,
      ));
      echo '</div></div>';
    }

    // Basic badge styles
    echo '<style>
    .wpel-badge{display:inline-block;padding:2px 8px;border-radius:4px;color:#fff;font-size:12px;}
    .wpel-error{background:#cc0000;}
    .wpel-warning{background:#d97706;}
    .wpel-info{background:#2563eb;}
    .wpel-success{background:#059669;}
    </style>';

    echo '</div>';
  }

  private static function export($format = 'csv') {
    if (!current_user_can('manage_options')) {
      wp_die(__('Unauthorized', 'wpel'));
    }
    global $wpdb;
    $table = wpel_table_name();

    $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id DESC", ARRAY_A);

    if ($format === 'json') {
      nocache_headers();
      header('Content-Type: application/json; charset=utf-8');
      header('Content-Disposition: attachment; filename="wpel-logs.json"');
      echo wp_json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
      nocache_headers();
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename="wpel-logs.csv"');
      $out = fopen('php://output', 'w');
      fputcsv($out, array('id','type','message','context','created_at'));
      foreach ($rows as $row) {
        fputcsv($out, array($row['id'], $row['type'], $row['message'], $row['context'], $row['created_at']));
      }
      fclose($out);
    }
    exit;
  }
}

<?php
// File: pages/setting.php
if (!defined('ABSPATH')) exit;
global $wpdb;

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mutasi_bca_save_setting'])) {
    $endpoint = sanitize_text_field($_POST['endpoint']);
    $interval_time = intval($_POST['interval']);
    $user_id = sanitize_text_field($_POST['user_id']);
    $pin = sanitize_text_field($_POST['pin']);
    $bearer_token = sanitize_text_field($_POST['bearer_token']);
    $forms_id = isset($_POST['forms_id']) ? sanitize_text_field($_POST['forms_id']) : null;
    $field_status_id = isset($_POST['field_status_id']) ? sanitize_text_field($_POST['field_status_id']) : null;
    $wpdb->insert($wpdb->prefix.'mutasi_bca_settings', [
        'endpoint' => $endpoint,
        'interval_time' => $interval_time,
        'user_id' => $user_id,
        'pin' => $pin,
        'bearer_token' => $bearer_token,
        'forms_id' => $forms_id,
        'field_status_id' => $field_status_id
    ]);
    echo '<div class="updated"><p>Setting berhasil disimpan!</p></div>';
}

$setting = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}mutasi_bca_settings ORDER BY id DESC LIMIT 1");
$logs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mutasi_bca_log ORDER BY id DESC LIMIT 50");
?>
<div class="wrap">
    <h1>Setting Mutasi BCA</h1>
    <form method="post">
        <table class="form-table">
            <tr><th>Endpoint URL</th><td><input type="text" name="endpoint" value="<?php echo esc_attr($setting->endpoint ?? ''); ?>" size="50" required></td></tr>
            <tr><th>Interval (menit)</th><td><input type="number" name="interval" value="<?php echo esc_attr($setting->interval_time ?? 60); ?>" min="1" required></td></tr>
            <tr><th>User ID</th><td><input type="text" name="user_id" value="<?php echo esc_attr($setting->user_id ?? ''); ?>" required></td></tr>
            <tr><th>PIN</th><td><input type="password" name="pin" value="<?php echo esc_attr($setting->pin ?? ''); ?>" required></td></tr>
            <tr><th>Bearer Token</th><td><input type="text" name="bearer_token" value="<?php echo esc_attr($setting->bearer_token ?? ''); ?>" required></td></tr>
            <tr><th>Forms ID</th><td><input type="number" name="forms_id" value="<?php echo esc_attr($setting->forms_id ?? ''); ?>" required></td></tr>
            <tr><th>Field Status ID</th><td><input type="number" name="field_status_id" value="<?php echo esc_attr($setting->field_status_id ?? ''); ?>" required></td></tr>
        </table>
        <p><input type="submit" name="mutasi_bca_save_setting" class="button-primary" value="Simpan Setting"></p>
    </form>
    <h2>Log Event</h2>
    <table class="widefat">
        <thead><tr><th>Waktu</th><th>Pesan</th></tr></thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?php echo esc_html($log->event_time); ?></td>
                <td><?php echo esc_html($log->message); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

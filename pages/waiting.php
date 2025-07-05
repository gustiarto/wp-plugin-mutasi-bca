<?php
// File: pages/waiting.php
if (!defined('ABSPATH')) exit;
global $wpdb;

// Handle add manual
if (isset($_POST['mutasi_bca_add_waiting'])) {
    $entry_id = intval($_POST['entry_id']);
    $nominal = sanitize_text_field($_POST['nominal']);
    $wpdb->insert($wpdb->prefix.'mutasi_bca_waiting', [
        'entry_id' => $entry_id,
        'nominal' => $nominal,
        'status' => 'waiting'
    ]);
    echo '<div class="updated"><p>Waiting list berhasil ditambah!</p></div>';
}
// Handle delete manual
if (isset($_GET['delete_waiting'])) {
    $wpdb->delete($wpdb->prefix.'mutasi_bca_waiting', ['id' => intval($_GET['delete_waiting'])]);
    echo '<div class="updated"><p>Waiting list berhasil dihapus!</p></div>';
}
// Handle trigger manual
if (isset($_GET['mark_paid'])) {
    $id = intval($_GET['mark_paid']);
    $waiting = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mutasi_bca_waiting WHERE id = %d", $id));
    if ($waiting) {
        $wpdb->update($wpdb->prefix.'mutasi_bca_waiting', ['status' => 'manual_paid'], ['id' => $id]);
        // Update status di wpforms jika setting tersedia
        $setting = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}mutasi_bca_settings ORDER BY id DESC LIMIT 1");
        if ($setting && $setting->forms_id && $setting->field_status_id) {
            $wpdb->update(
                $wpdb->prefix . 'wpforms_entries',
                ['field_id_' . $setting->field_status_id => 'LUNAS'],
                ['form_id' => $setting->forms_id, 'entry_id' => $waiting->entry_id]
            );
        }
        echo '<div class="updated"><p>Status pembayaran ditandai manual!</p></div>';
    }
}
$waitings = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mutasi_bca_waiting ORDER BY id DESC LIMIT 100");
?>
<div class="wrap">
    <h1>Waiting List Mutasi</h1>
    <form method="post" style="margin-bottom:20px;">
        <h2>Tambah Manual</h2>
        <table class="form-table">
            <tr><th>Entry ID</th><td><input type="number" name="entry_id" required></td></tr>
            <tr><th>Nominal</th><td><input type="text" name="nominal" required></td></tr>
        </table>
        <p><input type="submit" name="mutasi_bca_add_waiting" class="button-primary" value="Tambah Waiting"></p>
    </form>
    <h2>Daftar Waiting List</h2>
    <table class="widefat">
        <thead>
            <tr>
                <th>ID</th>
                <th>Entry ID</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Waktu</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($waitings as $row): ?>
            <tr>
                <td><?php echo esc_html($row->id); ?></td>
                <td><?php echo esc_html($row->entry_id); ?></td>
                <td><?php echo esc_html($row->nominal); ?></td>
                <td><?php echo esc_html($row->status); ?></td>
                <td><?php echo esc_html($row->created_at); ?></td>
                <td>
                    <a href="?page=mutasi-bca-waiting&mark_paid=<?php echo $row->id; ?>" class="button">Tandai Lunas</a>
                    <a href="?page=mutasi-bca-waiting&delete_waiting=<?php echo $row->id; ?>" class="button" onclick="return confirm('Hapus waiting list ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

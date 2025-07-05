<?php
// File: pages/mutasi.php
if (!defined('ABSPATH')) exit;
global $wpdb;
$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mutasi_bca_data ORDER BY id DESC LIMIT 100");

if (isset($_GET['mutasi_bca_fetch_manual'])) {
    require_once plugin_dir_path(__FILE__) . '../includes/cronjob.php';
    echo '<div class="updated"><p>Fetching mutasi manual telah dijalankan!</p></div>';
}
?>
<div class="wrap">
    <h1>Mutasi BCA</h1>
    <form method="get" style="margin-bottom:20px;">
        <input type="hidden" name="page" value="mutasi-bca">
        <input type="submit" name="mutasi_bca_fetch_manual" class="button-primary" value="Fetch Mutasi Sekarang">
    </form>
    <table class="widefat">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Cabang</th>
                <th>Mutasi</th>
                <th>Tipe</th>
                <th>Saldo</th>
                <th>Waktu Simpan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?php echo esc_html($row->tanggal); ?></td>
                <td><?php echo esc_html($row->keterangan); ?></td>
                <td><?php echo esc_html($row->cabang); ?></td>
                <td><?php echo esc_html($row->mutasi); ?></td>
                <td><?php echo esc_html($row->tipe); ?></td>
                <td><?php echo esc_html($row->saldo); ?></td>
                <td><?php echo esc_html($row->created_at); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

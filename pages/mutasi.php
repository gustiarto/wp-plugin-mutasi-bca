<?php
// File: pages/mutasi.php
if (!defined('ABSPATH')) exit;
global $wpdb;
$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mutasi_bca_data ORDER BY id DESC LIMIT 100");
?>
<div class="wrap">
    <h1>Mutasi BCA</h1>
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

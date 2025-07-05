<?php
// File: includes/cronjob.php
// Fungsi untuk menjalankan request ke endpoint mutasi dan simpan hasilnya ke database
if (!defined('ABSPATH')) exit;
global $wpdb;

$settings = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}mutasi_bca_settings ORDER BY id DESC LIMIT 1");
if (!$settings) return;

$url = $settings->endpoint;
$user_id = $settings->user_id;
$pin = $settings->pin;
$token = $settings->bearer_token;

$body = json_encode([
    'user_id' => $user_id,
    'pin' => $pin
]);

$args = [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json'
    ],
    'body' => $body,
    'timeout' => 30
];

$response = wp_remote_post($url, $args);
if (is_wp_error($response)) {
    $wpdb->insert($wpdb->prefix.'mutasi_bca_log', [
        'message' => 'Request error: ' . $response->get_error_message()
    ]);
    return;
}

$body = wp_remote_retrieve_body($response);
$data = json_decode($body, true);
if (!$data || empty($data['success'])) {
    $wpdb->insert($wpdb->prefix.'mutasi_bca_log', [
        'message' => 'Response error: ' . $body
    ]);
    return;
}

require_once plugin_dir_path(__FILE__) . 'waiting.php';

// Ambil semua waiting list yang valid (status waiting, form_id sesuai, fields mengandung 'antrian payment')
$waiting_candidates = mutasi_bca_get_waiting_candidates($wpdb, $settings->forms_id);

// Buat array untuk pencocokan cepat
$waiting_map = [];
foreach ($waiting_candidates as $w) {
    $waiting_map[$w->nominal][] = $w;
}

foreach ($data['data'] as $row) {
    $wpdb->insert($wpdb->prefix.'mutasi_bca_data', [
        'tanggal' => $row['tanggal'],
        'keterangan' => $row['keterangan'],
        'cabang' => $row['cabang'],
        'mutasi' => $row['mutasi'],
        'tipe' => $row['tipe'],
        'saldo' => $row['saldo']
    ]);
    // Trigger hanya untuk transaksi masuk (CR)
    if ($row['tipe'] === 'CR' && isset($waiting_map[$row['mutasi']])) {
        foreach ($waiting_map[$row['mutasi']] as $waiting) {
            // Ambil data entry wpforms
            $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wpforms_entries WHERE entry_id = %d", $waiting->entry_id));
            if ($entry && strpos($entry->fields, 'antrian payment') !== false) {
                // Update kolom fields: ganti value antrian payment menjadi keterangan mutasi
                $fields = json_decode($entry->fields, true);
                foreach ($fields as $fid => &$f) {
                    if (isset($f['name']) && strtolower($f['name']) === 'status' && strtolower($f['value']) === 'antrian payment') {
                        $f['value'] = $row['keterangan'];
                    }
                }
                $fields_json = wp_json_encode($fields);
                $wpdb->update(
                    $wpdb->prefix . 'wpforms_entries',
                    ['fields' => $fields_json],
                    ['entry_id' => $waiting->entry_id]
                );
                // Hapus dari waiting list
                $wpdb->delete($wpdb->prefix.'mutasi_bca_waiting', ['id' => $waiting->id]);
                // Tambahkan keterangan pada mutasi
                $wpdb->update($wpdb->prefix.'mutasi_bca_data', [
                    'keterangan' => $row['keterangan'] . ' [TRIGGERED: entry_id ' . $waiting->entry_id . ']'
                ], [
                    'tanggal' => $row['tanggal'],
                    'mutasi' => $row['mutasi'],
                    'tipe' => $row['tipe']
                ]);
            }
        }
    }
}

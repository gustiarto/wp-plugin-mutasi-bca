<?php
// File: includes/waiting.php
// Fungsi untuk menambah, menghapus, dan update waiting list
if (!defined('ABSPATH')) exit;
global $wpdb;

function mutasi_bca_add_waiting($entry_id, $nominal) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix.'mutasi_bca_waiting', [
        'entry_id' => $entry_id,
        'nominal' => $nominal,
        'status' => 'waiting'
    ]);
}

function mutasi_bca_update_waiting_status($id, $status) {
    global $wpdb;
    $wpdb->update($wpdb->prefix.'mutasi_bca_waiting', [
        'status' => $status
    ], ['id' => $id]);
}

function mutasi_bca_get_waiting_by_nominal($nominal) {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mutasi_bca_waiting WHERE nominal = %s AND status = 'waiting' LIMIT 1", $nominal));
}

function mutasi_bca_get_waiting_candidates($wpdb, $form_id) {
    // Ambil entry_id dan nominal dari waiting list yang entry-nya di wpforms_entries kolom fields mengandung 'antrian payment'
    $waiting = $wpdb->get_results($wpdb->prepare("SELECT w.* FROM {$wpdb->prefix}mutasi_bca_waiting w JOIN {$wpdb->prefix}wpforms_entries e ON w.entry_id = e.entry_id WHERE e.form_id = %d AND e.fields LIKE %s AND w.status = 'antrian payment'", $form_id, '%antrian payment%'));
    echo $waiting;
    return $waiting;
}

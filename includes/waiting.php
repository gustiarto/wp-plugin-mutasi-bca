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
    // Join wpforms_entries dan mutasi_bca_waiting, hanya yang status waiting dan fields mengandung 'antrian payment'
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT w.*, e.fields FROM {$wpdb->prefix}mutasi_bca_waiting w 
         JOIN {$wpdb->prefix}wpforms_entries e ON w.entry_id = e.entry_id 
         WHERE e.form_id = %d AND e.fields LIKE %s AND w.status = 'waiting'",
        $form_id, '%antrian payment%'
    ));
    return $results;
}

function mutasi_bca_nominal_match($waiting_nominal, $mutasi_nominal) {
    // Hilangkan karakter selain angka dari kedua nominal
    $waiting = preg_replace('/[^0-9]/', '', $waiting_nominal);
    $mutasi = preg_replace('/[^0-9]/', '', $mutasi_nominal);
    return ltrim($waiting, '0') === ltrim($mutasi, '0');
}

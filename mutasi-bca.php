<?php
/*
Plugin Name: Mutasi BCA Scheduler
Description: Plugin untuk cek mutasi BCA secara berkala dan menampilkan hasilnya di dashboard WordPress.
Version: 1.0
Author: DevOps zeeya.id
*/

if (!defined('ABSPATH')) exit;

// Aktivasi plugin: buat tabel custom
register_activation_hook(__FILE__, 'mutasi_bca_activate');
function mutasi_bca_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    $table_settings = $wpdb->prefix . 'mutasi_bca_settings';
    $table_mutasi = $wpdb->prefix . 'mutasi_bca_data';
    $table_log = $wpdb->prefix . 'mutasi_bca_log';
    $table_waiting = $wpdb->prefix . 'mutasi_bca_waiting';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta("CREATE TABLE $table_settings (
        id INT NOT NULL AUTO_INCREMENT,
        endpoint VARCHAR(255),
        interval_time INT,
        user_id VARCHAR(100),
        pin VARCHAR(100),
        bearer_token VARCHAR(255),
        forms_id INT,
        field_status_id INT,
        PRIMARY KEY (id)
    ) $charset_collate;");
    dbDelta("CREATE TABLE $table_mutasi (
        id INT NOT NULL AUTO_INCREMENT,
        tanggal VARCHAR(50),
        keterangan TEXT,
        cabang VARCHAR(50),
        mutasi VARCHAR(50),
        tipe VARCHAR(10),
        saldo VARCHAR(50),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;");
    dbDelta("CREATE TABLE $table_log (
        id INT NOT NULL AUTO_INCREMENT,
        event_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        message TEXT,
        PRIMARY KEY (id)
    ) $charset_collate;");
    dbDelta("CREATE TABLE $table_waiting (
        id INT NOT NULL AUTO_INCREMENT,
        entry_id INT NOT NULL,
        nominal VARCHAR(50) NOT NULL,
        status VARCHAR(50) DEFAULT 'waiting',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;");
}

// Tambah menu admin
add_action('admin_menu', 'mutasi_bca_admin_menu');
function mutasi_bca_admin_menu() {
    add_menu_page('Mutasi BCA', 'Mutasi BCA', 'manage_options', 'mutasi-bca', 'mutasi_bca_page_mutasi', 'dashicons-bank', 6);
    add_submenu_page('mutasi-bca', 'Mutasi', 'Mutasi', 'manage_options', 'mutasi-bca', 'mutasi_bca_page_mutasi');
    add_submenu_page('mutasi-bca', 'Setting', 'Setting', 'manage_options', 'mutasi-bca-setting', 'mutasi_bca_page_setting');
    add_submenu_page('mutasi-bca', 'Waiting List', 'Waiting List', 'manage_options', 'mutasi-bca-waiting', 'mutasi_bca_page_waiting');
}

// Halaman Mutasi
function mutasi_bca_page_mutasi() {
    include plugin_dir_path(__FILE__) . 'pages/mutasi.php';
}

// Halaman Setting
function mutasi_bca_page_setting() {
    include plugin_dir_path(__FILE__) . 'pages/setting.php';
}

// Halaman Waiting List
function mutasi_bca_page_waiting() {
    include plugin_dir_path(__FILE__) . 'pages/waiting.php';
}

// Scheduler cron event
register_activation_hook(__FILE__, 'mutasi_bca_schedule_event');
function mutasi_bca_schedule_event() {
    global $wpdb;
    $setting = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}mutasi_bca_settings ORDER BY id DESC LIMIT 1");
    $interval = ($setting && $setting->interval_time) ? intval($setting->interval_time) * 60 : 3600; // detik
    if (!wp_next_scheduled('mutasi_bca_cron_hook')) {
        wp_schedule_event(time(), $interval >= 60 ? 'mutasi_bca_custom_interval' : 'hourly', 'mutasi_bca_cron_hook');
    }
}

add_filter('cron_schedules', function($schedules) {
    global $wpdb;
    $setting = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}mutasi_bca_settings ORDER BY id DESC LIMIT 1");
    $interval = ($setting && $setting->interval_time) ? intval($setting->interval_time) * 60 : 3600;
    $schedules['mutasi_bca_custom_interval'] = [
        'interval' => $interval,
        'display' => 'Mutasi BCA Custom Interval'
    ];
    return $schedules;
});

register_deactivation_hook(__FILE__, 'mutasi_bca_clear_schedule');
function mutasi_bca_clear_schedule() {
    wp_clear_scheduled_hook('mutasi_bca_cron_hook');
}
add_action('mutasi_bca_cron_hook', 'mutasi_bca_cron_job');

// Fungsi utama cron job
function mutasi_bca_cron_job() {
    require_once plugin_dir_path(__FILE__) . 'includes/cronjob.php';
}

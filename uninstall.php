<?php
/**
 * Uninstall script for Headline Ligatures and Styles plugin
 *
 * This file is called when the plugin is uninstalled via the WordPress admin.
 * It cleans up all plugin data from the database and filesystem.
 *
 * @package Headline_Ligatures_Styles
 */

// Exit if accessed directly or not uninstalling
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options from database
delete_option('hls_presets');
delete_option('hls_custom_fonts');
delete_option('hls_global_settings');

// Delete transients
global $wpdb;

// Delete all editor data transients
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $wpdb->esc_like('_transient_hls_editor_data_') . '%',
        $wpdb->esc_like('_transient_timeout_hls_editor_data_') . '%'
    )
);

// Delete combined font CSS transient
delete_transient('hls_combined_font_css');

// Delete has_styled transients for all posts
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $wpdb->esc_like('_transient_hls_has_styled_') . '%',
        $wpdb->esc_like('_transient_timeout_hls_has_styled_') . '%'
    )
);

// Delete rate limit transients
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $wpdb->esc_like('_transient_hls_rate_limit_') . '%',
        $wpdb->esc_like('_transient_timeout_hls_rate_limit_') . '%'
    )
);

// Delete uploaded font files
$upload_dir = wp_upload_dir();
$font_dir = $upload_dir['basedir'] . '/hls';

if (file_exists($font_dir)) {
    require_once(ABSPATH . 'wp-admin/includes/file.php');

    // Initialize WordPress Filesystem
    if (WP_Filesystem()) {
        global $wp_filesystem;

        // Remove entire HLS directory including fonts
        $wp_filesystem->rmdir($font_dir, true);
    } else {
        // Fallback to PHP functions if WP_Filesystem fails
        if (is_dir($font_dir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($font_dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                if ($file->isDir()) {
                    @rmdir($file->getRealPath());
                } else {
                    @unlink($file->getRealPath());
                }
            }

            @rmdir($font_dir);
        }
    }
}

// Clean up any orphaned post meta (though this plugin doesn't use post meta, good practice)
// Features are stored inline in post content, so no cleanup needed there

// Log completion for debugging (if WP_DEBUG is enabled)
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Headline Ligatures & Styles: Plugin uninstalled and all data removed');
}

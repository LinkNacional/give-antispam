<?php
/**
 * @since      1.0.0
 */
if ( ! defined('WPINC')) {
    exit;
}

/**
 * Show plugin dependency notice.
 *
 * @since
 */
function __give_lkn_antispam_dependency_notice(): void {
    // Admin notice.
    $message = sprintf(
        '<div class="notice notice-error"><p><strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a>  %5$s %6$s+ %7$s.</p></div>',
        __('Activation Error:', 'antispam-donation-for-givewp'),
        __('You must have', 'antispam-donation-for-givewp'),
        'https://givewp.com',
        __('Give', 'antispam-donation-for-givewp'),
        __('version', 'antispam-donation-for-givewp'),
        LKN_ANTISPAM_FOR_GIVEWP_MIN_GIVE_VERSION,
        __('for the Give Antispam to activate', 'antispam-donation-for-givewp')
    );

    echo $message;
}

/**
 * Notice for No Core Activation.
 *
 * @since
 */
function __give_lkn_antispam_inactive_notice(): void {
    // Admin notice.
    $message = sprintf(
        '<div class="notice notice-error"><p><strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a> %5$s.</p></div>',
        __('Activation Error:', 'antispam-donation-for-givewp'),
        __('You must have', 'antispam-donation-for-givewp'),
        'https://givewp.com',
        __('Give', 'antispam-donation-for-givewp'),
        __('plugin installed and activated for the Give Antispam', 'antispam-donation-for-givewp')
    );

    echo $message;
}

/**
 * Plugin row meta links.
 *
 * @since
 *
 * @param array  $plugin_meta an array of the plugin's metadata
 * @param string $plugin_file path to the plugin file, relative to the plugins directory
 *
 * @return array
 */
function __give_lkn_antispam_plugin_row_meta($plugin_meta, $plugin_file) {
    $new_meta_links['setting'] = sprintf(
        '<a href="%1$s">%2$s</a>',
        admin_url('edit.php?post_type=give_forms&page=give-settings&tab=general&section=access-control'),
        __('Settings', 'antispam-donation-for-givewp')
    );

    return array_merge($plugin_meta, $new_meta_links);
}

/**
 * Show activation banner.
 *
 * @since
 */
function __give_lkn_antispam_activation(): void {
    // Initialize activation welcome banner.
    if (class_exists('Lkn_Give_Antispam')) {
        // Only runs on admin.
        $args = array(
            'file' => LKN_ANTISPAM_FOR_GIVEWP_FILE,
            'name' => __('Antispam', 'antispam-donation-for-givewp'),
            'version' => LKN_ANTISPAM_FOR_GIVEWP_VERSION,
            'settings_url' => admin_url('edit.php?post_type=give_forms&page=give-settings&tab=general&section=access-control'),
            'documentation_url' => 'https://www.linknacional.com.br/wordpress/givewp/',
            'support_url' => 'https://www.linknacional.com.br/wordpress/givewp/',
            'testing' => false, // Never leave true.
        );

        new Lkn_Give_Antispam($args);
    }
}

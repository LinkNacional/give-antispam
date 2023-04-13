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
function verifyPluginDependencies(): void {
    // Load plugin helper functions.
    if ( ! function_exists('deactivate_plugins') || ! function_exists('is_plugin_active')) {
        require_once ABSPATH . '/wp-admin/includes/plugin.php';
    }

    // Flag to check whether deactivate plugin or not.
    $is_deactivate_plugin = null;

    $lkn_antispam_path = ABSPATH . '/wp-content/plugins/give-antispam/lkn-antispam-for-givewp.php';

    $is_installed = false;

    // Verifica se o plugin Give está instalado e ativado.
    if (function_exists('get_plugins')) {
        $all_plugins = get_plugins();
        $is_installed = ! empty($all_plugins['give/give.php']);

        $all_activateds = get_option( 'active_plugins' );
        $activeted_plugin = in_array('give/give.php', $all_activateds, true);
    }

    // Verifica a versão mínima do Give e se ele está ativado.
    if ($is_installed) {
        require_once ABSPATH . '/wp-content/plugins/give/give.php';

        if ($activeted_plugin && version_compare(GIVE_VERSION, LKN_ANTISPAM_FOR_GIVEWP_MIN_GIVE_VERSION, '<')) {
            $is_deactivate_plugin = true;
            dependencyAlert();
        } elseif ($activeted_plugin && version_compare(GIVE_VERSION, LKN_ANTISPAM_FOR_GIVEWP_MIN_GIVE_VERSION, '>')) {
            $is_deactivate_plugin = false;
        } elseif ( ! $activeted_plugin) {
            $is_deactivate_plugin = true;
            inactiveAlert();
        }
    } elseif ( ! $is_installed) {
        $is_deactivate_plugin = true;
        dependencyAlert();
    }

    // Deactivate plugin.
    if ($is_deactivate_plugin) {
        deactivate_plugins($lkn_antispam_path);

        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}

function __lkn_antispam_for_givewp_dependency_notice(): void {
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

function dependencyAlert(): void {
    add_action('admin_notices', '__lkn_antispam_for_givewp_dependency_notice');
}

/**
 * Notice for No Core Activation.
 *
 * @since
 */
function __lkn_antispam_for_givewp_inactive_notice(): void {
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

function inactiveAlert(): void {
    add_action('admin_notices', '__lkn_antispam_for_givewp_inactive_notice');
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
function __lkn_antispam_for_givewp_plugin_row_meta($plugin_meta) {
    $new_meta_links['setting'] = sprintf(
        '<a href="%1$s">%2$s</a>',
        admin_url('edit.php?post_type=give_forms&page=give-settings&tab=general&section=access-control'),
        __('Settings', 'antispam-donation-for-givewp')
    );

    return array_merge($plugin_meta, $new_meta_links);
}

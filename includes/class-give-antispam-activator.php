<?php

/**
 * Fired during plugin activation.
 *
 * @see        https://https://www.linknacional.com.br
 * @since      1.0.0
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 *
 * @author     Link Nacional
 */
final class Lkn_Give_Antispam_Activator {
    /**
     * Short Description. (use period).
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate(): void {
        if ( ! Lkn_Give_Antispam_Activator::check_environment()) {
            return;
        }
    }

    /**
     * Check plugin environment.
     *
     * @return bool|null
     *
     * @since
     */
    public static function check_environment() {
        // Not admin insert here.
        if ( ! is_admin() || ! current_user_can('activate_plugins')) {
            require_once LKN_ANTISPAM_FOR_GIVEWP_DIR . 'public/partials/give-antispam-actions.php';

            return null;
        }

        // Load helper functions.
        require_once plugin_dir_path( __DIR__ ) . 'admin/partials/give-antispam-misc-functions.php';

        // Load plugin helper functions.
        if ( ! function_exists('deactivate_plugins') || ! function_exists('is_plugin_active')) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }

        // Flag to check whether deactivate plugin or not.
        $is_deactivate_plugin = false;

        if (defined('GIVE_PLUGIN_BASENAME')) {
            $is_give_active = is_plugin_active(GIVE_PLUGIN_BASENAME);
        } else {
            $is_give_active = false;
        }

        $give_antispam = plugin_basename(__FILE__);

        // Verifica versão mínima do Give e se ele está ativado.
        if ($is_give_active) {
            if (version_compare(GIVE_VERSION, LKN_ANTISPAM_FOR_GIVEWP_MIN_GIVE_VERSION, '<')) {
                $is_deactivate_plugin = true;
                add_action('admin_notices', '__give_lkn_antispam_dependency_notice');
            }
        } elseif ( ! $is_give_active) {
            $is_deactivate_plugin = true;
            add_action('admin_notices', '__give_lkn_antispam_inactive_notice');
        }

        // Deactivate plugin.
        if ($is_deactivate_plugin) {
            deactivate_plugins($give_antispam);

            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        }
    }
}

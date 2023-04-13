<?php

/**
 * Fired during plugin activation.
 *
 * @see        https://www.linknacional.com.br
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
final class Lkn_Antispam_For_GiveWP_Activator {
    /**
     * Short Description. (use period).
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate(): void {
        if ( ! Lkn_Antispam_For_GiveWP_Activator::check_environment()) {
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
        if (is_user_logged_in()) {
            if ( ! is_admin() || ! current_user_can('activate_plugins')) {
                require_once LKN_ANTISPAM_FOR_GIVEWP_DIR . 'public/partials/lkn-antispam-for-givewp-actions.php';
            }
        }
    }
}

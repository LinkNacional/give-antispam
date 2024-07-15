<?php
/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @see              https://www.linknacional.com/
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Give - Antispam
 * Plugin URI:        https://www.linknacional.com/wordpress/plugins/
 * Description:       Performs verification and prevention of malicious donations.
 * Version:           1.3.0
 * Author:            Link Nacional
 * Author URI:        https://www.linknacional.com/
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       antispam-donation-for-givewp
 * Domain Path:       /languages
 */
if ( ! defined('WPINC')) {
    exit;
}

define('LKN_ANTISPAM_FOR_GIVEWP_VERSION', '1.3.0');

define('LKN_ANTISPAM_FOR_GIVEWP_MIN_GIVE_VERSION', '2.3.0');

define('LKN_ANTISPAM_FOR_GIVEWP_FILE', __FILE__);

define('LKN_ANTISPAM_FOR_GIVEWP_SLUG', 'antispam-donation-for-givewp');

define('LKN_ANTISPAM_FOR_GIVEWP_DIR', plugin_dir_path(LKN_ANTISPAM_FOR_GIVEWP_FILE));

define('LKN_ANTISPAM_FOR_GIVEWP_URL', plugin_dir_url(LKN_ANTISPAM_FOR_GIVEWP_FILE));

define('LKN_ANTISPAM_FOR_GIVEWP_BASENAME', plugin_basename(LKN_ANTISPAM_FOR_GIVEWP_SLUG));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-lkn-antispam-for-givewp-activator.php.
 */
function activate_lkn_antispam_for_givewp(): void
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-lkn-antispam-for-givewp-activator.php';
    Lkn_Antispam_For_GiveWP_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-lkn-antispam-for-givewp-deactivator.php.
 */
function deactivate_lkn_antispam_for_givewp(): void
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-lkn-antispam-for-givewp-deactivator.php';
    Lkn_Antispam_For_GiveWP_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_lkn_antispam_for_givewp');
register_deactivation_hook(__FILE__, 'deactivate_lkn_antispam_for_givewp');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-lkn-antispam-for-givewp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_lkn_antispam_for_givewp(): void
{
    $plugin = new Lkn_Antispam_For_GiveWP();
    $plugin->run();
}
run_lkn_antispam_for_givewp();

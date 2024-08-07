<?php

/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @see       https://www.linknacional.com.br
 * @since      1.0.0
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 *
 * @author     Link Nacional
 */
final class Lkn_Antispam_For_GiveWP {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     *
     * @var Lkn_Antispam_For_GiveWP_Loader maintains and registers all hooks for the plugin
     */
    private $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     *
     * @var string the string used to uniquely identify this plugin
     */
    private $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     *
     * @var string the current version of the plugin
     */
    private $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('LKN_ANTISPAM_FOR_GIVEWP_VERSION')) {
            $this->version = LKN_ANTISPAM_FOR_GIVEWP_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'antispam-donation-for-givewp';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        Lkn_Antispam_Helper::verify_plugin_dependencies();
        $this->define_event_delete_old_logs();
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run(): void {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     *
     * @return string the name of the plugin
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     *
     * @return Lkn_Antispam_For_GiveWP_Loader orchestrates the hooks of the plugin
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     *
     * @return string the version number of the plugin
     */
    public function get_version() {
        return $this->version;
    }

    public function define_cron_hook(): void {
        add_action('lkn_antispam_delete_old_logs_cron_hook', array('Lkn_Antispam_Helper', 'delete_old_logs'));
    }

    public function updater_init() {
        include_once plugin_dir_path(__DIR__) . 'includes/plugin-updater/plugin-update-checker.php';

        return new Lkn_Puc_Plugin_UpdateChecker(
            'https://api.linknacional.com.br/v2/u/?slug=give-antispam',
            LKN_ANTISPAM_FOR_GIVEWP_FILE,// (caso o plugin não precise de compatibilidade com ioncube utilize: __FILE__), //Full path to the main plugin file or functions.php.
            'give-antispam'
        );
    }

    private function define_event_delete_old_logs(): void {
        if ( ! wp_next_scheduled('lkn_antispam_delete_old_logs_cron_hook')) {
            $time = time() + ((7 * 24) * (60 * 60));
            wp_schedule_event($time, 'weekly', 'lkn_antispam_delete_old_logs_cron_hook');
        }
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Lkn_Antispam_For_GiveWP_Loader. Orchestrates the hooks of the plugin.
     * - Lkn_Antispam_For_GiveWP_i18n. Defines internationalization functionality.
     * - Lkn_Antispam_For_GiveWP_Admin. Defines all hooks for the admin area.
     * - Lkn_Antispam_For_GiveWP_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     */
    private function load_dependencies(): void {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(__DIR__) . 'includes/class-lkn-antispam-for-givewp-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(__DIR__) . 'includes/class-lkn-antispam-for-givewp-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(__DIR__) . 'admin/class-lkn-antispam-for-givewp-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(__DIR__) . 'public/class-lkn-antispam-for-givewp-public.php';

        /**
         * Load plugin files.
         */
        require_once plugin_dir_path(__DIR__) . 'includes/class-lkn-antispam-for-givewp-helper.php';

        /**
         * The class responsible for defining the actions in front-end.
         */
        require_once plugin_dir_path(__DIR__) . 'public/partials/lkn-antispam-for-givewp-actions.php';

        $this->loader = new Lkn_Antispam_For_GiveWP_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Lkn_Antispam_For_GiveWP_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     */
    private function set_locale(): void {
        $plugin_i18n = new Lkn_Antispam_For_GiveWP_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_admin_hooks(): void {
        $plugin_admin = new Lkn_Antispam_For_GiveWP_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('give_init', $this, 'updater_init');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     */
    private function define_public_hooks(): void {
        $plugin_public = new Lkn_Antispam_For_GiveWP_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('lkn_antispam_timeout_for_spam_detected_cron', 'Lkn_Antispam_Actions', 'time_for_spam_detected');
        $this->loader->add_filter('give_enabled_payment_gateways', 'Lkn_Antispam_Helper', 'block_all_payments', 99);
        $this->loader->add_action('lkn_antispam_spam_detected_block_all_event', 'Lkn_Antispam_Helper', 'remove_status_block_all_payments');
        $this->loader->add_filter('the_content', 'Lkn_Antispam_Helper', 'add_php_custom_page');

        add_filter('plugin_action_links_give-antispam/give-antispam.php', array('Lkn_Antispam_Helper', 'plugin_row_meta'), 10, 2);
        add_action('give_init', array($this, 'define_cron_hook'), 10, 1);
    }
}

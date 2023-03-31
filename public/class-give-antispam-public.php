<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @see       https://https://www.linknacional.com.br
 * @since      1.0.0
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @author     Link Nacional <linknacional@gmail.com>
 */
final class Lkn_Give_Antispam_Public {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     *
     * @var string the ID of this plugin
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     *
     * @var string the current version of this plugin
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name the name of the plugin
     * @param string $version     the version of this plugin
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('init', array($this, 'init_actions'));
    }

    public function init_actions(): void {
        add_action('give_checkout_error_checks', 'lkn_give_antispam_validate_donation', 10, 2);
        add_action('give_checkout_error_checks', 'lkn_give_antispam_validate_recaptcha', 9, 2);
        add_action('wp_enqueue_scripts', 'lkn_give_antispam_recaptcha_scripts');
        add_action('wp_footer', 'lkn_give_antispam_print_my_inline_script');
        add_action('give_after_donation_levels', 'lkn_give_antispam_custom_form_fields', 10, 1);
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles(): void {
        /*
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Lkn_Give_Antispam_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Lkn_Give_Antispam_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/give-antispam-public.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts(): void {
        /*
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Lkn_Give_Antispam_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Lkn_Give_Antispam_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/give-antispam-public.js', array('jquery'), $this->version, false );
    }
}

<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @see       https://www.linknacional.com.br
 * @since      1.0.0
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @author     Link Nacional
 */
final class Lkn_Antispam_For_GiveWP_Public {
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
        add_action('give_checkout_error_checks', array('Lkn_Antispam_Actions', 'validate_donation'), 10, 2);
        add_action('give_after_donation_levels', array('Lkn_Antispam_Actions', 'custom_form_fields'), 9, 1);
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
         * defined in Lkn_Antispam_For_GiveWP_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Lkn_Antispam_For_GiveWP_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/lkn-antispam-for-givewp-public.css', $this->version, 'all' );
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
         * defined in Lkn_Antispam_For_GiveWP_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Lkn_Antispam_For_GiveWP_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        if (is_singular('give_forms')) {
            $configs = Lkn_Antispam_Helper::get_configs();

            if ('enabled' === $configs['antispamEnabled']) {
                if ('enabled' === $configs['recEnabled']) {
                    $siteKey = $configs['siteRec'];
                    wp_register_script('give-recaptcha-element', 'https://www.google.com/recaptcha/api.js?render=' . $siteKey);

                    wp_enqueue_script('give-recaptcha-element');

                    wp_enqueue_script( 'lkn-antispam-for-givewp-recaptcha', plugin_dir_url( __FILE__ ) . 'js/lkn-antispam-for-givewp-recaptcha.js', array('jquery', 'give-recaptcha-element'), $this->version, false );

                    $siteKeyData = array(
                        'sitekey' => $configs['siteRec'],
                    );

                    wp_localize_script('lkn-antispam-for-givewp-recaptcha', 'skData', $siteKeyData);
                }
            }
        }

        // Uncomment if statement to control output
        // Only execute once per form.
    }
}

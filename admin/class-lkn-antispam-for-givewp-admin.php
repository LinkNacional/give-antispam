<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @see       https://www.linknacional.com.br
 * @since      1.0.0
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @author     Link Nacional
 */
final class Lkn_Antispam_For_GiveWP_Admin
{
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
     * @param string $plugin_name the name of this plugin
     * @param string $version     the version of this plugin
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('init', array($this, 'include_settings'));
    }

    /**
     * Add setting to exiting section and tab
     * If you want to add setting to existing tab and existing section then find a required filter for setting and add your logic.
     * With current code we are adding a setting field to "General" section of "General" tab.
     *
     * @param mixed $settings
     *
     * @return array
     */
    public function lkn_antispam_for_givewp_add_setting_into_existing_tab($settings)
    {
        if ( ! Give_Admin_Settings::is_setting_page('general', 'access-control')) {
            return $settings;
        }

        // Make sure you will create your own section or add new setting before array with type 'sectionend' otherwise setting field with not align properly with other setting fields.
        $newSetting = array();
        foreach ($settings as $key => $setting) {
            if ('give_docs_link' === $setting['type']) { // You can use id to compare or create own sub section to add new setting.
                $newSetting[] = array(
                    'name' => __('Enable spam donation protection', 'antispam-donation-for-givewp'),
                    'id' => 'lkn_antispam_enabled_setting_field',
                    'desc' => __('Activate or deactivate the Antispam plugin, which will block suspicious donations.', 'antispam-donation-for-givewp'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enabled', 'antispam-donation-for-givewp'),
                        'disabled' => __('Disabled', 'antispam-donation-for-givewp'),
                    ),
                );

                // Options only apears if the plugin option is 'enabled'
                if (give_get_option('lkn_antispam_enabled_setting_field') === 'enabled') {
                    $newSetting[] = array(
                        'name' => __('Enable debug', 'antispam-donation-for-givewp'),
                        'id' => 'lkn_antispam_debug_setting_field',
                        'desc' => __('Enable logs.', 'antispam-donation-for-givewp'),
                        'type' => 'radio',
                        'default' => 'disabled',
                        'options' => array(
                            'enabled' => __('Enabled', 'antispam-donation-for-givewp'),
                            'disabled' => __('Disabled', 'antispam-donation-for-givewp'),
                        ),
                    );
                    $newSetting[] = array(
                        'name' => __('Blocking due to donation quantity:', 'antispam-donation-for-givewp'),
                        'id' => 'lkn_antispam_blocking_donation_amount_setting_field',
                        'desc' => __('It is a mechanism that restricts the number of donations allowed per donor to prevent abuse.', 'antispam-donation-for-givewp'),
                        'default' => 'disabled',
                        'type' => 'radio',
                        'options' => array(
                            'enabled' => __('Enabled', 'antispam-donation-for-givewp'),
                            'disabled' => __('Disabled', 'antispam-donation-for-givewp'),
                        ),
                    );
                    $newSetting[] = array(
                        'name' => __('Time for form unlocking:', 'antispam-donation-for-givewp'),
                        'id' => 'lkn_antispam_timestamp_in_minuts',
                        'desc_tip' => true,
                        'desc' => __('Enter the time in minutes for the form unlocking', 'antispam-donation-for-givewp'),
                        'type' => 'number',
                        'default' => null,
                    );

                    $newSetting[] = array(
                        'name' => __('Banned IPs', 'antispam-donation-for-givewp'),
                        'id' => 'lkn_antispam_banned_ips_setting_field',
                        'desc' => __('Separete the IPs by jumping a line with the Enter key.', 'antispam-donation-for-givewp'),
                        'type' => 'textarea',
                    );

                    $newSetting[] = array(
                        'name' => __('Limit on donations within a time interval', 'antispam-donation-for-givewp'),
                        'id' => 'lkn_antispam_limit_setting_field',
                        'desc' => __('Number of donations a customer can make in a certain period of time.', 'antispam-donation-for-givewp'),
                        'type' => 'number',
                        'default' => '2',
                    );

                    $newSetting[] = array(
                        'name' => __('Interval between donations', 'antispam-donation-for-givewp'),
                        'id' => 'lkn_antispam_time_interval_setting_field',
                        'desc' => __('Time interval between donations a customer can make (in minutes).', 'antispam-donation-for-givewp'),
                        'type' => 'number',
                        'default' => '10',
                    );

                    $newSetting[] = array(
                        'name' => __('Limit donations to the same payment method', 'antispam-donation-for-givewp'),
                        'id' => 'lkn_antispam_same_gateway_setting_field',
                        'desc' => __('Enable to limit consecutive doantions that have the same payment methods.', 'antispam-donation-for-givewp'),
                        'type' => 'radio',
                        'default' => 'disabled',
                        'options' => array(
                            'enabled' => __('Enabled', 'antispam-donation-for-givewp'),
                            'disabled' => __('Disabled', 'antispam-donation-for-givewp'),
                        ),
                    );

                    $newSetting[] = array(
                        'name' => __('Save antispam report', 'antispam-donation-for-givewp'),
                        'id' => 'lkn_antispam_save_log_setting_field',
                        'desc' => __('Enable to save a report containing blocked spam donations.', 'antispam-donation-for-givewp') . sprintf(' <a id="lkn_log_new_tab">%s</a>', __('Blocked spam report.', 'antispam-donation-for-givewp')),
                        'type' => 'radio',
                        'default' => 'disabled',
                        'options' => array(
                            'enabled' => __('Enabled', 'antispam-donation-for-givewp'),
                            'disabled' => __('Disabled', 'antispam-donation-for-givewp'),
                        ),
                    );

                    $newSetting[] = array(
                        'name' => __('Recaptcha donation form', 'antispam-donation-for-givewp'),
                        'id' => 'lkn_antispam_active_recaptcha_setting_field',
                        'desc' => __('Enable to activate recaptcha on donation forms.', 'antispam-donation-for-givewp') . sprintf(' <a href="https://www.google.com/recaptcha/admin/" target="_blank">%s</a>', __('Generate Recaptcha V3 keys here.', 'antispam-donation-for-givewp')),
                        'type' => 'radio',
                        'default' => 'disabled',
                        'options' => array(
                            'enabled' => __('Enabled', 'antispam-donation-for-givewp'),
                            'disabled' => __('Disabled', 'antispam-donation-for-givewp'),
                        ),
                    );
                    if (give_get_option('lkn_antispam_active_recaptcha_setting_field') === 'enabled') {
                        $newSetting[] = array(
                            'name' => __('Recaptcha site key', 'antispam-donation-for-givewp'),
                            'id' => 'lkn_antispam_site_rec_id_setting_field',
                            'desc' => __('Google Recaptcha V3 service key.', 'antispam-donation-for-givewp'),
                            'type' => 'api_key',
                        );

                        $newSetting[] = array(
                            'name' => __('Recaptcha secret key', 'antispam-donation-for-givewp'),
                            'id' => 'lkn_antispam_secret_rec_id_setting_field',
                            'desc' => __('Google Recaptcha V3 secret key.', 'antispam-donation-for-givewp'),
                            'type' => 'api_key',
                        );
                        $newSetting[] = array(
                            'name' => __('Minimum score', 'antispam-donation-for-givewp'),
                            'id' => 'lkn_antispam_score_re_setting_field',
                            'desc' => __('The minimum score validated by Recaptcha for donations to be accepted. Varies between 0 and 10.', 'antispam-donation-for-givewp'),
                            'type' => 'number',
                            'default' => '5',
                        );
                    }

                    $newSetting[] = array(
                        'name' => __('Block All Donations', 'antispam-donation-for-givewp'),
                        'id' => 'lkn_antispam_disable_all_donations',
                        'desc' => __('The feature enables users to block all site forms for a custom period. When activated, it monitors site activity, such as donations. If a user-defined limit is exceeded, forms are temporarily blocked. After the set time, forms are automatically unlocked.<br><strong>Activate to customize fields</strong>.<br><a id="lkn-antispam-link" href="#">Activate your forms manually here.</a>', 'antispam-donation-for-givewp'),
                        'type' => 'radio',
                        'default' => 'disabled',
                        'options' => array(
                            'enabled' => __('Enabled', 'antispam-donation-for-givewp'),
                            'disabled' => __('Disabled', 'antispam-donation-for-givewp'),
                        ),
                    );
                    if (give_get_option('lkn_antispam_disable_all_donations') === 'enabled') {
                        $newSetting[] = array(
                            'name' => __('Time for forms to normalize.', 'antispam-donation-for-givewp'),
                            'id' => 'lkn_antispam_disable_all_interval',
                            'desc' => __('Set the time for the forms to be normalized again','antispam-donation-for-givewp'),
                            'type' => 'number',
                            'default' => 60,
                        );
                        $newSetting[] = array(
                            'name' => __('Donations limit for spam detection', 'antispam-donation-for-givewp'),
                            'id' => 'lkn_antispam_disable_all_suspect_number',
                            'desc' => __('Set the minimum number of donations within one hour to consider as suspicious spam activity.', 'antispam-donation-for-givewp'),
                            'type' => 'number',
                            'default' => 30,
                        );
                    }

                    // Options only apears if the plugin option is 'enabled'
                    if (give_get_option('lkn_antispam_save_log_setting_field') === 'enabled') {
                    }
                }

                $newSetting[] = array(
                    'id' => 'lkn_antispam',
                    'type' => 'sectionend',
                );
            }

            $newSetting[] = $setting;
        }

        return $newSetting;
    }

    // Insert settings on GiveWP settings
    public function include_settings(): void
    {
        add_filter('give_get_settings_general', array($this, 'lkn_antispam_for_givewp_add_setting_into_existing_tab'), 10, 1);
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles(): void
    {
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

        wp_enqueue_style('lkn-antispam-for-givewp-admin-css', plugin_dir_url(__FILE__) . '/css/lkn-antispam-for-givewp-admin.css', $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts(): void
    {
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

        wp_enqueue_script('lkn-antispam-for-givewp-admin-js', plugin_dir_url(__FILE__) . '/js/lkn-antispam-for-givewp-admin.js', array('jquery'), $this->version, false);
        wp_enqueue_script('lkn-antispam-for-givewp-link.js', plugin_dir_url(__FILE__) . '/js/lkn-antispam-for-givewp-link.js');

        wp_localize_script('lkn-antispam-for-givewp-link.js', 'link', array('href' => get_permalink(Lkn_Antispam_Helper::create_custom_page())));
    }
}

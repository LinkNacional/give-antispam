<?php

/**
 * Give - Antispam Frontend Actions.
 *
 * @since 1.0.0
 *
 * @copyright  Copyright (c) 2021, Link Nacional
 * @license    https://opensource.org/license/gpl-3-0/ GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined('WPINC')) {
    exit;
}

final class Lkn_Antispam_Actions {
    /**
     * Makes a .log file for each spam report.
     *
     * @param string $message
     * @param array  $configs
     */
    public static function reg_report($message, $configs): void {
        if ('enabled' === $configs['reportSpam']) {
            error_log($message, 3, $configs['baseReport']);

            $size = filesize($configs['baseReport']);

            chmod($configs['baseReport'], 0600);

            if ($size > 2000) { // 2Kb
                unlink($configs['baseReport']);
            }
        }
    }

    /**
     * Makes a .log file for each donation.
     *
     * @param string|array $log
     * @param array        $configs
     */
    public static function reg_log($log, $configs): void {
        if ('enabled' === $configs['debug']) {
            $jsonLog = json_encode($log, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE) . "\n";

            error_log($jsonLog, 3, $configs['base']);
            chmod($configs['base'], 0600);
        }
    }

    /**
     * Get the Recaptcha min score.
     *
     * @return float
     */
    public static function get_recaptcha_score() {
        $score = give_get_option('lkn_antispam_score_re_setting_field');

        if ($score < 0 || $score > 10) {
            return 0.5;
        }

        return (float) ($score / 10);
    }

    /**
     * Gets the time interval from settings.
     *
     * @return int
     */
    public static function get_time_interval() {
        $timeInterval = give_get_option('lkn_antispam_time_interval_setting_field');

        if ($timeInterval < 0) {
            return 0;
        }

        return $timeInterval;
    }

    /**
     * Validate Donation and mark as spam.
     *
     * @param mixed $valid_data
     * @param mixed $data
     *
     * @return array
     */
    public static function validate_donation($valid_data, $data) {
        $configs = Lkn_Antispam_Helper::get_configs();

        // Verify if plugin is active
        if ('enabled' === $configs['antispamEnabled']) {
            // Get the save spam-log option
            $reportSpam = $configs['reportSpam'];

            $bannedIps = explode(\PHP_EOL, $configs['bannedIps']);

            // Get current user ip
            $userIp = give_get_ip();

            if (in_array($userIp, $bannedIps, true)) {
                Lkn_Antispam_Actions::reg_report(date('d.m.Y-H.i.s') . ' - [IP] ' . var_export($userIp, true) . ' [Payment] ' . var_export($valid_data['gateway'], true) . ' - PAYMENT DENIED, BANNED IP  <br> ' . \PHP_EOL, $configs);

                return give_set_error('g-recaptcha-response', __('Your IP address is banned.', 'antispam-donation-for-givewp'));
            }

            // Get givewp payment data
            $payments = give_get_payments();

            // Get the current dateTime and the time limit in minutes
            $actualDate = new DateTime(current_time('mysql'));
            $timeLimit = absint($configs['interval']);

            // Get donation limit and the donation counter
            $donationLimit = absint($configs['donationLimit']) - 1;
            $donationCounter = 0;

            // The donation list ip and date
            $donationIp = array();
            $dates = array();

            $gatewayVerification = $configs['gatewayVerify'];

            $paymentInfo = array();

            // Verify the last 20 payments
            for ($c = 0; $c < count($payments); ++$c) {
                // Get the GiveWP payment info
                $paymentId = $payments[$c]->ID;
                $dates[] = $payments[$c]->post_date;
                $donationIp[] = give_get_payment_user_ip($paymentId);
                $paymentInfo[] = give_get_payment_by('id', $paymentId);

                // Verify if the saved donation IP is equal to the actual user IP
                if ($donationIp[$c] == $userIp) {
                    // Initializes a DateTime object with the donation saved date
                    $donationDate = new DateTime($dates[$c]);
                    // Verify the time interval between the actual date and the saved date
                    $dateInterval = $actualDate->diff($donationDate);

                    // Convert time to minutes
                    $minutes = $dateInterval->days * 24 * 60;
                    $minutes += $dateInterval->h * 60;
                    $minutes += $dateInterval->i;

                    // Verify if the donations interval is greater than timeLimit specified in the admin-settings
                    if ($minutes < $timeLimit) {
                        // Checks the gateway verification option is enabled
                        if ('enabled' === $gatewayVerification) {
                            // Verifies the current gateway with the donation gateway
                            if ($paymentInfo[$c]->gateway === $valid_data['gateway']) {
                                // Verify if the user has made another donation in the time interval
                                if ($donationLimit > $donationCounter) {
                                    ++$donationCounter;
                                } else {
                                    Lkn_Antispam_Actions::reg_report(date('d.m.Y-H.i.s') . ' - [IP] ' . var_export($userIp, true) . ' [Payment] ' . var_export($valid_data['gateway'], true) . ' - PAYMENT DENIED, TOO MANY ATTEMPTS  <br> ' . \PHP_EOL, $configs);
                                    give_set_error('g-recaptcha-response', __('The email you are using has been flagged as being used in SPAM donations by our system. Contact the site administrator if you have any questions.', 'antispam-donation-for-givewp'));
                                }
                            }
                        } else {
                            // Verify if the user has made another donation in the time interval
                            if ($donationLimit > $donationCounter) {
                                ++$donationCounter;
                            } else {
                                Lkn_Antispam_Actions::reg_report(date('d.m.Y-H.i.s') . ' - [IP] ' . var_export($userIp, true) . ' [Payment] ' . var_export($valid_data['gateway'], true) . ' - PAYMENT DENIED, TOO MANY ATTEMPTS  <br> ' . \PHP_EOL, $configs);
                                give_set_error('g-recaptcha-response', __('The email you are using has been flagged as being used in SPAM donations by our system. Contact the site administrator if you have any questions.', 'antispam-donation-for-givewp'));
                            }
                        }
                    }
                }
            }

            return Lkn_Antispam_Actions::reg_log(array(
                'ip' => $userIp,
                'donation_ip' => $donationIp,
                'timestamp_interval' => $minutes,
                'form_id' => $data['give-form-id'],
            ), $configs);
        }

        return $valid_data;
    }

    /**
     * Implementing Google's ReCaptcha on All Give Forms V3.
     *
     *  To effectively use this snippet, please do the following:
     *  1. Get your Google ReCAPTCHA API Key here: https://www.google.com/recaptcha/admin/create
     *  2. In each function and action, replace "_myprefix_" with your own custom prefix
     *  3. Put your "Secret Key" where it says "MYSECRETKEY" in the $recaptcha_secret_key string
     *  4. Put your "Site Key" where it says "MYSITEKEY" in TWO areas below
     *
     * @param mixed $valid_data
     * @param mixed $data
     */

    /**
     * Validate ReCAPTCHA.
     *
     * @return array
     */
    public static function validate_recaptcha($valid_data, $data) {
        $configs = Lkn_Antispam_Helper::get_configs();
        // Verify if the plugin is enabled and ensure that it only runs once.
        if ('enabled' === $configs['antispamEnabled'] && ! isset($data['give_ajax'])) {
            // Verify if the Recaptcha option is enabled.
            if ('enabled' === $configs['recEnabled']) {
                $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
                $recaptcha_secret_key = $configs['secretRec']; // Replace with your own key here.
                // Request for Recaptcha verification.
                $recaptcha_response = wp_remote_post($recaptcha_url . '?secret=' . $recaptcha_secret_key . '&response=' . $data['g-recaptcha-response'] . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
                // Format the received response into an object.
                $recaptcha_data = json_decode(wp_remote_retrieve_body($recaptcha_response));

                Lkn_Antispam_Actions::reg_log(array(
                    'give_ajax' => $data['give_ajax'],
                    'recaptcha_response' => $recaptcha_data,
                ), $configs);

                // Verify if the request was completed successfully.
                if ( ! isset($recaptcha_data->success) || false == $recaptcha_data->success) {
                    // User must have validated the reCAPTCHA to proceed with donation.
                    if ( ! isset($data['g-recaptcha-response']) || empty($data['g-recaptcha-response'])) {
                        give_set_error('g-recaptcha-response', __('The reCAPTCHA was not verified, try again.', 'antispam-donation-for-givewp'));
                    }
                } elseif ( ! isset($recaptcha_data->score) || $recaptcha_data->score < $configs['scoreRec']) {
                    // If the score is lower than the defined value, display an error message.
                    give_set_error('g-recaptcha-response', __('The reCAPTCHA was not verified, try again.', 'antispam-donation-for-givewp'));
                }
            }
        }

        return $valid_data;
    }

    /**
     * Custom ReCAPTCHA Form Field.
     * This function adds the reCAPTCHA field above the "Donation Total" field.
     * Don't forget to update the sitekey!
     *
     * @param mixed $form_id
     */
    public static function custom_form_fields($form_id): void {
        $configs = Lkn_Antispam_Helper::get_configs();
        if ('enabled' === $configs['antispamEnabled']) {
            if ('enabled' === $configs['recEnabled']) {
                $siteKey = $configs['siteRec'];
                // Add you own google API Site key.
                // $recResponse = sanitize_text_field($_POST['g-recaptcha-lkn-input']);
                $html = <<<'HTML'
            
			<div id="g-notice-wrapper" class="gNotice">
                This site is protected by reCAPTCHA and the <a href="https://policies.google.com/privacy" target="_blank">Privacy Policy</a> and Google <a href="https://policies.google.com/terms" target="_blank">Terms of Service</a> apply.
            </div>
            
            <input type="hidden" id="g-recaptcha-lkn-input" name="g-recaptcha-response" />

HTML;
                echo $html;
            }
        }
    }
}

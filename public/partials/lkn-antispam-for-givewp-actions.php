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

final class Lkn_Antispam_Actions
{
    /**
     * Makes a .log file for each spam report.
     *
     * @param string $message
     * @param array  $configs
     */
    public static function reg_report($message, $configs): void
    {
        if ('enabled' === $configs['reportSpam']) {
            error_log($message, 3, $configs['baseReport']);

            $size = filesize($configs['baseReport']);

            chmod($configs['baseReport'], 0600);

            if ($size > 2000) { // 2Kb
                wp_delete_file($configs['baseReport']);
            }
        }
    }

    /**
     * Makes a .log file for each donation.
     *
     * @param string|array $log
     * @param array        $configs
     */
    public static function reg_log($log, $configs): void
    {
        if ('enabled' === $configs['debug']) {
            $jsonLog = wp_json_encode($log, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE) . "\n";

            error_log($jsonLog, 3, $configs['base']);
            chmod($configs['base'], 0600);
        }
    }

    /**
     * Get the Recaptcha min score.
     *
     * @return float
     */
    public static function get_recaptcha_score()
    {
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
    public static function get_time_interval()
    {
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
    public static function validate_donation($valid_data, $data)
    {
        $configs = Lkn_Antispam_Helper::get_configs();

        if (self::is_plugin_active_and_not_ajax($configs, $data)) {
            $userIp = give_get_ip();

            if (self::is_ip_banned($configs, $userIp)) {
                self::handle_banned_ip($configs, $valid_data, $userIp);
                do_action('lkn_give_antispam_spam_detected');
                Lkn_Antispam_Actions::time_for_spam_detected();

                return $valid_data;
            }

            if (self::has_too_many_donations($configs, $valid_data, $userIp)) {
                do_action('lkn_give_antispam_spam_detected');
                Lkn_Antispam_Actions::time_for_spam_detected();

                return $valid_data;
            }
        }

        Lkn_Antispam_Actions::validate_recaptcha($valid_data, $data);

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
    public static function validate_recaptcha($valid_data, $data)
    {
        $configs = Lkn_Antispam_Helper::get_configs();

        if (self::is_plugin_active_and_not_ajax($configs, $data) && self::is_recaptcha_enabled($configs)) {
            $recaptcha_response = self::get_recaptcha_response($configs, $data);
            self::log_recaptcha_response($recaptcha_response, $data, $configs);

            if ( ! self::is_recaptcha_valid($recaptcha_response, $configs, $data)) {
                give_set_error('g-recaptcha-response', __('The reCAPTCHA was not verified, try again.', 'antispam-donation-for-givewp'));
            }
        }
    }

    /**
     * Custom ReCAPTCHA Form Field.
     * This function adds the reCAPTCHA field above the "Donation Total" field.
     * Don't forget to update the sitekey!
     *
     * @param mixed $form_id
     */
    public static function custom_form_fields($form_id): void
    {
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
                echo esc_html($html);
            }
        }
    }

    // lkn_give_antispam_timeout_for_spam_detected function

    public static function time_for_spam_detected(): void
    {
        $hook = 'lkn_give_antispam_spam_detected_hook';
        // Verificar se o cron job já está agendado
        $timestamp = wp_next_scheduled($hook);

        // Se o cron job já estiver agendado, desagende-o
        if (false !== $timestamp) {
            wp_unschedule_event($timestamp, $hook);
        }

        // Obter o valor da opção customizada
        $custom_cron = give_get_option('lkn_give_antispam_timestamp_in_minuts');

        // Agendar o evento cron de acordo com o valor da opção customizada
        $schedule = ($custom_cron > 0) ? 'custom_cron' : 'hourly';
        wp_schedule_event(time(), $schedule, $hook);

        // Atualizar a opção indicando que o cron job foi agendado
        give_update_option('lkn_give_antispam_spam_detected', true);
        $disable_form = give_get_option('lkn_give_antispam_disable_form');
        if ($disable_form) {
            do_action('lkn_give_antispam_disable_form');
        }
    }

    public static function alter_status_spam(): void
    {
        give_update_option('lkn_give_antispam_spam_detected', false);

        // Depois que a ação for executada, remova o cron job
        $cron_hook = 'lkn_give_antispam_spam_detected_hook';
        wp_unschedule_event(wp_next_scheduled($cron_hook), $cron_hook);
    }

    private static function many_donations_in_top_200($configs, $data, $userIp)
    {
        $payments = give_get_payments();
        $actualDate = new DateTime(current_time('mysql'));
        $timeLimit = absint($configs['interval']);
        $donationLimit = absint($configs['donationLimit']) - 1;
        $donationCounter = 0;
        $blockDonations = 'enabled' === $configs['blockDonation'];

        for ($c = 0; $c < count($payments) && $c < 20; ++$c) {
            $payment = $payments[$c];
            $paymentId = $payment->ID;
            $donationIp = give_get_payment_user_ip($paymentId);

            if ($donationIp === $userIp) {
                if (self::is_donation_within_time_limit($actualDate, $payment->post_date, $timeLimit)) {
                    if ($blockDonations && ! self::can_accept_donation($configs, $data, $donationCounter, $donationLimit, $payment)) {
                        return true;
                    }
                    ++$donationCounter;
                }
            }
        }
    }

    // Geral function
    private static function is_plugin_active_and_not_ajax($configs, $data)
    {
        return 'enabled' === $configs['antispamEnabled'] && ! isset($data['give_ajax']);
    }

    // Validate donation functions
    private static function is_ip_banned($configs, $userIp)
    {
        $bannedIps = explode(\PHP_EOL, $configs['bannedIps']);

        return in_array($userIp, $bannedIps, true);
    }

    private static function handle_banned_ip($configs, $valid_data, $userIp): void
    {
        if ('enabled' === $configs['reportSpam']) {
            self::report_spam($configs, $valid_data, $userIp, 'BANNED IP');
        }

        give_set_error('g-recaptcha-response', __('Your IP address is banned.', 'antispam-donation-for-givewp'));
    }

    private static function has_too_many_donations($configs, $valid_data, $userIp)
    {
        $payments = give_get_payments();
        $actualDate = new DateTime(current_time('mysql'));
        $timeLimit = absint($configs['interval']);
        $donationLimit = absint($configs['donationLimit']) - 1;
        $donationCounter = 0;
        $blockDonations = 'enabled' === $configs['blockDonation'];

        for ($c = 0; $c < count($payments) && $c < 20; ++$c) {
            $payment = $payments[$c];
            $paymentId = $payment->ID;
            $donationIp = give_get_payment_user_ip($paymentId);

            if ($donationIp === $userIp) {
                if (self::is_donation_within_time_limit($actualDate, $payment->post_date, $timeLimit)) {
                    if ($blockDonations && ! self::can_accept_donation($configs, $valid_data, $donationCounter, $donationLimit, $payment)) {
                        return true;
                    }
                    ++$donationCounter;
                }
            }
        }

        return false;
    }

    private static function is_donation_within_time_limit($actualDate, $donationDate, $timeLimit)
    {
        $donationDate = new DateTime($donationDate);
        $dateInterval = $actualDate->diff($donationDate);

        $minutes = ($dateInterval->days * 24 * 60) + ($dateInterval->h * 60) + $dateInterval->i;

        return $minutes < $timeLimit;
    }

    private static function can_accept_donation($configs, $valid_data, $donationCounter, $donationLimit, $payment)
    {
        if ($donationLimit > $donationCounter) {
            return true;
        }

        self::report_spam($configs, $valid_data, give_get_ip(), 'TOO MANY ATTEMPTS');
        give_set_error('g-recaptcha-response', __('The email you are using has been flagged as being used in SPAM donations by our system. Contact the site administrator if you have any questions.', 'antispam-donation-for-givewp'));

        return false;
    }

    private static function report_spam($configs, $valid_data, $userIp, $reason): void
    {
        Lkn_Antispam_Actions::reg_report(
            gmdate('d.m.Y-H.i.s') . ' - [IP] ' . var_export($userIp, true) .
                ' [Payment] ' . var_export($valid_data['gateway'], true) .
                ' - PAYMENT DENIED, ' . $reason . ' <br> ' . \PHP_EOL,
            $configs
        );
    }

    // Recaptcha functions
    private static function is_recaptcha_enabled($configs)
    {
        return 'enabled' === $configs['recEnabled'];
    }

    private static function get_recaptcha_response($configs, $data)
    {
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_secret_key = $configs['secretRec'];

        $response = wp_remote_post($recaptcha_url . '?secret=' . $recaptcha_secret_key . '&response=' . $data['g-recaptcha-response'] . '&remoteip=' . $_SERVER['REMOTE_ADDR']);

        return json_decode(wp_remote_retrieve_body($response));
    }

    private static function log_recaptcha_response($recaptcha_response, $data, $configs): void
    {
        Lkn_Antispam_Actions::reg_log(array(
            'give_ajax' => $data['give_ajax'],
            'recaptcha_response' => $recaptcha_response,
        ), $configs);
    }

    private static function is_recaptcha_valid($recaptcha_response, $configs, $data)
    {
        if ( ! isset($recaptcha_response->success) || ! $recaptcha_response->success) {
            return false;
        }

        if ( ! isset($recaptcha_response->score) || $recaptcha_response->score < $configs['scoreRec']) {
            return false;
        }

        return true;
    }
}

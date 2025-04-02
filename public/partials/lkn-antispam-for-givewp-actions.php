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
use Give\LegacyPaymentGateways\Adapters\LegacyPaymentGatewayAdapter;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Log\LogFactory;

final class Lkn_Antispam_Actions {
    /**
     * Makes a .log file for each spam report.
     *
     * @param string $message
     * @param array  $configs
     */
    public static function regLog($configs, $data): void {
        if ('enabled' == $configs['debug']) {
            $logFactory = new LogFactory();
            $log = $logFactory->make(
                'info',
                'Give Antispam Log',
                'Antispam',
                'Give Antispam',
                $data
            );
            $log->save();
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
        try {
            if (self::is_plugin_active_and_not_ajax($configs, $data)) {
            
                $userIp = give_get_ip();
    
                if (self::is_ip_banned($configs, $userIp)) {
                    do_action('lkn__antispam_spam_detected');
                    Lkn_Antispam_Actions::time_for_spam_detected();
                    throw new PaymentGatewayException(esc_attr(__('Your IP address is banned.', 'antispam-donation-for-givewp')));
    
                }
    
                if (self::has_too_many_donations($configs, $valid_data, $userIp)) {
                    do_action('lkn__antispam_spam_detected');
                    self::time_for_spam_detected();
                    throw new PaymentGatewayException(esc_attr(__('The email you are using has been flagged as being used in SPAM donations by our system. Contact the site administrator if you have any questions.', 'antispam-donation-for-givewp')));
    
                }
                if (self::many_donations_in_top($configs, $data)) {
                    self::spam_detected_block_all();
    
                }
    
                if(Lkn_Antispam_Actions::validate_recaptcha($valid_data, $_POST)){
                    throw new PaymentGatewayException(esc_attr(__('The reCAPTCHA was not verified, try again.', 'antispam-donation-for-givewp')));
                }
            }
        } catch (Exception $exception) {
            $legacyPaymentGatewayAdapter = new LegacyPaymentGatewayAdapter();
            $legacyPaymentGatewayAdapter->handleExceptionResponse($exception, $exception->getMessage());
        }

        return $valid_data;
    }

    public static function validate_donation_react_form($formData, $donation) {
        $configs = Lkn_Antispam_Helper::get_configs();
        
        $userIp = give_get_ip();

        if (self::is_ip_banned($configs, $userIp)) {
            do_action('lkn__antispam_spam_detected');
            Lkn_Antispam_Actions::time_for_spam_detected();

            throw new PaymentGatewayException(esc_attr(__('Your IP address is banned.', 'antispam-donation-for-givewp')));
        }
        
        if (self::has_too_many_donations($configs, $formData, $userIp)) {
            do_action('lkn__antispam_spam_detected');
            self::time_for_spam_detected();
            
            throw new PaymentGatewayException(esc_attr(__('The email you are using has been flagged as being used in SPAM donations by our system. Contact the site administrator if you have any questions.', 'antispam-donation-for-givewp')));
        }
        if (self::many_donations_in_top($configs)) {
            self::spam_detected_block_all();
        }

        if(Lkn_Antispam_Actions::validate_recaptcha($donation, $_POST)){
            throw new PaymentGatewayException(esc_attr(__('The reCAPTCHA was not verified, try again.', 'antispam-donation-for-givewp')));
        }
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

        if (self::is_plugin_active_and_not_ajax($configs, $data) && self::is_recaptcha_enabled($configs)) {
            $recaptcha_response = self::get_recaptcha_response($configs, $data);
            self::log_recaptcha_response($recaptcha_response, $data, $configs);

            if ( ! self::is_recaptcha_valid($recaptcha_response, $configs, $data)) {
                return true;
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
    public static function custom_form_fields($form_id): void {
        $configs = Lkn_Antispam_Helper::get_configs();
        if ('enabled' === $configs['antispamEnabled']) {
            if ('enabled' === $configs['recEnabled']) {
                $siteKey = $configs['siteRec'];
                // Add you own google API Site key.
                // $recResponse = sanitize_text_field($_POST['g-recaptcha-lkn-input']);
                $html = '
            
			<div id="g-notice-wrapper" class="gNotice">
                This site is protected by reCAPTCHA and the <a href="https://policies.google.com/privacy" target="_blank">Privacy Policy</a> and Google <a href="https://policies.google.com/terms" target="_blank">Terms of Service</a> apply.
            </div>
            
            <input type="hidden" id="g-recaptcha-lkn-input" name="g-recaptcha-response" />

';

                $allowed_html = array(
                    'div' => array(
                        'id' => true,
                        'class' => true
                    ),
                    'a' => array(
                        'href' => true,
                        'target' => true
                    ),
                    'input' => array(
                        'type' => true,
                        'id' => true,
                        'name' => true
                    )
                );
                echo wp_kses($html, $allowed_html);
            }
        }
    }

    public static function time_for_spam_detected(): void {
        $hook = 'lkn__antispam_spam_detected_hook';
        // Verificar se o cron job já está agendado
        $timestamp = wp_next_scheduled($hook);

        // Se o cron job já estiver agendado, desagende-o
        if (false !== $timestamp) {
            wp_unschedule_event($timestamp, $hook);
        }

        // Obter o valor da opção customizada
        $custom_cron = give_get_option('lkn_antispam_timestamp_in_minuts');

        // Agendar o evento cron de acordo com o valor da opção customizada
        $schedule = ($custom_cron > 0) ? 'custom_cron' : 'hourly';
        wp_schedule_event(time(), $schedule, $hook);

        // Atualizar a opção indicando que o cron job foi agendado
        give_update_option('lkn__antispam_spam_detected', true);
        $disable_form = give_get_option('lkn__antispam_disable_form');
        if ($disable_form) {
            self::alter_status_spam();
        }
    }

    public static function alter_status_spam(): void {
        give_update_option('lkn_antispam_spam_detected', false);

        // Depois que a ação for executada, remova o cron job
        $cron_hook = 'lkn__antispam_spam_detected_hook';
        wp_unschedule_event(wp_next_scheduled($cron_hook), $cron_hook);
    }

    private static function many_donations_in_top($configs) {
        if (give_get_option('lkn_antispam_disable_all_donations') == 'enabled') {
            $payments = give_get_payments();  // Verifique se esta função pode ser limitada a um número específico de pagamentos
            $actualDate = new DateTime(current_time('mysql'));
            $userDefineRepeat = give_get_option('lkn_antispam_disable_all_suspect_number');
            $timeLimit = 60; // Limite de tempo em minutos
            $countLimit = $userDefineRepeat;

            $count = 0;
            $paymentCountLimit = count(array_slice($payments, -60));

            for ($c = 0; $c < $paymentCountLimit; ++$c) {
                $payment = $payments[$c];
                // Convertendo as datas para objetos DateTime
                $donationDate = new DateTime($payment->post_date);
                // Calculando a diferença em minutos entre as datas
                $dateInterval = $actualDate->diff($donationDate);

                $minutes = ($dateInterval->days * 24 * 60) + ($dateInterval->h * 60) + $dateInterval->i;

                // Verifica se a doação está dentro do limite de tempo
                if ($minutes < $timeLimit) {
                    ++$count;
                }
                // Verifica se o limite de doações suspeitas foi excedido
                if ($count == $countLimit) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function spam_detected_block_all(): void {
        // Atualizar a opção para indicar que o spam foi detectado e bloqueado
        give_update_option('lkn_antispam_spam_detected_block_all', true);

        // Nome do gancho para o evento cron
        $hook = 'lkn_antispam_spam_detected_block_all_event';

        // Verificar se o cron job já está agendado
        $timestamp = wp_next_scheduled($hook);
        $interval = give_get_option('lkn_antispam_disable_all_interval', 60);
        // Se o cron job já estiver agendado, desagende-o
        if (false !== $timestamp) {
            wp_schedule_single_event(time() + ($interval * 60), $hook);
        }

        // Agendar o evento cron para ser executado a cada hora
        wp_schedule_single_event(time() + ($interval * 60), $hook);
    }

    // Geral function
    private static function is_plugin_active_and_not_ajax($configs, $data) {
        return 'enabled' === $configs['antispamEnabled'] && ! isset($data['give_ajax']);
    }

    // Validate donation functions
    private static function is_ip_banned($configs, $userIp) {
        $bannedIps = explode(\PHP_EOL, $configs['bannedIps']);

        return in_array($userIp, $bannedIps, true);
    }

    private static function has_too_many_donations($configs, $valid_data, $userIp) {
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

    private static function is_donation_within_time_limit($actualDate, $donationDate, $timeLimit) {
        $donationDate = new DateTime($donationDate);
        $dateInterval = $actualDate->diff($donationDate);

        $minutes = ($dateInterval->days * 24 * 60) + ($dateInterval->h * 60) + $dateInterval->i;

        return $minutes < $timeLimit;
    }

    private static function can_accept_donation($configs, $valid_data, $donationCounter, $donationLimit, $payment) {
        if ($donationLimit > $donationCounter) {
            return true;
        }

        self::report_spam($configs, $valid_data, give_get_ip(), 'TOO MANY ATTEMPTS');

        return false;
    }

    private static function report_spam($configs, $valid_data, $userIp, $reason): void {
        Lkn_Antispam_Actions::regLog($configs, array(
            'valid_data' => $valid_data,
            'userIp' => $userIp,
            'reason' => $reason,
            'configs' => $configs
        ));
    }

    // Recaptcha functions
    private static function is_recaptcha_enabled($configs) {
        return 'enabled' === $configs['recEnabled'];
    }

    private static function get_recaptcha_response($configs, $data) {
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_secret_key = $configs['secretRec'];
        $ip_address = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        $response = wp_remote_post($recaptcha_url . '?secret=' . $recaptcha_secret_key . '&response=' . $data['g-recaptcha-response'] . '&remoteip=' . $ip_address);

        return json_decode(wp_remote_retrieve_body($response));
    }

    private static function log_recaptcha_response($recaptcha_response, $data, $configs): void {
        $give_ajax = isset($data['give_ajax']) ? sanitize_text_field(wp_unslash($data['give_ajax'])) : '';

        Lkn_Antispam_Actions::regLog($configs, array(
            'give_ajax' => $give_ajax,
            'recaptcha_response' => $recaptcha_response,
        ));
    }

    private static function is_recaptcha_valid($recaptcha_response, $configs, $data) {
        if ( ! isset($recaptcha_response->success) || ! $recaptcha_response->success) {
            return false;
        }

        if ( ! isset($recaptcha_response->score) || $recaptcha_response->score < $configs['scoreRec']) {
            return false;
        }

        return true;
    }
}

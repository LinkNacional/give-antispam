<?php

/**
 * Give - Antispam Frontend Actions
 *
 * @since 2.5.0
 *
 * @package    Give
 * @copyright  Copyright (c) 2021, Link Nacional
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// ========== PLUGIN HELPERS ==========

/**
 * This function centralizes the data in one spot for ease mannagment
 *
 * @return array
 */
function lkn_give_antispam_get_configs() {
    $configs = [];

    $configs['basePath'] = __DIR__ . '/../logs';
    $configs['base'] = $configs['basePath'] . '/' . date('d.m.Y-H.i.s') . '.log';
    $configs['baseReport'] = $configs['basePath'] . '/ip-spam.log';

    // Internal debug option
    $configs['debug'] = false;
    // External report log option
    $configs['reportSpam'] = lkn_give_antispam_get_report_spam();

    $configs['antispamEnabled'] = lkn_give_antispam_get_enabled();
    $configs['interval'] = lkn_give_antispam_get_time_interval();
    $configs['donationLimit'] = lkn_give_antispam_get_donation_limit();
    $configs['gatewayVerify'] = lkn_give_antispam_get_gateway_verification();
    // Recaptcha keys
    $configs['recEnabled'] = lkn_give_antispam_get_recaptcha_enabled();
    $configs['siteRec'] = lkn_give_antispam_get_rec_id();
    $configs['secretRec'] = lkn_give_antispam_get_rec_secret();
    $configs['scoreRec'] = lkn_give_antispam_get_recaptcha_score();

    return $configs;
}

/**
 * Makes a .log file for each spam report
 *
 * @return void
 */
function lkn_give_antispam_reg_report($message, $configs) {
    error_log($message, 3, $configs['baseReport']);

    chmod($configs['baseReport'], 0600);
}

/**
 * Makes a .log file for each donation
 *
 * @return void
 */
function lkn_give_antispam_reg_log($message, $configs) {
    error_log($message, 3, $configs['base']);

    chmod($configs['base'], 0600);
}

/**
 * Delete the log files older than 5 days
 *
 * @return void
 */
function lkn_give_antispam_delete_old_logs() {
    $configs = lkn_give_antispam_get_configs();
    $logsPath = $configs['basePath'];

    foreach (scandir($logsPath) as $logFilename) {
        if ($logFilename !== '.' && $logFilename !== '..' && $logFilename !== 'index.php') {
            $logDate = explode('-', $logFilename)[0];
            $logDate = explode('.', $logDate);

            $logDay = $logDate[0];
            $logMonth = $logDate[1];
            $logYear = $logDate[2];

            $logDate = $logYear . '-' . $logMonth . '-' . $logDay;

            $logDate = new DateTime($logDate);
            $now = new DateTime(date('Y-m-d'));

            $interval = $logDate->diff($now);
            $logAge = $interval->format('%a');

            if ($logAge >= 5) {
                unlink($logsPath . '/' . $logFilename);
            }
        }
    }
}

/**
 * Checks if the antispam report log is enabled
 *
 * @return string enabled | disabled
 *
 */
function lkn_give_antispam_get_report_spam() {
    $reportEnabled = give_get_option('lkn_antispam_save_log_setting_field');

    return $reportEnabled;
}

/**
 * Get the Recaptcha min score
 *
 * @return float $score
 *
 */
function lkn_give_antispam_get_recaptcha_score() {
    $score = give_get_option('lkn_antispam_score_re_setting_field');

    if ($score < 0 || $score > 10) {
        return 0.5;
    } else {
        return floatval($score / 10);
    }
}

/**
 * Checks if the antispam is enabled
 *
 * @return string enabled | disabled
 *
 */
function lkn_give_antispam_get_enabled() {
    $enabled = give_get_option('lkn_antispam_enabled_setting_field');

    return $enabled;
}

/**
 * Checks if the recaptcha is enabled
 *
 * @return string enabled | disabled
 *
 */
function lkn_give_antispam_get_recaptcha_enabled() {
    $enabled = give_get_option('lkn_antispam_active_recaptcha_setting_field');

    return $enabled;
}

/**
 * Get the recaptcha site key
 *
 * @return string $siteId
 *
 */
function lkn_give_antispam_get_rec_id() {
    $siteId = give_get_option('lkn_antispam_site_rec_id_setting_field');

    return $siteId;
}

/**
 * Get the recaptcha secret key
 *
 * @return string $recSecret
 *
 */
function lkn_give_antispam_get_rec_secret() {
    $recSecret = give_get_option('lkn_antispam_secret_rec_id_setting_field');

    return $recSecret;
}

/**
 * Checks if the gateway verification is enabled
 *
 * @return string enabled | disabled
 *
 */
function lkn_give_antispam_get_gateway_verification() {
    $gatewayVerification = give_get_option('lkn_antispam_same_gateway_setting_field');

    return $gatewayVerification;
}

/**
 * Gets the time interval from settings
 *
 * @return integer $timeInterval
 *
 */
function lkn_give_antispam_get_time_interval() {
    $timeInterval = give_get_option('lkn_antispam_time_interval_setting_field');

    if ($timeInterval < 0) {
        return 0;
    } else {
        return $timeInterval;
    }
}

/**
 * User donation limit
 *
 * @return integer $donationLimit
 */
function lkn_give_antispam_get_donation_limit() {
    $donationLimit = give_get_option('lkn_antispam_limit_setting_field');

    return $donationLimit;
}

/**
 * Validate Donation and mark as spam
 *
 * @param $valid_data
 * @param $data
 *
 * @return array $valid_data
 */
function lkn_give_antispam_validate_donation($valid_data, $data) {
    $configs = lkn_give_antispam_get_configs();

    // Verify if plugin is active
    if ($configs['antispamEnabled'] === 'enabled') {
        // Get the save spam-log option
        $reportSpam = $configs['reportSpam'];

        // Get current user ip
        $userIp = give_get_ip();

        // Get givewp payment data
        $payments = give_get_payments();

        // Get the current dateTime and the time limit in minutes
        $actualDate = new DateTime(current_time('mysql'));
        $timeLimit = absint($configs['interval']);

        // Get donation limit and the donation counter
        $donationLimit = absint($configs['donationLimit']) - 1;
        $donationCounter = 0;

        // The donation list ip and date
        $donationIp = [];
        $dates = [];

        $gatewayVerification = $configs['gatewayVerify'];

        $paymentInfo = [];

        // Verify the last 20 payments
        for ($c = 0; $c < count($payments); $c++) {
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
                    if ($gatewayVerification === 'enabled') {
                        // Verifies the current gateway with the donation gateway
                        if ($paymentInfo[$c]->gateway === $valid_data['gateway']) {
                            // Verify if the user has made another donation in the time interval
                            if ($donationLimit > $donationCounter) {
                                $donationCounter++;
                            } else {
                                if ($reportSpam === 'enabled') {
                                    lkn_give_antispam_reg_report(date('d.m.Y-H.i.s') . ' - [IP] ' . var_export($userIp, true) . ' [Payment] ' . var_export($valid_data['gateway'], true) . ' - PAYMENT DENIED ' . ' <br> ' . PHP_EOL, $configs);
                                }
                                give_set_error('spam_donation', 'O e-mail que você está usando foi sinalizado como sendo usado em comentários de SPAM ou doações por nosso sistema. Tente usar um endereço de e-mail diferente ou entre em contato com o administrador do site se tiver alguma dúvida.');
                            }
                        }
                    } else {
                        // Verify if the user has made another donation in the time interval
                        if ($donationLimit > $donationCounter) {
                            $donationCounter++;
                        } else {
                            if ($reportSpam === 'enabled') {
                                lkn_give_antispam_reg_report(date('d.m.Y-H.i.s') . ' - [IP] ' . var_export($userIp, true) . ' [Payment] ' . var_export($valid_data['gateway'], true) . ' - PAYMENT DENIED ' . ' <br> ' . PHP_EOL, $configs);
                            }
                            give_set_error('spam_donation', 'O e-mail que você está usando foi sinalizado como sendo usado em comentários de SPAM ou doações por nosso sistema. Tente usar um endereço de e-mail diferente ou entre em contato com o administrador do site se tiver alguma dúvida.');
                        }
                    }
                }
            }
        }

        // Activates debug mode and saves a temporary log
        if ($configs['debug'] === true) {
            lkn_give_antispam_delete_old_logs();

            // lkn_give_antispam_reg_log(' || my ip || ' . var_export($userIp, true) . ' || donation ip || ' . var_export($donationIp, true) . ' || timestamp interval || ' . var_export($minutes, true), $configs);
            lkn_give_antispam_reg_log(' || valid data || ' . var_export($valid_data, true) . ' || raw data || ' . var_export($data, true) . ' || payments || ' . var_export($paymentInfo[0]->gateway, true), $configs);
        }

        return $valid_data;
    } else {
        return $valid_data;
    }
}

add_action('give_checkout_error_checks', 'lkn_give_antispam_validate_donation', 10, 2);

/**
 * Implementing Google's ReCaptcha on All Give Forms V3
 *
 *  To effectively use this snippet, please do the following:
 *  1. Get your Google ReCAPTCHA API Key here: https://www.google.com/recaptcha/admin/create
 *  2. In each function and action, replace "_myprefix_" with your own custom prefix
 *  3. Put your "Secret Key" where it says "MYSECRETKEY" in the $recaptcha_secret_key string
 *  4. Put your "Site Key" where it says "MYSITEKEY" in TWO areas below
 */

/**
 * Validate ReCAPTCHA
 *
 * @param $valid_data
 * @param $data
 *
 * @return array $valid_data
 */
function lkn_give_antispam_validate_recaptcha($valid_data, $data) {
    $configs = lkn_give_antispam_get_configs();
    // Verifica se plugin está habilitado e garante que só executa 1 vez
    if ($configs['antispamEnabled'] === 'enabled' && !isset($data['give_ajax'])) {
        // Verifica se opção do recaptcha está habilitada
        if ($configs['recEnabled'] === 'enabled') {
            $recaptcha_url        = 'https://www.google.com/recaptcha/api/siteverify';
            $recaptcha_secret_key = $configs['secretRec']; // Replace with your own key here.
            // Requisição de verificação do recaptcha
            $recaptcha_response   = wp_remote_post($recaptcha_url . '?secret=' . $recaptcha_secret_key . '&response=' . $data['g-recaptcha-response'] . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
            // Formata a resposta recebida em um objeto
            $recaptcha_data       = json_decode(wp_remote_retrieve_body($recaptcha_response));

            // Ativar logs de depuração
            if ($configs['debug'] === true) {
                lkn_give_antispam_reg_log('(recaptcha response data): ' . var_export($recaptcha_data, true) . PHP_EOL . ' ||| (recaptcha data response): ' . var_export($data, true) . PHP_EOL . ' ||| (data action): ' . var_export($data['give_ajax'], true), $configs);
            }

            // Verifica se a requisição foi concluída com sucesso
            if (!isset($recaptcha_data->success) || $recaptcha_data->success == false) {
                // User must have validated the reCAPTCHA to proceed with donation.
                if (!isset($data['g-recaptcha-response']) || empty($data['g-recaptcha-response'])) {
                    give_set_error('g-recaptcha-response', __('O e-mail que você está usando foi sinalizado como sendo usado em comentários de SPAM ou doações por nosso sistema. Entre em contato com o administrador do site para mais informações.', 'give'));
                }
            } elseif (!isset($recaptcha_data->score) || $recaptcha_data->score < $configs['scoreRec']) {
                // Caso o score seja menor que o valor definido mostra mensagem de erro
                give_set_error('g-recaptcha-response', __('O e-mail que você está usando foi sinalizado como sendo usado em comentários de SPAM ou doações por nosso sistema. Entre em contato com o administrador do site para mais informações.', 'give'));
            }
        }
    }

    return $valid_data;
}

add_action('give_checkout_error_checks', 'lkn_give_antispam_validate_recaptcha', 9, 2);

/**
 * Enqueue ReCAPTCHA Scripts
 */
function lkn_give_antispam_recaptcha_scripts() {
    $configs = lkn_give_antispam_get_configs();
    if ($configs['antispamEnabled'] === 'enabled') {
        if ($configs['recEnabled'] === 'enabled') {
            $siteKey = $configs['siteRec'];
            wp_register_script('give-captcha-js', 'https://www.google.com/recaptcha/api.js?render=' . $siteKey);
            // If you only want to enqueue on single form pages then uncomment if statement
            if (is_singular('give_forms')) {
                wp_enqueue_script('give-captcha-js');
            }
        }
    }
}

add_action('wp_enqueue_scripts', 'lkn_give_antispam_recaptcha_scripts');

/**
 * Print Necessary Inline JS for ReCAPTCHA
 *
 * This function outputs the appropriate inline js ReCAPTCHA scripts in the footer
 */
function lkn_give_antispam_print_my_inline_script() {
    $configs = lkn_give_antispam_get_configs();
    if ($configs['antispamEnabled'] === 'enabled') {
        if ($configs['recEnabled'] === 'enabled') {
            $siteKey = $configs['siteRec'];
            // Uncomment if statement to control output
            // Só executa 1 vez por formulário
            if (is_singular('give_forms')) {
                $html = <<<HTML
			<script type="text/javascript">
				// Faz a renderização do footer do recaptcha
					jQuery( document ).on( 'give_gateway_loaded', function() {
						grecaptcha.render( 'give-recaptcha-element', {
							'sitekey': '$siteKey' // Add your own Google API sitekey here.
						} );
					} );
			</script>
HTML;
                echo $html;
            }
        }
    }
}

add_action('wp_footer', 'lkn_give_antispam_print_my_inline_script');

/**
 * Custom ReCAPTCHA Form Field
 *
 * This function adds the reCAPTCHA field above the "Donation Total" field.
 *
 * Don't forget to update the sitekey!
 *
 * @param $form_id
 */

function lkn_give_antispam_custom_form_fields($form_id) {
    $configs = lkn_give_antispam_get_configs();
    if ($configs['antispamEnabled'] === 'enabled') {
        if ($configs['recEnabled'] === 'enabled') {
            $siteKey = $configs['siteRec'];
            // Add you own google API Site key.
            $html = <<<HTML

			<input type="hidden" id="g-recaptcha-lkn-input" name="g-recaptcha-response" />

			<script type="text/javascript">
				// Verifica se DOM carregou completamente
				window.addEventListener('DOMContentLoaded', function() {
					
					let iframeLoader = parent.document.getElementsByClassName('iframe-loader')[0];
					let totalWrapper = document.getElementsByClassName('give-total-wrap')[0];
					let gNoticeWrapper = document.createElement('div');
					// Alguns temas e páginas do wordpress escondem o badge do recaptcha
					// Adicionado notice contendo políticas de privacidade e termos de uso como requerido pela documentação do recaptcha
					// @see { https://developers.google.com/recaptcha/docs/faq#id-like-to-hide-the-recaptcha-badge.-what-is-allowed }
					gNoticeWrapper.innerHTML = 'Este site é protegido pelo reCAPTCHA e as <a href="https://policies.google.com/privacy" target="_blank">Políticas de Privacidade</a> e <a href="https://policies.google.com/terms" target="_blank">Termos de Serviço</a> do Google se aplicam.';
					gNoticeWrapper.setAttribute('class','gNotice');

					totalWrapper.append(gNoticeWrapper);

					// caso for um formulário legado altera também os atributos do formulário para validação do giveWP
					if(!iframeLoader) { // verifica a existência do iframe loader que é específico do formulário multi-step
						let givePaymentSelect = document.getElementById('give-payment-mode-wrap');
						if(givePaymentSelect) {
							lknPrepareRecaptcha();
						} else {
							let paymentDiv = document.getElementById('give_purchase_form_wrap');
							paymentDiv.addEventListener('click', function () {
								grecaptcha.ready(function() {
									grecaptcha.execute('$siteKey', {action: 'submit'}).then(function(token) {
										// Add your logic to submit to your backend server here.
										document.getElementById('g-recaptcha-lkn-input').value = token;
									});
								});
							}, { once: true });
						}
					} else { // Formulário não tem iframe
						let userInfo = document.getElementById('give_checkout_user_info');
						userInfo.addEventListener('click', function () {
							grecaptcha.ready(function() {
								grecaptcha.execute('$siteKey', {action: 'submit'}).then(function(token) {
									// Add your logic to submit to your backend server here.
									document.getElementById('g-recaptcha-lkn-input').value = token;
								});
							});
						}, { once: true });
					}			
				});

				/**
				 * Detect HTML DOM object and add event listener on click to execute Recaptcha V3
				 * 
				 * @return Boolean
				 *  */
				function lknPrepareRecaptcha() {
					let paymentDiv = document.getElementById('give_purchase_form_wrap');
					paymentDiv.addEventListener('click', function () {
						grecaptcha.ready(function() {
							grecaptcha.execute('$siteKey', {action: 'submit'}).then(function(token) {
								// Add your logic to submit to your backend server here.
								document.getElementById('g-recaptcha-lkn-input').value = token;
							});
						});
					}, { once: true });
				}
			</script>

			<script id="give-recaptcha-element" class="g-recaptcha" src="https://www.google.com/recaptcha/api.js?render=$siteKey"></script>
			<style>
				.give-total-wrap {
					flex-direction: column;
				}
				.gNotice {
					margin: 15px 20px;
					font-size: 15px;
				}
			</style>
HTML;
            echo $html;
        }
    }
}

add_action('give_after_donation_levels', 'lkn_give_antispam_custom_form_fields', 10, 1);

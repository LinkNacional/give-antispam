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

	// Internal debug option
	$configs['debug'] = true;

	$configs['antispamEnabled'] = lkn_give_antispam_get_enabled();
	$configs['interval'] = lkn_give_antispam_get_time_interval();
	$configs['donationLimit'] = lkn_give_antispam_get_donation_limit();
	$configs['gatewayVerify'] = lkn_give_antispam_get_gateway_verification();

	return $configs;
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

	return $timeInterval;
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
								give_set_error('spam_donation', 'O e-mail que você está usando foi sinalizado como sendo usado em comentários de SPAM ou doações por nosso sistema. Tente usar um endereço de e-mail diferente ou entre em contato com o administrador do site se tiver alguma dúvida.');
							}
						}
					} else {
						// Verify if the user has made another donation in the time interval
						if ($donationLimit > $donationCounter) {
							$donationCounter++;
						} else {
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

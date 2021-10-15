<?php
/**
 * Give - Antispam Settings Page/Tab
 *
 * @package    Give_Antispam
 * @subpackage Give_Antispam/includes/admin
 * @author     GiveWP <https://givewp.com>
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Add setting to exiting section and tab
 * If you want to add setting to existing tab and existing section then find a required filter for setting and add your logic.
 * With current code we are adding a setting field to "General" section of "General" tab
 *
 * @param $settings
 *
 * @return array
 */
function give_lkn_antispam_add_setting_into_existing_tab($settings) {
	if (!Give_Admin_Settings::is_setting_page('general', 'access-control')) {
		return $settings;
	}

	// Make sure you will create your own section or add new setting before array with type 'sectionend' otherwise setting field with not align properly with other setting fields.
	$newSetting = [];
	foreach ($settings as $key => $setting) {
		if ('give_docs_link' === $setting['type']) { // You can use id to compare or create own sub section to add new setting.
			$newSetting[] = [
				'name' => __('Habilitar proteção de doações spam', 'give'),
				'id' => 'lkn_antispam_enabled_setting_field',
				'desc' => __('Ative ou desative o plugin Antispam esse plugin fará o bloqueio de doações suspeitas'),
				'type' => 'radio',
				'default' => 'disabled',
				'options' => [
					'enabled' => __('Habilitado', 'give'),
					'disabled' => __('Desabilitado', 'give'),
				],
			];

			// Options only apears if the plugin option is 'enabled'
			if (give_get_option('lkn_antispam_enabled_setting_field') === 'enabled') {
				$newSetting[] = [
					'name' => __('Limite de doações no intervalo de tempo', 'give'),
					'id' => 'lkn_antispam_limit_setting_field',
					'desc' => __('Quantidade de doações que um cliente pode fazer em determinado período de tempo'),
					'type' => 'number',
					'default' => '2',
				];

				$newSetting[] = [
					'name' => __('Intervalo entre doações', 'give'),
					'id' => 'lkn_antispam_time_interval_setting_field',
					'desc' => __('Intervalo de tempo entre doações que cliente pode fazer (em minutos)'),
					'type' => 'number',
					'default' => '10',
				];

				$newSetting[] = [
					'name' => __('Limitar doações para mesma forma de pagamento', 'give'),
					'id' => 'lkn_antispam_same_gateway_setting_field',
					'desc' => __('Ative para limitar doações em sequência que tenham os mesmos meios de pagamento'),
					'type' => 'radio',
					'default' => 'disabled',
					'options' => [
						'enabled' => __('Habilitado', 'give'),
						'disabled' => __('Desabilitado', 'give'),
					],
				];
			}

			$newSetting[] = [
				'id' => 'lkn_antispam',
				'type' => 'sectionend',
			];
		}

		$newSetting[] = $setting;
	}

	return $newSetting;
}

add_filter('give_get_settings_general', 'give_lkn_antispam_add_setting_into_existing_tab');

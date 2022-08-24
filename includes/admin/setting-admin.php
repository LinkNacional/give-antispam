<?php
/**
 * Give - Antispam Settings Page/Tab
 *
 * @since      1.0.0
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
    $spamLogUrl = __DIR__ . '/../../logs/ip-spam.log';

    $logContent = file_exists($spamLogUrl) ? file_get_contents($spamLogUrl) : false;

    if ($logContent !== false) {
        $logContent = json_encode($logContent);
    } else {
        $logContent = json_encode(['msg' => 'Nenhum spam bloqueado']);
    }

    $html = <<<HTML
		<script>
			// Open new tab and register logs
			function openWindowContent () {
				var newWindow = window.open('','_blank');
				newWindow.document.write($logContent);
			}
			// On page load run the creation element script
			document.addEventListener('DOMContentLoaded', function () {
				// Get the elements from the page
				let formTable = document.getElementsByClassName('form-table')[0];
				let urlLogElement = document.getElementById('lkn_log_new_tab');

				// Add the click event on the <a></a> element
				urlLogElement.addEventListener('click', openWindowContent);
			})
		</script>

		<style>
			#lkn_log_new_tab {
				cursor: pointer;
			}
		</style>
HTML;
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
                'desc' => __('Ative ou desative o plugin Antispam esse plugin fará o bloqueio de doações suspeitas.'),
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
                    'name' => __('Habilitar debug', 'give'),
                    'id' => 'lkn_antispam_debug_setting_field',
                    'desc' => __('Habilitar registro de logs.'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => [
                        'enabled' => __('Habilitado', 'give'),
                        'disabled' => __('Desabilitado', 'give'),
                    ],
                ];

                $newSetting[] = [
                    'name' => __('Ips banidos', 'give'),
                    'id' => 'lkn_antispam_banned_ips_setting_field',
                    'desc' => __('Separar os IPs pulando uma linha com a tecla Enter.'),
                    'type' => 'textarea',
                ];

                $newSetting[] = [
                    'name' => __('Limite de doações no intervalo de tempo', 'give'),
                    'id' => 'lkn_antispam_limit_setting_field',
                    'desc' => __('Quantidade de doações que um cliente pode fazer em determinado período de tempo.'),
                    'type' => 'number',
                    'default' => '2',
                ];

                $newSetting[] = [
                    'name' => __('Intervalo entre doações', 'give'),
                    'id' => 'lkn_antispam_time_interval_setting_field',
                    'desc' => __('Intervalo de tempo entre doações que cliente pode fazer (em minutos).'),
                    'type' => 'number',
                    'default' => '10',
                ];

                $newSetting[] = [
                    'name' => __('Limitar doações para mesma forma de pagamento', 'give'),
                    'id' => 'lkn_antispam_same_gateway_setting_field',
                    'desc' => __('Ative para limitar doações em sequência que tenham os mesmos meios de pagamento.'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => [
                        'enabled' => __('Habilitado', 'give'),
                        'disabled' => __('Desabilitado', 'give'),
                    ],
                ];

                $newSetting[] = [
                    'name' => __('Salvar relatório anti-spam', 'give'),
                    'id' => 'lkn_antispam_save_log_setting_field',
                    'desc' => __('Ative para salvar um relatório contendo as doações de spam bloqueadas. <a id="lkn_log_new_tab">Relatório de spam bloqueado.</a>'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => [
                        'enabled' => __('Habilitado', 'give'),
                        'disabled' => __('Desabilitado', 'give'),
                    ],
                ];

                $newSetting[] = [
                    'name' => __('Recaptcha formulário de doação', 'give'),
                    'id' => 'lkn_antispam_active_recaptcha_setting_field',
                    'desc' => __('Ative para habilitar o Recaptcha nos formulários de doação. <a href="https://www.google.com/recaptcha/admin/" target="_blank">Gere as chaves do Recaptcha V3 aqui.</a>'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => [
                        'enabled' => __('Habilitado', 'give'),
                        'disabled' => __('Desabilitado', 'give'),
                    ],
                ];
                if (give_get_option('lkn_antispam_active_recaptcha_setting_field') === 'enabled') {
                    $newSetting[] = [
                        'name' => __('Recaptcha site key', 'give'),
                        'id' => 'lkn_antispam_site_rec_id_setting_field',
                        'desc' => __('Chave do serviço Google Recaptcha V3.'),
                        'type' => 'api_key',
                    ];

                    $newSetting[] = [
                        'name' => __('Recaptcha secret key', 'give'),
                        'id' => 'lkn_antispam_secret_rec_id_setting_field',
                        'desc' => __('Chave do serviço Google Recaptcha V3.'),
                        'type' => 'api_key',
                    ];
                    $newSetting[] = [
                        'name' => __('Score mínimo', 'give'),
                        'id' => 'lkn_antispam_score_re_setting_field',
                        'desc' => __('O score mínimo validado pelo Recaptcha para que as doações sejam aceitas. Varia entre 0 e 10.'),
                        'type' => 'number',
                        'default' => '5',
                    ];
                }

                // Options only apears if the plugin option is 'enabled'
                if (give_get_option('lkn_antispam_save_log_setting_field') === 'enabled') {
                    echo $html;
                }
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

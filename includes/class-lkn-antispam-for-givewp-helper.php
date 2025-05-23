<?php

/**
 * @since      1.0.0
 */
if ( ! defined('WPINC')) {
    exit;
}

abstract class Lkn_Antispam_Helper {
    /**
     * Show plugin dependency notice.
     *
     * @since
     */
    final public static function verify_plugin_dependencies(): void {
        // Load plugin helper functions.
        if ( ! function_exists('deactivate_plugins') || ! function_exists('is_plugin_active')) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }

        // Flag to check whether deactivate plugin or not.
        $is_deactivate_plugin = null;

        $lkn_antispam_path = ABSPATH . '/wp-content/plugins/give-antispam/lkn-antispam-for-givewp.php';

        $is_installed = false;

        // Check if the Give plugin is installed and activated.
        if (function_exists('get_plugins')) {
            $all_plugins = get_plugins();
            $is_installed = ! empty($all_plugins['give/give.php']);

            $all_activateds = get_option('active_plugins');
            $activeted_plugin = in_array('give/give.php', $all_activateds, true);
        }

        // Check the minimum version of Give and if it is enabled.
        if ($is_installed) {
            require_once ABSPATH . '/wp-content/plugins/give/give.php';

            if ($activeted_plugin && version_compare(GIVE_VERSION, LKN_ANTISPAM_FOR_GIVEWP_MIN_GIVE_VERSION, '<')) {
                $is_deactivate_plugin = true;
                Lkn_Antispam_Helper::dependency_alert();
            } elseif ($activeted_plugin && version_compare(GIVE_VERSION, LKN_ANTISPAM_FOR_GIVEWP_MIN_GIVE_VERSION, '>')) {
                $is_deactivate_plugin = false;
            } elseif ( ! $activeted_plugin) {
                $is_deactivate_plugin = true;
                Lkn_Antispam_Helper::inactive_alert();
            }
        } elseif ( ! $is_installed) {
            $is_deactivate_plugin = true;
            Lkn_Antispam_Helper::dependency_alert();
        }

        // Deactivate plugin.
        if ($is_deactivate_plugin) {
            deactivate_plugins($lkn_antispam_path);
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        }
    }

    final public static function dependency_notice(): void {
        // Admin notice.
        $message = sprintf(
            '<div class="notice notice-error"><p><strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a>  %5$s %6$s+ %7$s.</p></div>',
            __('Activation Error:', 'antispam-donation-for-givewp'),
            __('You must have', 'antispam-donation-for-givewp'),
            'https://givewp.com',
            __('Give', 'antispam-donation-for-givewp'),
            __('version', 'antispam-donation-for-givewp'),
            LKN_ANTISPAM_FOR_GIVEWP_MIN_GIVE_VERSION,
            __('for the Give Antispam to activate', 'antispam-donation-for-givewp')
        );

        echo wp_kses_post($message);
    }

    /**
     * Notice for No Core Activation.
     *
     * @since
     */
    final public static function inactive_notice(): void {
        // Admin notice.
        $message = sprintf(
            '<div class="notice notice-error"><p><strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a> %5$s.</p></div>',
            __('Activation Error:', 'antispam-donation-for-givewp'),
            __('You must have', 'antispam-donation-for-givewp'),
            'https://givewp.com',
            __('Give', 'antispam-donation-for-givewp'),
            __('plugin installed and activated for the Give Antispam', 'antispam-donation-for-givewp')
        );

        echo wp_kses_post($message);
    }

    /**
     * Plugin row meta links.
     *
     * @since
     *
     * @param array $plugin_meta an array of the plugin's metadata
     *
     * @return array
     */
    final public static function plugin_row_meta($plugin_meta) {
        $new_meta_links['setting'] = sprintf(
            '<a href="%1$s">%2$s</a>',
            admin_url('edit.php?post_type=give_forms&page=give-settings&tab=general&section=access-control'),
            __('Settings', 'antispam-donation-for-givewp')
        );

        return array_merge($plugin_meta, $new_meta_links);
    }

    /**
     * This function centralizes the data in one spot for ease mannagment.
     *
     * @return array
     */
    final public static function get_configs() {
        $configs = array();

        $configs['basePath'] = LKN_ANTISPAM_FOR_GIVEWP_DIR;
        $configs['base'] = $configs['basePath'] . 'logs/' . gmdate('d.m.Y-H.i.s') . '.log';
        $configs['baseReport'] = $configs['basePath'] . 'logs/ip-spam.log';

        // Internal debug option
        $configs['debug'] = give_get_option('lkn_antispam_debug_setting_field');

        $configs['antispamEnabled'] = give_get_option('lkn_antispam_enabled_setting_field');
        $configs['interval'] = Lkn_Antispam_Actions::get_time_interval();
        $configs['donationLimit'] = give_get_option('lkn_antispam_limit_setting_field');
        $configs['gatewayVerify'] = give_get_option('lkn_antispam_same_gateway_setting_field');
        $configs['blockDonation'] = give_get_option('lkn_antispam_blocking_donation_amount_setting_field');
        // Recaptcha keys
        $configs['recEnabled'] = give_get_option('lkn_antispam_active_recaptcha_setting_field');
        $configs['siteRec'] = give_get_option('lkn_antispam_site_rec_id_setting_field');
        $configs['secretRec'] = give_get_option('lkn_antispam_secret_rec_id_setting_field');
        $configs['scoreRec'] = Lkn_Antispam_Actions::get_recaptcha_score();
        $configs['bannedIps'] = give_get_option('lkn_antispam_banned_ips_setting_field');

        return $configs;
    }

    /**
     * Delete the log files older than 5 days.
     */
    final public static function delete_old_logs(): void {
        $configs = Lkn_Antispam_Helper::get_configs();
        $logsPath = $configs['basePath'] . 'logs/';

        foreach (scandir($logsPath) as $logFilename) {
            if ('.' !== $logFilename && '..' !== $logFilename && 'index.php' !== $logFilename && 'ip-spam.log' !== $logFilename) {
                $logDate = explode('-', $logFilename)[0];
                $logDate = explode('.', $logDate);

                $logDay = $logDate[0];
                $logMonth = $logDate[1];
                $logYear = $logDate[2];

                $logDate = $logYear . '-' . $logMonth . '-' . $logDay;

                $logDate = new DateTime($logDate);
                $now = new DateTime(gmdate('Y-m-d'));

                $interval = $logDate->diff($now);
                $logAge = $interval->format('%a');

                if ($logAge >= 5) {
                    wp_delete_file($logsPath . '/' . $logFilename);
                }
            }
        }
    }

    final public static function dependency_alert(): void {
        add_action('admin_notices', array('Lkn_Antispam_Helper', 'dependency_notice'));
    }

    final public static function inactive_alert(): void {
        add_action('admin_notices', array('Lkn_Antispam_Helper', 'inactive_notice'));
    }

    final public static function block_all_payments($gateway_list) {
        $option = give_get_option('lkn_antispam_spam_detected_block_all');
        if (true == $option) {
            return array();
        }

        return $gateway_list;
    }

    final public static function remove_status_block_all_payments(): void {
        give_update_option('lkn_antispam_spam_detected_block_all', false);
    }

    final public static function create_custom_page(): int {
        // Título do template
        $template_title = 'Meu Template';

        // Define os argumentos da consulta WP_Query para verificar se o template já existe
        $args = array(
            'post_type' => 'page',
            'post_status' => 'any', // Verificar em todos os status de postagem
            'posts_per_page' => 1,
            'title' => $template_title,
        );

        // Executa a consulta WP_Query
        $query = new WP_Query($args);

        // Verifica se a consulta retornou alguma página
        if ($query->have_posts()) {
            // Se o template já existe, retorna o ID
            $template_id = $query->posts[0]->ID;
        } else {
            // Se o template não existir, cria um novo
            $new_template_args = array(
                'post_type' => 'page',
                'post_title' => $template_title,
                'post_status' => 'publish',
            );

            // Insere a postagem no banco de dados
            $template_id = wp_insert_post($new_template_args);
        }

        // Restaura as informações da consulta original

        // Retorna o ID do template
        return $template_id;
    }

    final public static function add_php_custom_page($content) {
        // Verifica se é a página desejada (substitua 'page-slug' pelo slug da página)
        if (is_page('Meu Template')) {
            // Adiciona o código PHP ao conteúdo da página
            ob_start(); // Inicia o buffer de saída

            include_once LKN_ANTISPAM_FOR_GIVEWP_DIR . 'public/templates/lkn-antispam-custom-page.php';
            $php_output = ob_get_clean(); // Captura a saída do buffer e limpa o buffer

            // Adiciona o código PHP ao conteúdo da página
            $content = $php_output;
        }

        return $content;
    }
}

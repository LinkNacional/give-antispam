<?php

/**
 * Uninstall Give_Antispam
 *
 * @package     Give
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Pega um array contendo todas as configurações
$lkn_array_antispam_options = give_get_settings();
// Procura pelo array pelas chaves correspondentes as configurações do plugin
// E salva o nome delas
$lkn_array_antispam_options = array_filter($lkn_array_antispam_options, function ($key) {
    return strpos($key, 'lkn_antispam_') === 0;
}, ARRAY_FILTER_USE_KEY);
$lkn_array_antispam_options = array_keys($lkn_array_antispam_options);

// Verifica se as chaves existem
if (count($lkn_array_antispam_options) > 0) {
    // Caso existam varre o array selecionando cada opção
    for ($c = 0; $c < count($lkn_array_antispam_options); $c++) {
        // utiliza o valor, que é o nome da chave, para deletar a opção do give
        give_delete_option($lkn_array_antispam_options[$c]);
    }
}

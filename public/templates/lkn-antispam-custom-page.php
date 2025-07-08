<?php

$unlock = isset($_GET['unlock']) && true == $_GET['unlock'] ? true : false;
$server_name = isset($_SERVER['SERVER_NAME']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '';
if ($unlock) {
    $timestamp = wp_next_scheduled('lkn_antispam_spam_detected_block_all_event');
    give_update_option('lkn_antispam_spam_detected_block_all', false);

    wp_unschedule_event($timestamp, 'lkn_antispam_spam_detected_block_all_event');
    $url = 'https://' . $server_name . '/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=general&section=lkn_antispam';

    wp_redirect($url);
}

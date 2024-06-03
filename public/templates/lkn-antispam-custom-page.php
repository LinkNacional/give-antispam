<?php

$unlock = isset($_GET['unlock']) && true == $_GET['unlock'] ? true : false;

if ($unlock) {
    $timestamp = wp_next_scheduled('lkn__antispam_spam_detected_block_all_event');
    give_update_option('lkn_antispam_spam_detected_block_all', false);

    wp_unschedule_event($timestamp, 'lkn__antispam_spam_detected_block_all_event');
    $url = 'https://' . $_SERVER['SERVER_NAME'] . '/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=general&section=access-control';
    wp_redirect($url);
}

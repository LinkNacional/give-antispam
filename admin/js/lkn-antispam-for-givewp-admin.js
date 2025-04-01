(function ($) {
  'use strict'

  // Check if the DOM has fully loaded.
  $(window).load(function () {

    let url = new URL(window.location.href);
    url.searchParams.set('page', 'give-tools');
    url.searchParams.set('tab', 'logs');
    
    $('#lkn_log_new_tab').attr('href', url.toString());
    $('#lkn_log_new_tab').attr('target', '_blank');
  })
// eslint-disable-next-line no-undef
})(jQuery)

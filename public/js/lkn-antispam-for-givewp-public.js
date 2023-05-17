(function ($) {
  'use strict'

  // Load sitekey from plugin configs
  const SITEKEY = window.skData.sitekey

  // Check if the DOM has fully loaded.
  $(window).load(function () {
    const iframeLoader = $('.iframe-loader').parent().get(0)
    const totalWrapper = $('.give-total-wrap')
    const gNoticeWrapper = $('#g-notice-wrapper')

    // Some Wordpress themes and pages hide the Recaptcha badge.
    // Add a notice containing privacy policy and terms of use as required by the Recaptcha documentation.
    // @see { https://developers.google.com/recaptcha/docs/faq#id-like-to-hide-the-recaptcha-badge.-what-is-allowed }
    if (totalWrapper) {
      totalWrapper.append(gNoticeWrapper)
    }

    // If it is a legacy form, also modify the form attributes for GiveWP validation.
    // Verify for the existence of the iframe loader, specific to the multi-step form.
    if (!iframeLoader) {
      lknPrepareRecaptcha('#give_checkout_user_info')
      console.log('Teste 3')

    // The form have iframe.
    // TODO em form multi-step tá sendo chamado antes do usuário chegar na página que tem o give_purchase_form_wrap
    } else {
      $('#give_purchase_form_wrap').ready(function () {
        lknPrepareRecaptcha('#give_purchase_form_wrap')
        console.log('Teste 2')
      })
    }
  })

  $(document).on('give_gateway_loaded', function () {
    // Render the reCAPTCHA footer
    // eslint-disable-next-line no-undef
    grecaptcha.render('give-recaptcha-element-js', {
      sitekey: SITEKEY,
      theme: 'light'
    })
  })

  /**
  * Detect HTML DOM object and add event listener on click to execute Recaptcha V3
  *
  * @return Boolean
  **/
  function lknPrepareRecaptcha (element) {
    $(element).one('click', function () {
      // eslint-disable-next-line no-undef
      grecaptcha.ready(function () {
        // eslint-disable-next-line no-undef
        grecaptcha.execute(SITEKEY, { action: 'submit' }).then(function (token) {
          // Add your logic to submit to your backend server here.
          $('#g-recaptcha-lkn-input').value = token
        })
      })
      console.log('Run')
    })
  }

// eslint-disable-next-line no-undef
})(jQuery)

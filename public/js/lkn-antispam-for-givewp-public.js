(function ($) {
  'use strict'

  // Load sitekey from plugin configs
  const SITEKEY = window.skData.sitekey

  // Check if the DOM has fully loaded.
  $(window).load(function () {
    const iframeLoader = getParentElementClassNameJquery('iframe-loader')
    const totalWrapper = getElementClassNameJquery('give-total-wrap')
    const gNoticeWrapper = getElementIdJquery('g-notice-wrapper')

    // Some Wordpress themes and pages hide the Recaptcha badge.
    // Add a notice containing privacy policy and terms of use as required by the Recaptcha documentation.
    // @see { https://developers.google.com/recaptcha/docs/faq#id-like-to-hide-the-recaptcha-badge.-what-is-allowed }
    if (totalWrapper) {
      totalWrapper.append(gNoticeWrapper)
    }

    // If it is a legacy form, also modify the form attributes for GiveWP validation.
    if (!iframeLoader) { // Verify for the existence of the iframe loader, specific to the multi-step form.
      const givePaymentSelect = getElementIdJquery('give-payment-mode-wrap')

      if (givePaymentSelect) {
        lknPrepareRecaptcha()
      } else {
        // TODO Transformar esse trecho reCAPTCHA em função reutilizável.
        if (clickJquery('give_purchase_form_wrap')) {
          window.grecaptcha.ready(function () {
            window.grecaptcha.execute('{$siteKey}', { action: 'submit' }).then(function (token) {
            // Add your logic to submit to your backend server here.
              getElementIdJquery('g-recaptcha-lkn-input').value = token
            })
          })
        }
      }
    } else { // The form doesn't have iframe.
      const userInfo = document.getElementById('give_checkout_user_info')
      userInfo.addEventListener('click', function () {
        window.grecaptcha.ready(function () {
          window.grecaptcha.execute('{$siteKey}', { action: 'submit' }).then(function (token) {
          // Add your logic to submit to your backend server here.
            document.getElementById('g-recaptcha-lkn-input').value = token
          })
        })
      }, { once: true })
    }
  })

  $(document).on('give_gateway_loaded', function () {
    // Render the reCAPTCHA footer
    window.grecaptcha.render('give-recaptcha-element', {
      sitekey: SITEKEY,
      theme: 'dark'
    })
  })

  /**
  * Detect HTML DOM object and add event listener on click to execute Recaptcha V3
  *
  * @return Boolean
  **/
  function lknPrepareRecaptcha () {
    if (clickJquery('give_purchase_form_wrap')) {
      window.grecaptcha.ready(function () {
        window.grecaptcha.execute('{$siteKey}', { action: 'submit' }).then(function (token) {
          // Add your logic to submit to your backend server here.
          getElementIdJquery('g-recaptcha-lkn-input').value = token
        })
      })
    }
  }

  /**
  * Search an HTML element by id using jquery.
  *
  * @return Object
  **/
  function getElementIdJquery (id) {
    const element = $('#' + id).get(0)

    if (element.lenght <= 0) {
      console.log('Element not found with ID: ' + id)
    }

    return element
  }

  /**
  * Search an HTML element by class name using jquery.
  *
  * @return Object
  **/
  function getElementClassNameJquery (className) {
    const element = $('.' + className).get(0)

    if (element.lenght <= 0) {
      console.log('Element not found with Class: ' + className)
    }

    return element
  }

  /**
  * Search an HTML Parent element by class name using jquery.
  *
  * @return Object
  **/
  function getParentElementClassNameJquery (className) {
    const element = $('.' + className).parent()

    if (element.lenght <= 0) {
      console.log('Parent element not found with Class: ' + className)
    }

    return element
  }

  /**
  * Detect click on HTML Object, and return true.
  *
  * @return Boolean
  **/
  function clickJquery (id) {
    $(document).ready(function () {
      $('#' + id).click(function () {
        return true
      })
    })
  }

// eslint-disable-next-line no-undef
})(jQuery)

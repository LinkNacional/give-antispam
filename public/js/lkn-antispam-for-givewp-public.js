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
      lknPrepareRecaptcha('#give_checkout_user_info', '#g-recaptcha-lkn-input')

    // The form have iframe.
    } else {
      const iframeBody = iframeLoader.firstChild.contentDocument.childNodes[1].childNodes[1]
      let arrayAux = []

      filterElementsByClass('give-form-wrap give-embed-form give-viewing-form-in-iframe', iframeBody, arrayAux)
      const iframeForm = arrayAux[0]
      arrayAux = []

      filterElementsByType('FORM', iframeForm, arrayAux)
      const iframeFormDiv = arrayAux[0]
      arrayAux = []

      filterElementsByClass('give-form-section give-donation-amount-section', iframeFormDiv, arrayAux)
      const iframeRecaptchaSection = arrayAux[0]
      arrayAux = []

      // For classic template
      if (iframeRecaptchaSection !== undefined) {
        filterElementsById('g-recaptcha-lkn-input', iframeRecaptchaSection, arrayAux)
        const iframeRecaptchaInput = arrayAux[0]
        arrayAux = []

        lknPrepareRecaptcha(iframeForm, iframeRecaptchaInput)

        // For multi step template
      } else {
        filterElementsByClass('give-section choose-amount', iframeFormDiv, arrayAux)
        const iframeRecaptchaSection = arrayAux[0]
        arrayAux = []

        filterElementsById('g-recaptcha-lkn-input', iframeRecaptchaSection, arrayAux)
        const iframeRecaptchaInput = arrayAux[0]
        arrayAux = []

        lknPrepareRecaptcha(iframeForm, iframeRecaptchaInput)
      }
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
  function lknPrepareRecaptcha (element, recInput) {
    $(element).one('click', function () {
      $(recInput).val('10')
      // eslint-disable-next-line no-undef
      grecaptcha.ready(function () {
        // eslint-disable-next-line no-undef
        grecaptcha.execute(SITEKEY, { action: 'submit' }).then(function (token) {
          // Add your logic to submit to your backend server here.
          $('#g-recaptcha-lkn-input').value = token
        })
      })
    })
  }

  // Functions for search HTML elements in iframe.
  /**
  * Filter elements by ID in an HTML element.
  *
  * @return
  **/
  function filterElementsById (elementId, parentElement, elementsArray) {
    for (let i = 0; i < parentElement.childNodes.length; i++) {
      const node = parentElement.childNodes[i]
      if (node.nodeType === 1 && node.id === elementId) {
        elementsArray.push(node)
      }
    }
  }

  /**
  * Filter elements by Class in an HTML element.
  *
  * @return
  **/
  function filterElementsByClass (elementClass, parentElement, elementsArray) {
    for (let i = 0; i < parentElement.childNodes.length; i++) {
      const node = parentElement.childNodes[i]
      if (node.classList && node.classList.value === elementClass) {
        elementsArray.push(node)
      }
    }
  }

  /**
  * Filter elements by type in an HTML element.
  *
  * @return
  **/
  function filterElementsByType (elementType, parentElement, elementsArray) {
    for (let i = 0; i < parentElement.childNodes.length; i++) {
      const node = parentElement.childNodes[i]
      if (node.nodeType === 1 && node.nodeName === elementType) {
        elementsArray.push(node)
      }
    }
  }

// eslint-disable-next-line no-undef
})(jQuery)

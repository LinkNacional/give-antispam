console.log('Teste')
console.log(window.skData.sitekey)
// Load sitekey from plugin configs
const siteKey = window.skData.sitekey

// Render the reCAPTCHA footer
window.grecaptcha.render('give-recaptcha-element', {
  sitekey: siteKey,
  theme: 'light'
})

// Check if the DOM has fully loaded.
window.addEventListener('DOMContentLoaded', function () {
  const iframeLoader = parent.document.getElementsByClassName('iframe-loader')[0]
  const totalWrapper = document.getElementsByClassName('give-total-wrap')[0]
  const gNoticeWrapper = document.getElementById('g-notice-wrapper')

  // Some Wordpress themes and pages hide the Recaptcha badge.
  // Add a notice containing privacy policy and terms of use as required by the Recaptcha documentation.
  // @see { https://developers.google.com/recaptcha/docs/faq#id-like-to-hide-the-recaptcha-badge.-what-is-allowed }
  if (totalWrapper) {
    totalWrapper.append(gNoticeWrapper)
  }

  // If it is a legacy form, also modify the form attributes for GiveWP validation.
  if (!iframeLoader) { // Verify for the existence of the iframe  loader specific to the multi-step form.
    const givePaymentSelect = document.getElementById('give-payment-mode-wrap')
    if (givePaymentSelect) {
      lknPrepareRecaptcha()
    } else {
      const paymentDiv = document.getElementById('give_purchase_form_wrap')
      paymentDiv.addEventListener('click', function () {
        window.grecaptcha.ready(function () {
          window.grecaptcha.execute('{$siteKey}', { action: 'submit' }).then(function (token) {
            // Add your logic to submit to your backend server here.
            document.getElementById('g-recaptcha-lkn-input').value = token
          })
        })
      }, { once: true })
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

/**
* Detect HTML DOM object and add event listener on click to execute Recaptcha V3
*
* @return Boolean
**/
function lknPrepareRecaptcha () {
  const paymentDiv = document.getElementById('give_purchase_form_wrap')
  paymentDiv.addEventListener('click', function () {
    window.grecaptcha.ready(function () {
      window.grecaptcha.execute('{$siteKey}', { action: 'submit' }).then(function (token) {
        // Add your logic to submit to your backend server here.
        document.getElementById('g-recaptcha-lkn-input').value = token
      })
    })
  }, { once: true })
}

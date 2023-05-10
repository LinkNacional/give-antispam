console.log('Teste')
console.log(skData.sitekey)
// Load sitekey from plugin configs
const siteKey = skData.sitekey

// Render the reCAPTCHA footer
window.grecaptcha.render('give-recaptcha-element', {
  sitekey: siteKey,
  theme: 'light'
})

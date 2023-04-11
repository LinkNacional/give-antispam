// const grecaptcha = document.createElement('grecaptcha')
// grecaptcha.src = 'https://www.google.com/recaptcha/api.js'
// grecaptcha.async = true
// grecaptcha.defer = true
// document.body.appendChild(grecaptcha)

// // Verifica se DOM carregou completamente
// window.addEventListener('DOMContentLoaded', function () {
//   const iframeLoader = parent.document.getElementsByClassName('iframe-loader')[0]
//   const totalWrapper = document.getElementsByClassName('give-total-wrap')[0]
//   const gNoticeWrapper = document.getElementById('g-notice-wrapper')

//   // Alguns temas e páginas do wordpress escondem o badge do recaptcha
//   // Adicionado notice contendo políticas de privacidade e termos de uso como requerido pela documentação do recaptcha
//   // @see { https://developers.google.com/recaptcha/docs/faq#id-like-to-hide-the-recaptcha-badge.-what-is-allowed }
//   if (totalWrapper) {
//     totalWrapper.append(gNoticeWrapper)
//   }

//   // caso for um formulário legado altera também os atributos do formulário para validação do giveWP
//   if (!iframeLoader) { // verifica a existência do iframe loader que é específico do formulário multi-step
//     const givePaymentSelect = document.getElementById('give-payment-mode-wrap')
//     if (givePaymentSelect) {
//       lknPrepareRecaptcha()
//     } else {
//       const paymentDiv = document.getElementById('give_purchase_form_wrap')
//       paymentDiv.addEventListener('click', function () {
//         grecaptcha.ready(function () {
//           grecaptcha.execute('{$siteKey}', { action: 'submit' }).then(function (token) {
//             // Add your logic to submit to your backend server here.
//             document.getElementById('g-recaptcha-lkn-input').value = token
//           })
//         })
//       }, { once: true })
//     }
//   } else { // Formulário não tem iframe
//     const userInfo = document.getElementById('give_checkout_user_info')
//     userInfo.addEventListener('click', function () {
//       grecaptcha.ready(function () {
//         grecaptcha.execute('{$siteKey}', { action: 'submit' }).then(function (token) {
//           // Add your logic to submit to your backend server here.
//           document.getElementById('g-recaptcha-lkn-input').value = token
//         })
//       })
//     }, { once: true })
//   }
// })

// // Detect HTML DOM object and add event listener on click to execute Recaptcha V3
// // @return Boolean
// function lknPrepareRecaptcha () {
//   const paymentDiv = document.getElementById('give_purchase_form_wrap')
//   paymentDiv.addEventListener('click', function () {
//     grecaptcha.ready(function () {
//       grecaptcha.execute('{$siteKey}', { action: 'submit' }).then(function (token) {
//         // Add your logic to submit to your backend server here.
//         document.getElementById('g-recaptcha-lkn-input').value = token
//       })
//     })
//   }, { once: true })
// }

(function ($) {
  'use strict';

  let changeRequestFunction = () => {
    let originalXHROpen = XMLHttpRequest.prototype.open;
    let originalXHRSend = XMLHttpRequest.prototype.send;
  
    XMLHttpRequest.prototype.open = function (method, url, async, user, password) {
      this._requestURL = url; // Armazena a URL da requisição
      originalXHROpen.apply(this, arguments);
    };
  
    XMLHttpRequest.prototype.send = function (body) {
      if (this._requestURL && this._requestURL.includes('wp-admin/admin-ajax.php')) {
        let xhr = this; // Armazena referência ao objeto XMLHttpRequest
  
        window.grecaptcha.ready(async () => {
          let tokenButton = await window.grecaptcha.execute(window.skData.sitekey, { action: 'submit' });
  
          // Adiciona o token reCAPTCHA ao corpo da requisição
          let newBody = new URLSearchParams(body);
          newBody.append('g-recaptcha-response', tokenButton);
          body = newBody.toString();
  
          let recaptchaInput = document.querySelector('#g-recaptcha-lkn-input');
          if (recaptchaInput) {
            recaptchaInput.value = tokenButton;
          }
  
          // Só chama o send() depois que o token foi adicionado
          originalXHRSend.call(xhr, body);
        });
      } else {
        originalXHRSend.apply(this, arguments);
      }
    };
  }

  changeRequestFunction()

  $(window).load(function () {
    let iframe = document.querySelector('[src*="giveDonationFormInIframe"]');
    if(iframe){
      iframe.contentWindow.grecaptcha = grecaptcha
      iframe.contentWindow.skData = skData
      iframe.contentWindow.eval(`(${changeRequestFunction.toString()})()`);
    }
  });

})(jQuery)
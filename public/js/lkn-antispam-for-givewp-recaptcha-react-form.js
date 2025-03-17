(function ($) {
    $(window).load(function () {
        iframe = document.querySelector('[src*="?givewp-route=donation-form-view"]');
        if (iframe) {
            iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            
            if (iframeDoc) {
                formDesc = iframeDoc.querySelector('.givewp-fields.givewp-fields-gateways');
                if(!formDesc){
                    formDesc = iframeDoc.querySelector('.givewp-donation-form__steps-body');
                }
                if(formDesc){
                    spanElement = document.createElement('span');
                    spanElement.innerHTML = skData.googleTermsText;
                    spanElement.style.display = "flex";
                    spanElement.style.flexDirection = "row";
                    spanElement.style.justifyContent = "center";
                    spanElement.style.marginTop = "20px";

                    pElement = spanElement.querySelector('p')
                    pElement.style.width = "70%";
                    pElement.style.textAlign = "center";

                    formDesc.appendChild(spanElement);
                }
            }

            iframe.contentWindow.grecaptcha = grecaptcha
            iframe.contentWindow.tokenButton = skData.sitekey
            iframe.contentWindow.eval(`
                window.grecaptcha.ready(() => {
                    let originalFetch = window.fetch;
                    window.fetch = async (input, init) => {
                        if (typeof input === 'string' && input.includes('?givewp-route=donate')) {
                            let tokenButton = await window.grecaptcha.execute(window.tokenButton, { action: 'submit' });
                            
                            if (init.body instanceof FormData) {
                                let newBody = new FormData();
                                
                                // Copia os dados existentes
                                for (let [key, value] of init.body.entries()) {
                                    newBody.append(key, value);
                                }

                                // Adiciona o novo token
                                newBody.append('g-recaptcha-response', tokenButton);

                                // Atualiza o body da requisição
                                init.body = newBody;
                            }
                            
                            console.log(init);
                            console.log(input);

                        }
                        return originalFetch(input, init);
                    };
                });
            `);
        }
    })
})(jQuery)


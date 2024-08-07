window.addEventListener('load', function () {
  const urlParams = new URLSearchParams(window.location.search)
  const page = urlParams.get('page')
  const tab = urlParams.get('tab')
  const section = urlParams.get('section')

  if (page === 'give-settings' && tab === 'general' && section === 'access-control') {
    // Setting max length option for pagseguro description field.
    const wcForm = document.querySelector('.give-docs-link')

    if (wcForm) {
      const noticeDiv = document.createElement('div')

      noticeDiv.innerHTML = `
          <div style='background-color: #fcf9e8;color: #646970;border: solid 1px #d3d3d3;border-left: 4px #dba617 solid;font-size: 16px;margin-top: 10px;'>
            <a  target="_blank" style="text-decoration:none; display: block;padding: 10px;">AntiSpam não esta compatível com formulários 3.0 do GiveWP.</a>
        </div>
            `
      wcForm.insertAdjacentElement('beforebegin', noticeDiv)
    } else {
      console.error('O formulário de configurações do gateway não foi encontrado.')
    }
  }
})

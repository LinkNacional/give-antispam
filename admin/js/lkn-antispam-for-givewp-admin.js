// Get the content text from .log
async function verifyLogs () {
  const logFilePath = '../wp-content/plugins/give-antispam/logs/ip-spam.log'
  const xhr = new XMLHttpRequest()

  const promise = new Promise((resolve, reject) => {
    xhr.onreadystatechange = function () {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
          let logData

          if (xhr.responseText !== '') {
            logData = xhr.responseText
          } else if (xhr.responseText === '') {
            logData = 'No IP blocked by spam.'
          }

          resolve(logData)
        } else {
          reject(new Error('Error loading the report of blocked IPs due to spam.'))
        }
      }
    }
  })

  xhr.open('GET', logFilePath, true)
  xhr.send()

  const logData = await promise

  // Open new tab and register logs
  function openWindowContent () {
    const newWindow = window.open('', '_blank')
    newWindow.document.write(logData)
  }
  // On page load run the creation element script
  document.addEventListener('DOMContentLoaded', function () {
  // Get the elements from the page
    const urlLogElement = document.getElementById('lkn_log_new_tab')

    // Add the click event on the <a></a> element
    urlLogElement.addEventListener('click', openWindowContent)
  })
}

verifyLogs()

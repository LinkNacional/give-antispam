(function ($) {
  'use strict'

  // Check if the DOM has fully loaded.
  $(window).load(function () {
    verifyLogs()

    /**
    * Get the content text from .log, and create a new page with the blocked IPs logs in .log.
    *
    * @return Boolean
    **/
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

      function openWindowContent () {
        // Open new tab
        const newWindow = window.open('', '_blank')
        // Rename new tab
        $(newWindow.document).find('html').html('<title>Report of blocked IPs</title>')
        // Register logs
        $(newWindow.document).find('body').html(logData)
      }

      $('#lkn_log_new_tab').on('click', function () {
        openWindowContent()
      })
    }
  })
  
// eslint-disable-next-line no-undef
})(jQuery)

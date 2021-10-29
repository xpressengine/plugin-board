(function ($) {
  if (BoardShare == undefined) {
    var BoardShare = {
      init: function () {
        $('body').on('click', '.xe-toggle-menu .share-item', function (event) {
          event.preventDefault()

          var $target = $(event.target),
            $anchor = $target.closest('a')
          if ($anchor.data('type') == 'copy') {
            BoardShare.copyToClipboard($anchor.data('url'))
            XE.toast('info', XE.Lang.trans('board::copyClipboard'))
          } else {
            window.open($anchor.data('url'))
          }
        })
      },
      copyToClipboard: function (text) {
        if (window.clipboardData && window.clipboardData.setData) {
          // IE specific code path to prevent textarea being shown while dialog is visible.
          return clipboardData.setData('Text', text)
        } else if (document.queryCommandSupported && document.queryCommandSupported('copy')) {
          var textarea = document.createElement('textarea')
          textarea.textContent = text
          textarea.style.position = 'fixed' // Prevent scrolling to bottom of page in MS Edge.
          document.body.appendChild(textarea)
          textarea.select()
          try {
            return document.execCommand('copy') // Security exception may be thrown by some browsers.
          } catch (ex) {
            console.warn('Copy to clipboard failed.', ex)
            return false
          } finally {
            document.body.removeChild(textarea)
          }
        }
      },
      searchToObject: function () {
        return search.substring(1).split('&').reduce(function (result, value) {
          var parts = value.split('=')
          if (parts[0]) result[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1])
          return result
        }, {})
      }
    }
  }

  $(function () {
    BoardShare.init()
  })
})(window.jQuery)

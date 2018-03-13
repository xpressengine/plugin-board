import moment from 'moment'
import window from 'window'
import $ from 'jquery'

window.AssentVirtualGrid = (function () {
  var self
  var startId, limit

  return {
    getTemplate: function () {
      return [
        '<!--[D] 링크가 아닌 경우 div 로 교체 -->',
        '<a href="{{profilePage}}" class="list-inner-item">',
        '<!--[D] 실제 이미지 사이즈는 모바일 대응 위해 일대일 비율로 96*96 이상-->',
        '<div class="img-thumbnail"><img src="{{profileImage}}" width="48" height="48" alt="{{displayName}}" /></div>',
        '<div class="list-text">',
        '<p>{{displayName}}</p>',
        '</div>',
        '</a>'
      ].join('\n')
    },
    init: function () {
      self = AssentVirtualGrid

      $('.xe-list-group').css('height', '365px')

      startId = 0
      limit = 10

      DynamicLoadManager.jsLoad('/assets/core/xe-ui-component/js/xe-infinite.js', function () {
        XeInfinite.init({
          wrapper: '.xe-list-group',
          template: self.getTemplate(),
          loadRowCount: 3,
          rowHeight: 80,
          onGetRows: self.onGetRows
        })
      })

      return self
    },
    onGetRows: function () {
      XeInfinite.setPrevent(true)

      var data = {
        limit: limit
      }

      if (startId !== 0) {
        data.startId = startId
      }

      window.XE.ajax({
        url: $('.xe-list-group').data('url'),
        type: 'get',
        dataType: 'json',
        data: data,
        success: function (data) {
          if (data.nextStartId === 0) {
            XeInfinite.setPrevent(true)
          } else {
            XeInfinite.setPrevent(false)
          }

          startId = data.nextStartId

          for (var k = 0, max = data.list.length; k < max; k += 1) {
            XeInfinite.addItems(data.list[k])
          }
        }
      })
    }
  }
})()

$(function () {
  $('.__xe-bd-favorite').on('click', function (event) {
    event.preventDefault()
    var $target = $(event.target),
      $anchor = $target.closest('a'),
      id = $anchor.data('id'),
      url = $anchor.data('url')
    window.XE.ajax({
      url: url,
      type: 'post',
      dataType: 'json',
      data: {id: id}
    }).done(function (json) {
      if (json.favorite === true) {
        $anchor.addClass('on')
      } else {
        $anchor.removeClass('on')
      }
    })
  })

  $('.__xe-forms .__xe-dropdown-form input').on('change', function (event) {
    var $target = $(event.target),
      $frm = $('.__xe_search')

    $frm.find('[name="' + $target.attr('name') + '"]').val($target.val())
    $frm.submit()
  })

  $('.__xe-period .__xe-dropdown-form input').on('change', function (event) {
    var $target = $(event.target),
      $frm = $('.__xe_search'),
      period = $target.val()

    var startDate = '',
      endDate = moment().format('YYYY-MM-DD'),
      $startDate = $(event.target).closest('.__xe-period').find('[name="start_created_at"]'),
      $endDate = $(event.target).closest('.__xe-period').find('[name="end_created_at"]')

    switch (period) {
      case '1week' :
        startDate = moment().add(-1, 'weeks').format('YYYY-MM-DD')
        break
      case '2week' :
        startDate = moment().add(-2, 'weeks').format('YYYY-MM-DD')
        break
      case '1month' :
        startDate = moment().add(-1, 'months').format('YYYY-MM-DD')
        break
      case '3month' :
        startDate = moment().add(-3, 'months').format('YYYY-MM-DD')
        break
      case '6month' :
        startDate = moment().add(-6, 'months').format('YYYY-MM-DD')
        break
      case '1year' :
        startDate = moment().add(-1, 'years').format('YYYY-MM-DD')
        break
    }

    $startDate.val(startDate)
    $endDate.val(endDate)
  })

  $('.__xe-bd-mobile-sorting').on('click', function () {
    event.preventDefault()
    var $container = $('.__xe-forms')
    if ($container.hasClass('xe-hidden-xs')) {
      $container.removeClass('xe-hidden-xs')
      $('.board .bd_dimmed').show()
    } else {
      $container.addClass('xe-hidden-xs')
      $('.board .bd_dimmed').hide()
    }
  })

  $('.__xe-bd-manage').on('click', function () {
    $('.bd_manage_detail').toggle()
  })

  $('.__xe-bd-search').on('click', function (event) {
    event.preventDefault()
    $(this).toggleClass('on')

    if ($(this).hasClass('on')) {
      $('.bd_search_area').show()
      $('.bd_search_input').focus()
    } else {
      $('.bd_search_area').hide()
    }
  })

  $('.bd_btn_detail').on('click', function (e) {
    $(this).toggleClass('on')
    if ($(this).hasClass('on')) {
      $('.bd_search_detail').show()
    } else {
      $('.bd_search_detail').hide()
    }
  })

  // submit title content search form
  $('.__xe_simple_search').on('submit', function (event) {
    event.preventDefault()

    var $frm = $('.__xe_search')
    $frm.find('[name="title_pure_content"]').val($(this).find('[name="title_pure_content"]').val())
    $frm.submit()
  })

  // submit search close
  $('.bd_btn_cancel').on('click touchstart', function (event) {
    event.preventDefault()
    $(event.target).closest('form').find('.bd_search_detail').hide()
  })

  // submit search form
  $('.bd_btn_search').on('click touchstart', function (event) {
    event.preventDefault()
    $(event.target).closest('form').submit()
  })

  // check all of manager contents checkbox
  $('.bd_btn_manage_check_all').on('click touchstart', function (event) {
    $('.bd_manage_check').prop('checked', $(event.target).prop('checked'))
  })

  // open file
  $('.bd_btn_file').on('click touchstart', function (event) {
    event.preventDefault()
    $(event.target).closest('a').toggleClass('on')
  })

  // click like button
  $('.bd_like').on('click touchstart', function (event) {
    event.preventDefault()
    var $target = $(event.target).closest('a')

    var url = $target.data('url')

    window.XE.ajax({
      url: url,
      type: 'post',
      dataType: 'json'
    }).done(function (json) {
      $target.toggleClass('voted')
      $('.bd_like_num').text(json.counts.assent)
    })
  })

  $('.bd_delete').on('click touchstart', function (event) {
    event.preventDefault()
    if (confirm(window.XE.Lang.trans('board::msgDeleteConfirm'))) {
      var url = $(this).data('url')
      var $form = $('<form>', {
        action: url,
        method: 'post'
      }).append($('<input>', {
        type: 'hidden',
        name: '_token',
        value: window.XE.Request.options.headers['X-CSRF-TOKEN']
      })).append($('<input>', {
        type: 'hidden',
        name: '_method',
        value: 'delete'
      }))
      $('body').append($form)
      $form.submit()
    }
  })

  $('.bd_like_num').on('click touchstart', function (event) {
    event.preventDefault()
    if (parseInt($(event.target).text()) == 0) {
      return
    }
    var $target = $(event.target).closest('a')
    var url = $target.data('url')
    window.XE.page(url, '#bd_like_more' + $target.data('id'), {}, function () {
      $('#bd_like_more' + $target.data('id')).show()
    })
  })

  // 안됨
  $('.bd_like_more_text a').on('click touchstart', function (event) {
    event.preventDefault()
    if (parseInt($(event.target).text()) == 0) {
      return
    }
    var $target = $(event.target).closest('a')
    var url = $target.prop('href')
    window.XE.pageModal(url)
  })

  // open file
  $('.bd_share').on('click touchstart', function (event) {
    event.preventDefault()
    $(event.target).closest('a').toggleClass('on')
  })

  var getUsers = {
    activate: true,
    currentPage: 1,
    perPage: 10,
    get: function (url, callback) {
      // 활성화 되어 있지 않으면 동작 안함
      if (getUsers.activate === false) {
        return
      }

      var params = {
        perPage: this.perPage,
        page: this.currentPage
      }

      window.XE.ajax({
        url: url,
        type: 'get',
        dataType: 'json',
        data: params
      }).done(function (json) {
        ++getUsers.currentPage
        if (json.current_page > json.last_page) {
          // stop infinite scroll event
          getUsers.activate = false
        }
        callback(json)
      })
    }
  }

  var resize = function ($o, w, h, mw, mh) {
    if (h > mh) {
      var ratio = mh / h
      $o.css('height', mh)
      $o.css('width', w * ratio)
      var w = w * ratio, h = h * ratio
    }
  }

  $('.board_list .thumb_area img').each(function () {
    var $o = $(this)
    if ($o.data('resize') !== undefined) {
      return
    }

    var maxWidth = $o.prop('clientWidth')
    var width = $o.prop('naturalWidth')
    var maxHeight = $o.prop('clientHeight')
    var height = $o.prop('naturalHeight')

    if (width == 0 || maxHeight == 0) {
      return
    }
    $o.data('resize', '1')

    resize($o, width, height, maxWidth, maxHeight)
  })
  $('.board_list .thumb_area img').bind('load', function () {
    var $o = $(this)
    if ($o.data('resize') !== undefined) {
      return
    }
    $o.data('resize', '2')

    var maxWidth = $o.prop('clientWidth')
    var width = $o.prop('naturalWidth')
    var maxHeight = parseInt($o.css('max-height').replace('px', ''))
    var height = $o.prop('naturalHeight')

    resize($o, width, height, maxWidth, maxHeight)
  })
})

$(function () {
  $('.__board_form').on('click', '.__xe_btn_submit', function (event) {
    event.preventDefault()
    var $this = $(this)
    var form = $this.closest('form')
    form.trigger('submit')
  }).on('click', '.__xe_btn_preview', function (event) {
    event.preventDefault()

    var form = $(this).parents('form')

    var currentUrl = form.attr('action')
    var currentTarget = form.attr('target')
    form.attr('action', form.data('url-preview'))
    form.attr('target', '_blank')
    form.submit()

    form.attr('action', currentUrl)
    form.attr('target', currentTarget === undefined ? '' : currentTarget)
  }).on('click', '.__xe_temp_btn_save', function (event) {
    // var form = $('#board_form');
    // var temporary = $('textarea', form).temporary({
    //    key: 'document|' + form.data('instance_id'),
    //    btnLoad: $('.__xe_temp_btn_load', form),
    //    btnSave: $('.__xe_temp_btn_save', form),
    //    container: $('.__xe_temp_container', form),
    //    withForm: true,
    //    callback: function (data) {
    //        console.log(data);
    //        if (xe3CkEditors['xeContentEditor']) {
    //            xe3CkEditors['xeContentEditor'].setData($('textarea', this.dom).val());
    //        }
    //    }
    // });
  })
})

// manage
$(function ($) {
  // copy documents
  $('.__xe_copy .__xe_btn_submit').on('click', function (event) {
    event.preventDefault()
    if (hasChecked() === false) {
      return
    }

    var ids = getCheckedIds(),
      instanceId = $('.__xe_copy').find('[name="copyTo"]').val()

    if (instanceId == '') {
      window.XE.toast('warning', window.XE.Lang.trans('board::selectBoard'))
      return
    }

    $.ajax({
      type: 'post',
      dataType: 'json',
      data: {id: ids, instance_id: instanceId},
      url: $(event.target).data('url'),
      success: function (response) {
        document.location.reload()
      }
    })
  })

  $('.__xe_move .__xe_btn_submit').on('click', function (event) {
    if (hasChecked() === false) {
      return
    }

    event.preventDefault()

    var ids = getCheckedIds(),
      instanceId = $('.__xe_move').find('[name="moveTo"]').val()

    if (instanceId == '') {
      window.XE.toast('warning', window.XE.Lang.trans('board::selectBoard'))
      return
    }

    $.ajax({
      type: 'post',
      dataType: 'json',
      data: {id: ids, instance_id: instanceId},
      url: $(event.target).data('url'),
      success: function (response) {
        document.location.reload()
      }
    })
  })

  $('.__xe_to_trash').on('click', 'a:first', function (event) {
    event.preventDefault()
    if (hasChecked() === false) {
      return
    }

    var ids = getCheckedIds()
    $.ajax({
      type: 'post',
      dataType: 'json',
      data: {id: ids},
      url: $(event.target).data('url'),
      success: function (response) {
        document.location.reload()
      }
    })
  })

  $('.__xe_delete').on('click', 'a:first', function (event) {
    event.preventDefault()
    if (hasChecked() === false) {
      return
    }

    var ids = getCheckedIds()
    $.ajax({
      type: 'post',
      dataType: 'json',
      data: {id: ids},
      url: $(event.target).data('url'),
      success: function (response) {
        document.location.reload()
      }
    })
  })

  var hasChecked = function () {
    if ($('.bd_manage_check:checked').length == 0) {
      window.XE.toast('warning', window.XE.Lang.trans('board::selectPost'))
      return false
    }
    return true
  }

  var getCheckedIds = function () {
    var checkedIds = []
    $('.bd_manage_check:checked').each(function () {
      checkedIds.push($(this).val())
    })

    return checkedIds
  }
})

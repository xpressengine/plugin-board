var BoardToggleMenu = {
  delete: function(e, url) {
    e.preventDefault();

    if (confirm(window.XE.Lang.trans('board::msgDeleteConfirm'))) {
      url = url + window.location.search;

      XE.ajax({
        url: url,
        type: 'delete',
        dataType: 'json',
        success: function (data) {
          location.replace(data.links.href);
        }
      });
    }
  },
  trash: function(e, url, id) {
    e.preventDefault();

    if (confirm(window.XE.Lang.trans('board::msgTrashConfirm'))) {
      url = url + window.location.search;

      XE.ajax({
        url: url,
        type: 'post',
        data: {
          id: id
        },
        dataType: 'json',
        success: function (data) {
          location.replace(data.links.href);
        }
      });
    }
  },
  adopt: function(e, url) {
    e.preventDefault();

    url = url + window.location.search;

    XE.ajax({
      url: url,
      type: 'post',
      dataType: 'json',
      success: function (data) {
        location.replace(data.links.href);
      }
    });
  },
  unAdopt: function(e, url) {
    e.preventDefault();

    url = url + window.location.search;

    XE.ajax({
      url: url,
      type: 'post',
      dataType: 'json',
      success: function (data) {
        location.replace(data.links.href);
      }
    });
  }
};









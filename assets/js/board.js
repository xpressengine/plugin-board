var AssentVirtualGrid = (function() {

    var self, grid, dataView;

    var ajaxRunning = false;    //ajax중인지

    var startId,
        limit,
        isLastRow = false;      //마지막 row인지

    return {
        init: function() {

            var self = AssentVirtualGrid;
            var columns = [{
                //selectable: false,
                formatter: function(row, cell, value, columnDef, dataContext) {
                    var tmpl = [
                        '<!--[D] 링크가 아닌 경우 div 로 교체 -->',
                        '<a href="__profilePage__" class="list-inner-item">',
                        '<!--[D] 실제 이미지 사이즈는 모바일 대응 위해 일대일 비율로 96*96 이상-->',
                            '<div class="img-thumbnail"><img src="__src__" width="48" height="48" alt="__alt__" /></div>',
                            '<div class="list-text">',
                                '<p>__alt__</p>',
                            '</div>',
                        '</a>',
                    ].join("\n");

                    return tmpl.replace(/__src__/g, dataContext.profileImage).replace(/__alt__/g, dataContext.displayName).replace(/__profilePage__/g, dataContext.profilePage);
                }
            }];

            var options = {
                editable: false,
                enableAddRow: true,
                enableColumnReorder: false,
                enableCellNavigation: false,
                // asyncEditorLoading: false,
                // autoEdit: false,
                rowHeight: 80,
                headerHeight: 0,
                showHeaderRow: false
            };

            // var data = [];
            $(".xe-list-group").css("height", "365px");
            dataView = new Slick.Data.DataView();
            grid = new Slick.Grid(".xe-list-group", dataView, columns, options);
            grid.setHeaderRowVisibility(false);

            $(".slick-header").hide();


            id= 0;
            ajaxRunning = false;
            isLastRow = false;
            startId = 0;
            limit = 10;

            self.getRows();
            self.bindEvent();

            return self;
        },
        bindEvent: function() {
            grid.onScroll.subscribe(function(e, args) {

                var $viewport = $(".xe-modal").find(".slick-viewport"),
                    loadBlockCnt = 3;   //3 page 정도 남으면 reload함, 1page - modal body height 기준.

                if(!ajaxRunning && !isLastRow && ($viewport[0].scrollHeight - $viewport.scrollTop()) < ($viewport.outerHeight() * loadBlockCnt)) {
                    AssentVirtualGrid.getRows();
                }

            });

            dataView.onRowCountChanged.subscribe(function (e, args) {
                grid.updateRowCount();
                grid.render();
            });

            dataView.onRowsChanged.subscribe(function (e, args) {
                grid.invalidateRows(args.rows);
                grid.render();
            });
        },
        getRows: function() {

            ajaxRunning = true;

            var data = {
                limit: limit
            };

            if(startId !== 0) {
                data.startId = startId;
            }

            XE.ajax({
                url: $(".xe-list-group").data('url'),
                type: 'get',
                dataType: 'json',
                data: data,
                success: function(data) {

                    if(data.nextStartId === 0) {
                        isLastRow = true;
                    }

                    startId = data.nextStartId;

                    for(var k = 0, max = data.list.length; k < max; k += 1) {
                        dataView.addItem(data.list[k]);
                    }

                }
            }).done(function() {
                ajaxRunning = false;
            });
        }
    }
})();

$(function($) {
    $('.__xe-bd-favorite').on('click', function(event) {
        event.preventDefault();
        var $target = $(event.target),
            $anchor = $target.closest('a'),
            id = $anchor.data('id')
            url = $anchor.prop('href');

        XE.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: {id:id}
        }).done(function (json) {
            if (json.favorite === true) {
                $anchor.addClass('on');
            } else {
                $anchor.removeClass('on');
            }
        });
    });

    $('.__xe-forms .__xe-dropdown-form input').on('change', function(event) {
        var $target = $(event.target),
            $frm = $('.__xe_search');

        $frm.find('[name="'+$target.attr('name')+'"]').val($target.val());
        $frm.submit();
    });

    $('.__xe-period .__xe-dropdown-form input').on('change', function(event) {
        var $target = $(event.target),
            $frm = $('.__xe_search'),
            period = $target.val();

        System.import('vendor:/moment').then(function(moment) {
            var startDate = '',
                endDate = moment().format('YYYY-MM-DD'),
                $startDate = $(event.target).closest('.__xe-period').find('[name="startCreatedAt"]'),
                $endDate = $(event.target).closest('.__xe-period').find('[name="endCreatedAt"]');

            switch (period) {
                case '1week' :
                    startDate = moment().add(-1, 'weeks').format('YYYY-MM-DD');
                    break;
                case '2week' :
                    startDate = moment().add(-2, 'weeks').format('YYYY-MM-DD');
                    break;
                case '1month' :
                    startDate = moment().add(-1, 'months').format('YYYY-MM-DD');
                    break;
                case '3month' :
                    startDate = moment().add(-3, 'months').format('YYYY-MM-DD');
                    break;
                case '6month' :
                    startDate = moment().add(-6, 'months').format('YYYY-MM-DD');
                    break;
                case '1year' :
                    startDate = moment().add(-1, 'years').format('YYYY-MM-DD');
                    break;
            }

            $startDate.val(startDate);
            $endDate.val(endDate);
        });


    });

    $('.__xe-bd-mobile-sorting').on('click', function() {
        event.preventDefault();
        var $container = $('.__xe-forms');
        if ($container.hasClass('xe-hidden-xs')) {
            $container.removeClass('xe-hidden-xs');
            $(".board .bd_dimmed").show();
        } else {
            $container.addClass('xe-hidden-xs');
            $(".board .bd_dimmed").hide();
        }

    });

    $('.__xe-bd-manage').on('click', function() {
        $('.bd_manage_detail').toggle();
    });

    $('.__xe-bd-search').on('click', function(event) {
        event.preventDefault();
        $(this).toggleClass("on");

        if($(this).hasClass("on")){
            $(".bd_search_area").show();
            $(".bd_search_input").focus();
        } else{
            $(".bd_search_area").hide();
        }

        $(".bd_btn_detail").on("click", function(e){
            $(this).toggleClass("on");
            if($(this).hasClass("on")){
                $(".bd_search_detail").show();
            } else{
                $(".bd_search_detail").hide();
            }
        });
    });
    // submit title content search form
    $('.__xe_simple_search').on('submit', function(event) {
        event.preventDefault();

        var $frm = $('.__xe_search');
        $frm.find('[name="title_pureContent"]').val($(this).find('[name="title_pureContent"]').val());
        $frm.submit();
    });

    // submit search close
    $('.bd_btn_cancel').on('click touchstart', function(event) {
        event.preventDefault();
        $(event.target).closest('form').find('.bd_search_detail').hide();
    });

    // submit search form
    $('.bd_btn_search').on('click touchstart', function(event) {
        event.preventDefault();
        $(event.target).closest('form').submit();
    });

    // check all of manager contents checkbox
    $('.bd_btn_manage_check_all').on('click touchstart', function(event) {
        $('.bd_manage_check').prop('checked', $(event.target).prop('checked'));
    });

    // open file
    $('.bd_btn_file').on('click touchstart', function(event) {
        event.preventDefault();
        $(event.target).closest('a').toggleClass('on');
    });

    // click like button
    $('.bd_like').on('click touchstart', function(event) {
        event.preventDefault();
        var $target = $(event.target).closest('a');

        var url = $target.prop('href');
        //if ($target.hasClass('voted')) {
        //    url = $target.data('remove-url');
        //}

        XE.ajax({
            url: url,
            type: 'post',
            dataType: 'json'
        }).done(function (json) {
            $target.toggleClass('voted');
            $('.bd_like_num').text(json.counts.assent);
        });

    });

    $('.bd_like_num').on('click touchstart', function(event) {
        event.preventDefault();
        if (parseInt($(event.target).text()) == 0) {
            return;
        }
        var $target = $(event.target).closest('a');
        var url = $target.prop('href');
        XE.page(url, '#bd_like_more'+$target.data('id'), {}, function() {
            $('#bd_like_more'+$target.data('id')).show();
        });
    });

    // 안됨
    $('.bd_like_more_text a').on('click touchstart', function(event) {
        event.preventDefault();
        if (parseInt($(event.target).text()) == 0) {
            return;
        }
        var $target = $(event.target).closest('a');
        var url = $target.prop('href');
        XE.pageModal(url);
    });

        // click like number. show like member list
    $('.bd_like_num-notuse').on('click touchstart', function(event) {
        event.preventDefault();
        var $target = $('.bd_like_more');

        // on class 가 없다면 list 를 보기 위한 click
        if ($target.hasClass('on') === false) {
            var $modal = $('#xe-modal-list');

            if ($modal.length != 0) {
                $modal.empty();
            }

            $modal = $('<div class="modal fade" id="xe-Modal-list">');
            $modal.xeModal({show:false});
            $('body').append($modal);

            getUsers.currentPage = 1;
            getUsers.get($(event.target).attr('href'), function(json) {
                // modal css load
                //XE.cssLoad('/assets/vendor/core/css/modal.css');

                // draw list
                var $ul = $('<ul>').addClass('list-group');
                $.each(json.users, function(index, user) {
                    var li = $('<li>').append(
                        $('<div>').append(
                            $('<div>').addClass('img_thmb').append($('<img>').attr('src', user.profileImage).attr('alt', user.displayName).attr('height', 48).attr('width', 48))
                        ).append(
                            $('<div>').addClass('list_txt').append($('<a href="#">').addClass('__xe_member').text(user.displayName).attr('data-id', user.id).attr('data-text', user.displayName))
                        )
                    );
                    if (user.id == XE.options.loginUserId) {
                        //li.addClass('on');
                    }
                    $ul.append(li);
                });

                $modal.append($('<div class="modal-dialog">').append($('<div class="modal-content">').append(
                    $('<div class="modal-header bg_blue">').html('<button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="xi-close-thin"></i></button> <p class="modal-title">이 게시물을 좋아하는 사람들</p>')
                ).append($ul)));

                $modal.xeModal({show:true});
            });

        } else {
            var $modal = $('#xe-modal-list');
            if ($modal.length != 0) {
                $modal.xeModal({show: false});
            }
            $target.removeClass('on');
        }
    });

    // open file
    $('.bd_share').on('click touchstart', function(event) {
        event.preventDefault();
        $(event.target).closest('a').toggleClass('on');
    });

    var getUsers = {
        activate: true,
        currentPage: 1,
        perPage: 10,
        get: function(url, callback) {
            // 활성화 되어 있지 않으면 동작 안함
            if (getUsers.activate === false) {
                return;
            }

            var params = {
                perPage: this.perPage,
                page: this.currentPage
            };

            XE.ajax({
                url: url,
                type: 'get',
                dataType: 'json',
                data: params
            }).done(function (json) {
                ++getUsers.currentPage;
                if (json.current_page > json.last_page) {
                    // stop infinite scroll event
                    getUsers.activate = false;
                }
                callback(json);
            });
        }
    }

    // 엔터 or 콤마 입력 들어오면 태그 만들고 입력창 비움
    $('.__xe-board-tag .search-label').bind('keypress', function(event) {
        var $target = $(event.target),
            $ul = $target.closest('ul');

        console.log(event.keyCode);
        if (event.keyCode == 13) {
            var tag = $target.val();
            var $li = $('<li>').html('<span class="label-choice">'+
                tag+
                '<button type="button"><i class="xi-close"></i></button></span>');
            $ul.append($li);

            $target.val('');
        } else if (event.keyCode == 44) {
            // 콤마 입력은 무시하고 keyup 할 때 처리함.. 이건 잘못한것 같음
            event.preventDefault();
        }
    }).bind('keyup', function(event) {
        var $target = $(event.target),
            $ul = $target.closest('ul');
        if (event.keyCode == 188) {
            var tag = $target.val();
            var $li = $('<li>').html('<span class="label-choice">'+
                tag+
                '<button type="button"><i class="xi-close"></i></button></span>');
            $ul.append($li);

            $target.val('');
        }
    });

    $('.__xe-board-tag').on('click', '.xi-close', function(event) {
        var $target = $(event.target);
        $target.closest('li').empty();
    });
});

$(function($) {
    $('.__board_form').on('click', '.__xe_btn_submit', function (event) {
        event.preventDefault();
        var form = $(this).closest('form');
        form.trigger('submit');
    }).on('click', '.__xe_btn_preview', function (event) {
        event.preventDefault();

        var form = $(this).parents('form');

        var currentUrl = form.attr('action');
        var currentTarget = form.attr('target');
        form.attr('action', form.data('url-preview'));
        form.attr('target', '_blank');
        form.submit();

        form.attr('action', currentUrl);
        form.attr('target', currentTarget === undefined ? '' : currentTarget);
    }).on('click', '.__xe_temp_btn_save', function (event) {
        //var form = $('#board_form');
        //var temporary = $('textarea', form).temporary({
        //    key: 'document|' + form.data('instanceId'),
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
        //});
    });
});

// manage
$(function($) {
    // copy documents
    $('.__xe_copy .__xe_btn_submit').on('click', function(event) {
        event.preventDefault();
        if (hasChecked() === false) {
            return;
        }

        var ids = getCheckedIds(),
            instanceId = $('.__xe_copy').find('[name="copyTo"]').val();

        if (instanceId == '') {
            XE.toast('warning', XE.Lang.trans('board::selectBoard'));
            return;
        }

        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {id:ids, instanceId: instanceId},
            url: $(event.target).data('href'),
            success: function(response) {
                document.location.reload();
            }
        });
    });

    $('.__xe_move .__xe_btn_submit').on('click', function(event) {
        if (hasChecked() === false) {
            return;
        }

        event.preventDefault();

        var ids = getCheckedIds(),
            instanceId = $('.__xe_move').find('[name="moveTo"]').val();

        if (instanceId == '') {
            XE.toast('warning', XE.Lang.trans('board::selectBoard'));
            return;
        }

        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {id:ids, instanceId: instanceId},
            url: $(event.target).data('href'),
            success: function(response) {
                document.location.reload();
            }
        });
    });

    $('.__xe_to_trash').on('click', 'a:first', function(event) {
        event.preventDefault();
        if (hasChecked() === false) {
            return;
        }

        var ids = getCheckedIds();
        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {id:ids},
            url: $(event.target).prop('href'),
            success: function(response) {
                document.location.reload();
            }
        });
    });

    $('.__xe_delete').on('click', 'a:first', function(event) {
        event.preventDefault();
        if (hasChecked() === false) {
            return;
        }

        var ids = getCheckedIds();
        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {id:ids},
            url: $(event.target).prop('href'),
            success: function(response) {
                document.location.reload();
            }
        });
    });


    var hasChecked = function() {
        if ($('.bd_manage_check:checked').length == 0) {
            XE.toast('warning', XE.Lang.trans('board::selectPost'));
            return false;
        }
        return true;
    };

    var getCheckedIds = function() {
        var checkedIds = [];
        $('.bd_manage_check:checked').each(function() {
            checkedIds.push($(this).val());
        });

        return checkedIds;
    }
});

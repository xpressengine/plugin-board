{{ XeFrontend::js('plugins/board/assets/js/board.js')->appendTo('body')->load() }}
{{ XeFrontend::js('plugins/board/assets/js/userSkin.js')->appendTo('body')->load() }}
{{ XeFrontend::css('plugins/board/assets/css/board.css')->load() }}

<script type="text/javascript">
    XE.$(function($) {
        $(document).on('click touchstart', function(event) {
            var $target = $(event.target);

            open_select_box($target, event);
            open_select_area($target, event);
        });

        // category change
        $('.__xe_category_change').on('click', 'li', function(event) {
            var $input = $(this).closest('.__xe_category_change').find('input'),
                name = $input.prop('name'),
                categoryId = $(event.target).data('value');

            // search submit
            var $frm = $('.__xe_search');
            $frm.find('[name="'+name+'"]').val(categoryId);
            $frm.submit();
        });

        // order change
        $('.__xe_order_change').on('click', 'li', function(event) {
            var $input = $(this).closest('.__xe_order_change').find('input'),
                    name = $input.prop('name'),
                    order = $(event.target).data('value');

            // search submit
            var $frm = $('.__xe_search');
            $frm.find('[name="'+name+'"]').val(order);
            $frm.submit();
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
            var $target = $(event.target).closest('a'),
                    params = {
                        id: $target.data('id')
                    };

            var url = $target.data('add-url');
            if ($target.hasClass('invoked')) {
                url = $target.data('remove-url');
            }

            XE.ajax({
                url: url,
                type: 'post',
                dataType: 'json',
                data: params
            }).done(function (json) {
                $target.toggleClass('invoked');
                $('.bd_like_num').text(json.counts.assent);
            });

        });

        // click like number. show like member list
        $('.bd_like_num').on('click touchstart', function(event) {
            event.preventDefault();
            var $target = $('.bd_like_more');

            // on class 가 없다면 list 를 보기 위한 click
            if ($target.hasClass('on') === false) {
                var $modal = $('#myModal-list');

                if ($modal.length != 0) {
                    $modal.empty();
                }

                $modal = $('<div class="modal fade" id="myModal-list">');
                $modal.modal({show:false});
                $('body').append($modal);

                getUsers.currentPage = 1;
                getUsers.get($(event.target).attr('href'), function(json) {
                    // modal css load
                    XE.cssLoad('/assets/vendor/core/css/modal.css');

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

                    $modal.modal({show:true});
                });

            } else {
                var $modal = $('#myModal-list');
                if ($modal.length != 0) {
                    $modal.modal({show: false});
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

        // open select box - design select box
        function open_select_box($target, event)
        {
            if ($target.hasClass('__xe_select_box_show')) {
                event.preventDefault();
                var $dst = $target.next('.bd_select_list');

                if ($dst.length === 0) {
                    $dst = $target.closest('.bd_select_area').next('.bd_select_list');
                }

                var isVisible = false;
                if ($dst.is(':visible')) {
                    isVisible = true;
                }

                $('.bd_select_list').hide();

                if (isVisible !== true) {
                    $dst.show();
                }

            } else {
                $('.bd_select_list').hide();
            }

            if ($target.hasClass('__xe_search_box_show')) {
                $('.__xe_search_area').toggle();
            }
        }

        // open select area
        function open_select_area($target, event)
        {
            if ($target.hasClass('__xe_select_area_show')) {
                event.preventDefault();
                var $dst = $($target.data('selector'));

                if ($dst.is(':visible')) {
                    $dst.hide();
                } else {
                    $dst.show();
                }
            }
        }
    });
</script>

@if($isManager === true)
<script type="text/javascript">
XE.$(function($) {
    // copy documents
    $('.__xe_copy').on('click', 'li', function(event) {
        if (hasChecked() === false) {
            return;
        }

        var instanceId = $(event.target).data('value');
        $('.__xe_copy').find('[name="copyTo"]').val(instanceId);
        $('.__xe_copy').find('a.__xe_btn_submit').show();

    }).on('click', 'a.__xe_btn_submit', function(event) {
        event.preventDefault();

        var ids = getCheckedIds(),
            instanceId = $('.__xe_copy').find('[name="copyTo"]').val();
        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {id:ids, instanceId: instanceId},
            url: $(event.target).prop('href'),
            success: function(response) {
                document.location.reload();
            }
        });
    });

    $('.__xe_move').on('click', 'li', function(event) {
        if (hasChecked() === false) {
            return;
        }

        var instanceId = $(event.target).data('value');
        $('.__xe_move').find('[name="moveTo"]').val(instanceId);
        $('.__xe_move').find('a.__xe_btn_submit').show();

    }).on('click', 'a.__xe_btn_submit', function(event) {
        event.preventDefault();

        var ids = getCheckedIds(),
            instanceId = $('.__xe_move').find('[name="moveTo"]').val();
        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {id:ids, instanceId: instanceId},
            url: $(event.target).prop('href'),
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
        var instanceId = $(event.target).data('value');
        $('.__xe_to_trash').find('a.__xe_btn_submit').show();

    }).on('click', 'a.__xe_btn_submit', function(event) {
        event.preventDefault();

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
        var instanceId = $(event.target).data('value');
        $('.__xe_delete').find('a.__xe_btn_submit').show();

    }).on('click', 'a.__xe_btn_submit', function(event) {
        event.preventDefault();

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
            XE.toast('info', 'There is no checked post');
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
</script>
@endif
<style>
    .bd_function .bd_like.invoked{color:#FE381E}
</style>

<!-- BOARD -->
<div class="board">
    @yield('content', isset($content) ? $content : '')
</div>
<!-- /BOARD -->

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
            console.log(json);
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

    $('.__xe-bd-manage').on('click', function() {
        $('.bd_manage_detail').toggle();
    });

    $('.__xe-bd-search').on('click', function() {
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
        $('.bd_like_more').toggle();
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
});

var XeBoardSkin = {
    VoteBox: React.createClass({
        getInitialState: function () {
            this.request('show', '');

            return {
                data: {
                    display: {assent: false, dissent: false},
                    counts: {assent:0, dissent:0},
                    voteAt: null  // 참여 안됨
                }
            };
        },

        request: function (action, option) {
            var self = this;

            if (action == undefined) {
                action = 'show';
            }

            var type = 'post';
            if (action == 'show' || action == 'users') {
                type = 'get';
            }

            if (option == undefined) {
                option = '';
            }

            var params = {id:this.props.id};

            var url = this.props.url + '/' + action;
            if (option != '') {
                url = url + '/' + option;
            }

            XE.ajax({
                url: url,
                type: type,
                dataType: 'json',
                data: params
            }).done(function (json) {
                self.setState({data: json});
            });

        },

        componentDidMount: function() {
        },

        render: function () {
            return React.DOM.div({
                    className:'board_document_votebox btn-group'
                },
                React.createElement(XeBoardSkin.VoteButton, $.extend({}, this.state.data, {option:'assent', cb:this.request})),
                React.createElement(XeBoardSkin.VoteButton, $.extend({}, this.state.data, {option:'dissent', cb:this.request}))
            )
        }
    }),

    VoteButton: React.createClass({
        onclick: function(e) {
            var action = 'add';
            if (this.props.voteAt == this.props.option) {
                action = 'remove';
            }

            this.props.cb(action, this.props.option);
        },

        render: function() {

            var classNames = ['btn', 'btn-'+this.props.option];

            if (this.props.voteAt == this.props.option) {
                classNames.push('voted');
            } else if (this.props.voteAt != null) {
                classNames.push('disabled');
            }

            var iconClassName = 'glyphicon-chevron-' + (this.props.option == 'assent' ? 'up' : 'down');
            var count = this.props.option == 'assent' ? this.props.counts.assent : this.props.counts.dissent;
            var display = this.props.display[this.props.option] == false ? 'none' : 'block';

            return React.DOM.button({
                    className: classNames.join(' '),
                    onClick: this.onclick,
                    style:{display: display}
                }
                , React.DOM.span({className:'glyphicon ' + iconClassName})
                , React.DOM.br({})
                , React.DOM.span({style:{margin:'10px 10px'}}, count)
            );
        }
    })
};

$(function($) {
    if ($('.__xe_vote_document').length) {
        React.render(
            React.createElement(XeBoardSkin.VoteBox, {
                url: $('.__xe_vote_document').attr('data-url'),
                id: $('.__xe_vote_document').attr('data-id')
            }),
            $('.__xe_vote_document')[0]
        );
    }

    if ($('.__xe_manage_menu_document').length) {
        React.render(
            React.createElement(ToggleMenu, {
                type: "module/board@board/" +  $('.__xe_manage_menu_document').attr('data-instance-id'),
                identifier: $('.__xe_manage_menu_document').attr('data-id'),
                align: 'right',
                //class: '',
                //text: ' • • • ',
                //itemClass: '',
                html: $('.__xe_manage_menu_document').html(),
                data: {
                    id: $('.__xe_manage_menu_document').attr('data-id')
                }
            }),
            $('.__xe_manage_menu_document')[0]
        );
    }

    $('.__board_form').on('click', '.__xe_btn_preview', function(event) {
        event.preventDefault();

        var form = $(this).parents('form');

        var currentUrl = form.attr('action');
        var currentTarget = form.attr('target');
        var pieces = currentUrl.split('/');
        pieces[pieces.length-1] = 'preview';
        form.attr('action', pieces.join('/'));
        form.attr('target', '_blank');
        form.submit();

        form.attr('action', currentUrl);
        form.attr('target', currentTarget === undefined ? '' : currentTarget);
    }).on('click', '.__xe_btn_submit', function(event) {
        event.preventDefault();
        var form = $(this).closest('form');
        form.trigger('submit');
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
            XE.toast('info', XE.Lang.trans('board::selectBoard'));
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
            XE.toast('info', XE.Lang.trans('board::selectBoard'));
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
            XE.toast('info', XE.Lang.trans('board::selectPost'));
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
{{ XeFrontend::js('plugins/board/assets/js/managerSkin.js')->load() }}

<div class="row">
    <div class="col-sm-12">
        <div class="panel-group">
            <div class="panel">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h3 class="panel-title">{{ xe_trans('board::articlesApprove') }}</h3>
                    </div>
                </div>

                <div class="panel-heading">

                    <div class="pull-left">
                        <div class="btn-group __xe_function_buttons" role="group" aria-label="...">
                            <button type="button" class="btn btn-default __xe_button" data-mode="approve">{{xe_trans('xe::approve')}}</button>
                        </div>
                    </div>
                    <div class="pull-right">
                        <div class="input-group search-group">
                            <form>
                                <div class="input-group-btn __xe_btn_search_target">
                                    <input type="hidden" name="searchTarget" value="{{ Input::get('searchTarget') }}">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="__xe_text">{{Input::has('searchTarget') && Input::get('searchTarget') != '' ? xe_trans('board::' . Input::get('searchTarget')) : xe_trans('xe::select')}}</span> <span class="caret"></span></button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li @if(Input::get('searchTarget') == '') class="active" @endif><a href="#" value="">{{xe_trans('xe::select')}}</a></li>
                                        <li @if(Input::get('searchTarget') == 'titleAndcontent') class="active" @endif><a href="#" value="titleAndcontent">{{xe_trans('board::titleAndContent')}}</a></li>
                                        <li @if(Input::get('searchTarget') == 'title') class="active" @endif><a href="#" value="title">{{xe_trans('board::title')}}</a></li>
                                        <li @if(Input::get('searchTarget') == 'content') class="active" @endif><a href="#" value="content">{{xe_trans('board::content')}}</a></li>
                                        <li @if(Input::get('searchTarget') == 'writer') class="active" @endif><a href="#" value="writer">{{xe_trans('board::writer')}}</a></li>
                                    </ul>
                                </div>
                                <div class="search-input-group">
                                    <input type="text" name="searchKeyword" class="form-control" aria-label="Text input with dropdown button" placeholder="{{xe_trans('xe::enterKeyword')}}" value="{{Input::get('searchKeyword')}}">
                                    <button class="btn-link">
                                        <i class="xi-magnifier"></i><span class="sr-only">{{xe_trans('xe::search')}}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
                <div class="table-responsive">
                    <form class="__xe_form_list" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col"><input type="checkbox" class="__xe_check_all"></th>
                                <th scope="col">{{xe_trans('board::title')}}</th>
                                <th scope="col">{{xe_trans('board::writer')}}</th>
                                <th scope="col">{{xe_trans('board::recommend')}}/{{xe_trans('board::read')}}</th>
                                <th scope="col">{{xe_trans('board::writeDate')}}</th>
                                <th scope="col">IP</th>
                                <th scope="col">{{xe_trans('xe::status')}}</th>
                                <th scope="col">{{xe_trans('xe::approve')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($documents as $document)
                                <tr>
                                    <td><input type="checkbox" name="id[]" class="__xe_checkbox" value="{{ $document->id }}"></td>
                                    <td><a href="/{{ $urls[$document->instanceId] }}/show/{{ $document->id }}" target="_blank"><strong>[{{ $urls[$document->instanceId] }}]</strong> {{ $document->title }}<i class="xi-new"></i><i class="xi-external-link"></i></a></td>
                                    <td><a href="#">{{ $document->writer }}</a></td>
                                    <td>{{ $document->assentCount }}/{{ $document->readCount }}</td>
                                    <td><a href="#">{{ $document->createdAt }}</a></td>
                                    <td><a href="#">{{ $document->ipaddress }}</a></td>
                                    <td><span class="label label-green">{{ $document->display }}</span></td>
                                    <td><span class="label label-grey">{{ $document->approved }}</span></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="panel-footer">
                    <div class="pull-left">
                        <nav>
                            {!! $documents->render() !!}
                        </nav>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {

        $('.__xe_check_all').click(function () {
            if ($(this).is(':checked')) {
                $('input.__xe_checkbox').click();
            } else {
                $('input.__xe_checkbox').removeAttr('checked');
            }
        });

        $('.__xe_function_buttons .__xe_button').click(function (e) {
            e.preventDefault();

            var mode = $(this).attr('data-mode'), flag = false;

            $('input.__xe_checkbox').each(function () {
                if ($(this).is(':checked')) {
                    flag = true;
                }
            });

            if (flag !== true) {
                alert('select document');
                return;
            }

            var $f = $('.__xe_form_list');
            $('<input>').attr('type', 'hidden').attr('name', 'redirect').val(location.href).appendTo($f);

            eval('actions.' + mode + '($f)');
        });

        $('.__xe_btn_search_target .dropdown-menu a').click(function (e) {
            e.preventDefault();

            $('[name="searchTarget"]').val($(this).attr('value'));
            $('.__xe_btn_search_target .__xe_text').text($(this).text());

            $(this).closest('.dropdown-menu').find('li').removeClass('active');
            $(this).closest('li').addClass('active');
        });

        $('.__xe_btns_search').on('click', 'button', function() {
            var frm = $(this).parents('form');

            if ($(this).hasClass('active') == false) {
                frm.append(
                        $('<input>').hide()
                                .attr('type', 'text')
                                .attr('name', $(this).attr('data-key'))
                                .val($(this).attr('data-value'))
                );
            }

            frm.submit();
        });

        $('.__xe_document_move').on('click', '.__xe_move_submit', function() {
            moveModal.submit();
        });
    });

    var moveModal = {
        frm: null,
        modal: $('.__xe_document_move'),
        show: function(frm) {
            this.frm = frm;
            this.modal.modal('toggle');
        },
        submit: function() {
            var instanceId = $('.__xe_select_move_instance_id option:selected').val();;
            if (instanceId == '') {
                alert('이동할 게시판을 선택하세요.');
                return;
            }

            var url = '{!! $urlHandler->managerUrl('move') !!}';
            var params = this.frm.serialize();
            params = params + '&instanceId=' + instanceId;

            var _this = this;
            $.ajax({
                type: 'post',
                dataType: 'json',
                data: params,
                url: url,
                success: function(response) {
                    _this.modal.modal('toggle');
                    document.location.reload();
                },
                error: function(response) {
                    var responseText = $.parseJSON(response.responseText);
                    var type = 'xe-danger';
                    var errorMessage = responseText.message;
                    XE.toast(type, errorMessage);
                    self.openStep('close');
                }
            });


            // 이거 ajax 로 구현 해야 함.
            //this.modal.modal('toggle');
        }
    }

    var actions = {
        approve: function ($f) {
            $('<input>').attr('type', 'hidden').attr('name', 'approved').val('approved').appendTo($f);

            $f.attr('action', '{!! $urlHandler->managerUrl('approve', Input::all()) !!}');
            send($f);
        },
        reject: function ($f) {
            $('<input>').attr('type', 'hidden').attr('name', 'approved').val('rejected').appendTo($f);

            $f.attr('action', '{!! $urlHandler->managerUrl('approve', Input::all()) !!}');
            send($f);
        },
        destroy: function ($f) {
            $f.attr('action', '{!! $urlHandler->managerUrl('destroy', Input::all()) !!}');
            send($f);
        },
        trash: function ($f) {
            $f.attr('action', '{!! $urlHandler->managerUrl('trash', Input::all()) !!}');
            send($f);
        },
        move: function ($f) {
            moveModal.show($f);
        },
        restore: function ($f) {
            $f.attr('action', '{!! $urlHandler->managerUrl('restore', Input::all()) !!}');
            send($f);
        }
    };

    var moveDocument = function($f) {
        {{--$f.attr('action', '{!! $urlHandler->managerUrl('move', Input::all()) !!}');--}}
        {{--$f.submit();--}}
        send($f);
    }

    var send = function($f) {
        var url = $f.attr('action'),
                params = $f.serialize();

        $.ajax({
            type: 'post',
            dataType: 'json',
            data: params,
            url: url,
            success: function(response) {
                document.location.reload();
            }
        });
    }
</script>

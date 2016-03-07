@section('page_title')
    <h2>게시물 승인 관리</h2>
@endsection

@section('page_description')

@endsection

{{ Frontend::js('plugins/board/assets/js/managerSkin.js')->load() }}

<div class="panel">
    <div class="panel-heading">
<section class="contain">

    <div class="row">
        <div class="col-sm-12">

            <!-- function button -->
            <div class="btn-group pull-left mg-bottom mg-right-sm __xe_function_buttons">
                <button type="button" class="btn btn-default __xe_button" data-mode="approve">
                    <i class="fa fa-check-circle-o"></i>
                    게시승인
                </button>
            </div>
            <!-- /function button -->

            <!-- search button -->
            <div class="form-inline pull-right">
                <form class="__xe_search_form" method="get">
                    <div class="input-group mg-bottom">
                        <div class="input-group-btn __xe_btn_search_target">
                            <input type="hidden" name="searchTarget" value="{{ Input::old('searchTarget') }}">
                            <button type="button" class="btn btn-default dropdown-toggle text" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">선택<span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a href="#" class="item" value="">선택</a></li>
                                <li><a href="#" class="item" value="title_content">제목+내용</a></li>
                                <li><a href="#" class="item" value="title">제목</a></li>
                                <li><a href="#" class="item" value="content">내용</a></li>
                                <li><a href="#" class="item" value="writer">글쓴이</a></li>
                            </ul>
                        </div>
                        <input type="text" class="form-control" aria-label="..." name="searchKeyword" value="{{Input::get('searchKeyword')}}">
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-default"> <i class="fa fa-search"></i> <span class="sr-only">search</span> </button>
                            <a href="http://xe3.dev1.xpressengine.com/manage/module/pluginA@board" class="btn btn-default">취소</a>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /search button -->
        </div>
    </div>


    <!-- table -->
    <div class="box box-primary mg-bottom">
        <form class="__xe_form_list" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th scope="col"><input type="checkbox" title="Check All" class="__xe_check_all"></th>
                        <th scope="col">제목</th>
                        <th scope="col">작성자</th>
                        <th scope="col"><i class="fa fa-thumbs-o-up"></i> / <i class="fa fa-thumbs-o-down"></i> / <i class="fa fa-eye"></i></th>
                        <th scope="col">날짜</th>
                        <th scope="col">IP</th>
                        <th scope="col">상태</th>
                        <th scope="col">승인</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($documents as $document)
                        <tr>
                            <td><input type="checkbox" name="id[]" class="__xe_checkbox" value="{{ $document->id }}"></td>
                            <td>
                                <span><b>[{{ $urls[$document->instanceId] }}]</b> {{ $document->title }}</span>

                                <a href="/{{ $urls[$document->instanceId] }}/show/{{ $document->id }}" class="btn" target="_blank">
                                    <i class="fa fa-link fa-lg"></i>
                                </a>
                            </td>
                            <td>{{ $document->writer }}</td>
                            <td>{{ $document->assentCount }} / {{ $document->dissentCount }} / {{ $document->readCount }}</td>
                            <td>{{ $document->createdAt }}</td>
                            <td>{{ $document->ipaddress }}</td>
                            <td>{{ $document->display }}</td>
                            <td>{{ $document->approved }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    <!-- /table -->

    <div class="row">
        <div class="col-sm-12">

            <!-- function button -->
            <div class="btn-group pull-left mg-bottom mg-right-sm __xe_function_buttons">
                <button type="button" class="btn btn-default __xe_button" data-mode="approve">
                    <i class="fa fa-check-circle-o"></i>
                    게시승인
                </button>
            </div>
            <!-- /function button -->

            <!-- search button -->
            <div class="form-inline pull-right">
                <form class="__xe_search_form" method="get">
                    <div class="input-group mg-bottom">
                        <div class="input-group-btn __xe_btn_search_target">
                            <input type="hidden" name="searchTarget" value="{{ Input::old('searchTarget') }}">
                            <button type="button" class="btn btn-default dropdown-toggle text" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">선택<span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a href="#" class="item" value="">선택</a></li>
                                <li><a href="#" class="item" value="title_content">제목+내용</a></li>
                                <li><a href="#" class="item" value="title">제목</a></li>
                                <li><a href="#" class="item" value="content">내용</a></li>
                                <li><a href="#" class="item" value="writer">글쓴이</a></li>
                            </ul>
                        </div>
                        <input type="text" class="form-control" aria-label="..." name="searchKeyword" value="{{Input::get('searchKeyword')}}">
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-default"> <i class="fa fa-search"></i> <span class="sr-only">search</span> </button>
                            <a href="{{$urlHandler->managerUrl('docs.approve')}}" class="btn btn-default">취소</a>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /search button -->
        </div>
    </div>

    <nav class="text-center">{!! $documents->render() !!}</nav>
</section>
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

        $('.__xe_btn_search_target .item').click(function (e) {
            e.preventDefault();

            $('[name="searchTarget"]').val($(this).attr('value'));
            $('.__xe_btn_search_target .text').text($(this).text());
        })

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
                    var type = 'danger';
                    var errorMessage = responseText.message;
                    alertBox(type, errorMessage);
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

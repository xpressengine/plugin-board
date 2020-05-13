@section('page_title')
    <h2>{{xe_trans('board::boardDetailConfigures')}}</h2>
    @endsection

    @section('page_description')@endsection

            <!-- Main content -->
    <div class="row">
        <div class="col-sm-12">
            <div class="panel-group">
                <div class="panel">

                    <div class="panel-warning">
                        <p>1.0.10 버전 변경사항 안내</p>
                        <ul class="panel-warning-list">
                            <li> - 기존 출력순서변경 기능은 게시판 기본스킨에서만 적용되어 기본스킨의 설정으로 이동 되었습니다.</li>
                            <li> - 목록 출력 항목, 입력/출력 페이지 출력순서 설정은 게시판 상세설정으로 이동되었습니다.</li>
                            <li> - 출력순서 탭은 다음 버전에서 삭제될 예정입니다.</li>
                        </ul>
                    </div>
                    
                    <div class="panel-heading">
                        <div class="pull-left">
                            <h3 class="panel-title">{{xe_trans('board::outputOrder')}}</h3>
                        </div>
                    </div>
                    <form method="post" id="board_manage_form" action="{!! $urlHandler->managerUrl('columns.update', ['boardId' => $boardId]) !!}">
                        <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
                    <div id="collapseTwo" class="panel-collapse collapse in">
                        <div class="panel-body">

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="">{{xe_trans('xe::list')}}</label>

                                        <div class="table-responsive item-setting">
                                            <table class="table table-sortable">
                                                <colgroup>
                                                    <col width="200">
                                                    <col>
                                                    <col>
                                                </colgroup>
                                                <tbody>
                                                @foreach($sortListColumns as $columnName)
                                                    <tr>
                                                        <td>
                                                            <button class="btn handler"><i class="xi-drag-vertical"></i></button>
                                                            <em class="item-title">{{ $columnName }}</em>
                                                        </td>
                                                        <td>
                                                            <span class="item-subtext">{{ $columnName }}</span>
                                                        </td>
                                                        <td>
                                                            <div class="xe-btn-toggle pull-right">
                                                                <label>
                                                                    <span class="sr-only">toggle</span>
                                                                    <input type="checkbox" name="listColumns[]" value="{{ $columnName }}" @if(in_array($columnName, $config->get('listColumns'))) checked="checked" @endif />
                                                                    <span class="toggle"></span>
                                                                </label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="">{{xe_trans('board::inputOutputOrder')}}</label>

                                        <div class="table-responsive item-setting">
                                            <table class="table table-sortable">
                                                <colgroup>
                                                    <col width="200">
                                                    <col>
                                                    <col>
                                                </colgroup>
                                                <tbody>
                                                @foreach($sortFormColumns as $columnName)
                                                    <tr>
                                                        <td>
                                                            <button class="btn handler"><i class="xi-drag-vertical"></i></button>
                                                            <em class="item-title">{{ $columnName }}</em>
                                                        </td>
                                                        <td>
                                                            <span class="item-subtext">{{ $columnName }}</span>
                                                        </td>
                                                        <td>
                                                            <input type="hidden" name="formColumns[]" value="{{ $columnName }}" />
                                                            {{--<div class="xe-btn-toggle pull-right">--}}
                                                            {{--<label>--}}
                                                            {{--<span class="sr-only">toggle</span>--}}
                                                            {{--<input type="checkbox" name="formColumns[]" value="{{ $columnName }}" @if(in_array($columnName, $config->get('formColumns'))) checked="checked" @endif @if(in_array($columnName, ['title', 'content']))  @endif />--}}
                                                            {{--<span class="toggle"></span>--}}
                                                            {{--</label>--}}
                                                            {{--</div>--}}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary"><i class="xi-download"></i>{{xe_trans('xe::save')}}</button>
                        </div>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

<script type="text/javascript">
    $(function() {
        // sortable 한 table 구현해야 함
        $(".table-sortable tbody").sortable({
            handle: '.handler',
            cancel: '',
            update: function( event, ui ) {
            },
            start: function(e, ui) {
                ui.placeholder.height(ui.helper.outerHeight());
                ui.placeholder.css("display", "table-row");
                ui.helper.css("display", "table");
            },
            stop: function(e, ui) {
                $(ui.item.context).css("display", "table-row");
            }
        }).disableSelection();

        $(".table-sortable tbody").closest('form').bind('submit', function(event) {
            var list = [];

            $('[name="listColumns[]"]').each(function() {
                list.push($(this).val());
            });

            $('[name="sortListColumns[]"]').remove();
            for (var i in list) {
                $(this).append($('<input type="hidden" name="sortListColumns[]">').val(list[i]));
            }

            list = [];
            $('[name="formColumns[]"]').each(function() {
                list.push($(this).val());
            });

            $('[name="sortFormColumns[]"]').remove();
            for (var i in list) {
                $(this).append($('<input type="hidden" name="sortFormColumns[]">').val(list[i]));
            }
        });
    });
</script>

<style>
    .panel-warning {
        padding: 20px;
        margin: 20px;
        background: #ffba00;
        font-weight: 700;
        border-radius: 5px;
    }
    .panel-warning-list {
        padding-left: 10px;
        list-style-type: none;
    }
</style>

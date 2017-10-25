@section('page_title')
    <h2>{{xe_trans('board::boardDetailConfigures')}}</h2>
    @endsection

    @section('page_description')@endsection

            <!-- Main content -->
    <div class="row">
        <div class="col-sm-12">
            <div class="panel-group">
                <div class="panel">
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
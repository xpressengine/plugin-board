@section('page_title')
    <h2>Share</h2>
@endsection

{{ XeFrontend::css('https://gitcdn.github.io/bootstrap-toggle/2.2.0/css/bootstrap-toggle.min.css')->before('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css')->load() }}
{{ XeFrontend::js('https://gitcdn.github.io/bootstrap-toggle/2.2.0/js/bootstrap-toggle.min.js')->appendTo('head')->before('https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js')->load() }}
{{ XeFrontend::js('/assets/vendor/jqueryui/jquery-ui.js')->appendTo('head')->load() }}

<div class="row">
    <div class="col-sm-12">
        <div class="panel-group">
            <div class="panel">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h3 class="panel-title">Share</h3>
                    </div>
                </div>

                <form class="form-horizontal" method="post" action="{{route('manage.board.share.update')}}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="panel-body">
                    <div class="table-responsive item-setting">
                        <table class="table table-sortable">
                            <colgroup>
                                <col width="200">
                                <col>
                                <col>
                            </colgroup>
                            <tbody>
                            @foreach($items as $key => $data)
                                <tr>
                                    <td>
                                        <button class="btn handler"><i class="xi-bullet-point"></i></button>
                                        <em class="item-title">{{xe_trans($data['label'])}}</em>
                                    </td>
                                    <td>
                                    </td>
                                    <td>
                                        <div class="xe-btn-toggle pull-right">
                                            <label>
                                                <span class="sr-only">toggle</span>
                                                <input type="checkbox" name="items[]" value="{{ $key }}" @if($data['activated']) checked="checked" @endif />
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

                <div class="panel-footer">
                    <div class="pull-right">
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
            }
        }).disableSelection();
    });
</script>

{{ XeFrontend::rule('board', $rules) }}

<div class="board_write">
    <form method="post" id="board_form" class="__board_form" action="{{ $urlHandler->get('store') }}" enctype="multipart/form-data" data-rule="board" data-rule-alert-type="toast">
    <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
    <input type="hidden" name="parentId" value="{{$parentId}}" />
    <input type="hidden" name="head" value="{{$head}}" />
    <input type="hidden" name="queryString" value="{{ http_build_query(Input::except('parentId')) }}" />

    <div class="write_header">
        <div class="write_category form-group">
            @if($config->get('category') == true)
            <input type="hidden" name="categoryItemId" value="" placeholder="{{ xe_trans('xe::category') }}"/>
            <a href="#" class="bd_select __xe_select_box_show">{{ xe_trans('xe::category') }}</a>
            <div class="bd_select_list" data-name="categoryItemId">
                <ul>
                    <li><a href="#" data-value="">{{xe_trans('xe::category')}}</a></li>
                    @foreach ($categoryItems as $item)
                        <li><a href="#" data-value="{{$item->id}}">{{xe_trans($item->word)}}</a></li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <div class="write_title">
            {!! uio('titleWithSlug', [
            'title' => Input::old('title'),
            'slug' => '',
            'titleClassName' => 'bd_input',
            'config' => $config
            ]) !!}
        </div>
    </div>

    <div class="write_body">
        <div class="write_form_editor __xe_content __xe_temp_container">
            {!! uio('editor', [
              'content' => Input::old('content'),
              'editorConfig' => [
                'fileUpload' => [
                  'upload_url' => $urlHandler->get('upload'),
                  'source_url' => $urlHandler->get('source'),
                  'download_url' => $urlHandler->get('download'),
                ],
                'suggestion' => [
                  'hashtag_api' => $urlHandler->get('hashTag'),
                  'mention_api' => $urlHandler->get('mention'),
                ],
              ]
            ]) !!}
        </div>
    </div>

    <div class="write_footer">

        @foreach ($configHandler->formColumns($instanceId) as $columnName)
        @if ($columnName != 'category')
            <div class="__xe_{{$columnName}} __xe_section">
                {!! dfCreate($config->get('documentGroup'), $columnName, Input::all()) !!}
            </div>
        @endif
        @endforeach

        @if (Auth::guest() === true)
        <div class="write_form_input">
            <input type="text" name="writer" class="bd_input" placeholder="{{ xe_trans('xe::writer') }}" title="{{ xe_trans('xe::writer') }}" value="{{ Input::old('writer') }}">
            <input type="password" name="certifyKey" class="bd_input" placeholder="{{ xe_trans('xe::password') }}" title="{{ xe_trans('xe::password') }}">
            <input type="email" name="email" class="bd_input v2" placeholder="{{ xe_trans('xe::email') }}" title="{{ xe_trans('xe::email') }}" value="{{ Input::old('email') }}">
        </div>
        @endif

        <div class="write_form_option">
            @if($isManager === true)
            <input type="checkbox" id="notice" name="status" value="{{\Xpressengine\Document\Models\Document::STATUS_NOTICE}}" /><label for="notice">{{xe_trans('xe::notice')}}</label>
            @endif
        </div>
        <div class="write_form_btn">
            @if (Auth::guest() !== true)
            {{--<a href="#" class="bd_btn btn_temp_save __xe_temp_btn_load">{{ xe_trans('comment_service::tempLoad') }}</a>--}}
            {{--<a href="#" class="bd_btn btn_preview __xe_temp_btn_save">{{ xe_trans('comment_service::tempSave') }}</a>--}}
            @endif

            <a href="#" class="bd_btn btn_preview __xe_btn_preview">{{ xe_trans('xe::preview') }}</a>
            <a href="#" class="bd_btn btn_submit __xe_btn_submit">{{ xe_trans('xe::submit') }}</a>

            <a href="{{ $urlHandler->get('index', Input::except('id', 'parentId')) }}" class="bd_btn btn_cancel"><i class="xi-undo"></i> {{ xe_trans('xe::back') }}</a>
        </div>

        <!-- 게시판 addon -->
    </div>
    </form>
</div>

{{ XeFrontend::css('/assets/core/common/css/temporary.css')->load() }}
{{ XeFrontend::js('assets/core/common/js/temporary.js')->appendTo('body')->load() }}

<script>
    {{--$(function() {--}}
        {{--var form = $('#board_form');--}}
        {{--var temporary = $('textarea', form).temporary({--}}
            {{--key: 'document|{{$instanceId}}',--}}
            {{--btnLoad: $('.__xe_temp_btn_load', form),--}}
            {{--btnSave: $('.__xe_temp_btn_save', form),--}}
            {{--container: $('.__xe_temp_container', form),--}}
            {{--withForm: true,--}}
            {{--callback: function (data) {--}}
                {{--console.log(data);--}}
                {{--if (xe3CkEditors['xeContentEditor']) {--}}
                    {{--xe3CkEditors['xeContentEditor'].setData($('textarea', this.dom).val());--}}
                {{--}--}}
            {{--}--}}
        {{--});--}}
    {{--});--}}


    {{--$(function() {--}}
        {{--$('.board-container .__xe_btn_temporary').bind('click', function() {--}}
            {{--var f = $('#board_form').clone();--}}

            {{--var status = $('<input>').attr('name', 'status').attr('type', 'hidden').val('temp');--}}
            {{--f.append(status);--}}
            {{--f.attr('action', '{{ $urlHandler->get('temporary') }}');--}}
            {{--f.submit();--}}
        {{--});--}}


        {{--new Temporary($('#board_form [name="content"]'), 'board|{{$instanceId}}}', function (data) {--}}
            {{--form.editorSync();--}}
        {{--}, true);--}}
    {{--});--}}
</script>

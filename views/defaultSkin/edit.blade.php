<div class="board_write">
    <form method="post" id="board_form" class="__board_form" action="{{ $urlHandler->get('update') }}" enctype="multipart/form-data" data-rule="board">
        <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
        <input type="hidden" name="id" value="{{$item->id}}" />
        <input type="hidden" name="queryString" value="{{ http_build_query(Input::except('parentId')) }}" />

        <div class="write_header">
            @if(DynamicField::has($config->get('documentGroup'), 'category'))
            <div class="write_category">
                {!! DynamicField::get($config->get('documentGroup'), 'category')->getSkin()->edit($item->getAttributes()) !!}
            </div>
            @endif
            <div class="write_title">
                {!! uio('titleWithSlug', [
                'id' => $item->id,
                'title' => Input::old('title', $item->title),
                'slug' => $item->getSlug() === null ? '' : $item->getSlug()->slug,
                'titleClassName' => 'bd_input',
                'config' => $config
                ]) !!}
            </div>
        </div>

        <div class="write_body">
            <div class="write_form_editor __xe_content">
                {!! uio('editor', [
                  'content' => Input::old('content', $item->content),
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

            @foreach ($formColumns as $columnName)
                @if (($fieldType = DynamicField::get($config->get('documentGroup'), $columnName)) != null && $columnName != 'category')
                    <div class="__xe_{{$columnName}} __xe_section">
                        {!! $fieldType->getSkin()->edit($item->getAttributes()) !!}
                    </div>
                @endif
            @endforeach

            @if ($item->userType == \Xpressengine\Document\DocumentEntity::USER_TYPE_GUEST)
                <div class="write_form_input">
                    <input type="text" name="writer" class="bd_input" placeholder="{{ xe_trans('xe::writer') }}" title="{{ xe_trans('xe::writer') }}" value="{{ Input::old('writer', $item->writer) }}">
                    <input type="password" name="certifyKey" class="bd_input" placeholder="{{ xe_trans('xe::password') }}" title="{{ xe_trans('xe::password') }}">
                    <input type="email" name="email" class="bd_input v2" placeholder="{{ xe_trans('xe::email') }}" title="{{ xe_trans('xe::email') }}" value="{{ Input::old('email', $item->email) }}">
                </div>
            @endif

            <div class="write_form_option">
                @if($isManager === true)
                <input type="checkbox" id="notice" name="notice" value="{{\Xpressengine\Document\DocumentEntity::STATUS_NOTICE}}" @if($item->status == \Xpressengine\Document\DocumentEntity::STATUS_NOTICE) checked="checked" @endif /><label for="notice">{{xe_trans('xe::notice')}}</label>
                @endif
            </div>
            <div class="write_form_btn">
                <a href="#" class="bd_btn btn_preview __xe_btn_preview">{{ xe_trans('xe::preview') }}</a>
                <a href="#" class="bd_btn btn_submit __xe_btn_submit">{{ xe_trans('xe::submit') }}</a>
                <a href="{{ $urlHandler->get('index', Input::except('id', 'parentId')) }}" class="bd_btn btn_cancel"><i class="xi-undo"></i> {{ xe_trans('xe::back') }}</a>
            </div>

        </div>
    </form>
</div>

{{ Frontend::js('assets/vendor/core/js/temporary.js')->appendTo('body')->load() }}
{{--<script>--}}
    {{--$(function() {--}}
        {{--$('.board-container .__xe_btn_temporary').bind('click', function() {--}}
            {{--var f = $('#board_form').clone();--}}

            {{--var status = $('<input>').attr('name', 'status').attr('type', 'hidden').val('temp');--}}
            {{--f.append(status);--}}
            {{--f.attr('action', '{{ $urlHandler->get('temporary') }}');--}}
            {{--f.submit();--}}
        {{--});--}}


        {{--new Temporary($('#board_form [name="content"]'), 'board|{{$boardId}}}', function (data) {--}}
            {{--form.editorSync();--}}
        {{--}, true);--}}
    {{--});--}}
{{--</script>--}}

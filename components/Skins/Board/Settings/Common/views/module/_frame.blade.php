{{ XeFrontend::js('plugins/board/assets/js/managerSkin.js')->load() }}

{{-- $_active 는 SettingsSkin 에서 처리됨 --}}
<ul class="nav nav-tabs">
    <li @if($_active == 'config') class="active" @endif><a href="{{$urlHandler->managerUrl('config', ['boardId' => $boardId])}}">{{xe_trans('board::boardDetailConfigures')}}</a></li>
    <li @if($_active == 'permission') class="active" @endif><a href="{{$urlHandler->managerUrl('permission', ['boardId' => $boardId])}}">{{xe_trans('xe::permission')}}</a></li>
    <li @if($_active == 'skin') class="active" @endif><a href="{{$urlHandler->managerUrl('skin', ['boardId' => $boardId])}}">{{xe_trans('xe::skin')}}</a></li>
    <li @if($_active == 'editor') class="active" @endif><a href="{{$urlHandler->managerUrl('editor', ['boardId' => $boardId])}}">{{xe_trans('xe::editor')}}</a></li>
    <li @if($_active == 'columns') class="active" @endif><a href="{{$urlHandler->managerUrl('columns', ['boardId' => $boardId])}}">{{xe_trans('board::outputOrder')}}</a></li>
    <li @if($_active == 'dynamicField') class="active" @endif><a href="{{$urlHandler->managerUrl('dynamicField', ['boardId' => $boardId])}}">{{xe_trans('xe::dynamicField')}}</a></li>
    <li @if($_active == 'toggleMenu') class="active" @endif><a href="{{$urlHandler->managerUrl('toggleMenu', ['boardId' => $boardId])}}">{{xe_trans('xe::toggleMenu')}}</a></li>
</ul>

{!! $content !!}

{{ XeFrontend::js('assets/vendor/jqueryui/jquery-ui.min.js')->load() }}
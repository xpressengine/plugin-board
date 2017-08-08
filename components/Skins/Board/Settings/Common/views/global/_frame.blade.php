{{ XeFrontend::js('plugins/board/assets/js/managerSkin.js')->load() }}

{{-- $_active 는 SettingsSkin 에서 처리됨 --}}
<ul class="nav nav-tabs">
    <li @if($_active == 'config') class="active" @endif><a href="{{$urlHandler->managerUrl('global.config')}}">{{xe_trans('board::boardDetailConfigures')}}</a></li>
    <li @if($_active == 'permission') class="active" @endif><a href="{{$urlHandler->managerUrl('global.permission')}}">{{xe_trans('xe::permission')}}</a></li>
    <li @if($_active == 'toggleMenu') class="active" @endif><a href="{{$urlHandler->managerUrl('global.toggleMenu')}}">{{xe_trans('xe::toggleMenu')}}</a></li>
</ul>

{!! $content !!}
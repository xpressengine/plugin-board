@if ($scriptInit)
{{ XeFrontend::js('plugins/board/assets/js/build/BoardTags.js')->appendTo('body')->load() }}
@endif
<div id="{{$id}}" class="{{$class}}" data-placeholder="{{$placeholder}}" data-url="{{$url}}" data-tags="{{$strTags}}"></div>

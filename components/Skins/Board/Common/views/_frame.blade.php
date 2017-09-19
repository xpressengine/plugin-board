{{ XeFrontend::js('plugins/board/assets/js/build/board.js')->appendTo('body')->load() }}
{{ XeFrontend::css('plugins/board/assets/css/board.css')->load() }}

<style>
    .bd_function .bd_like.voted{color:#FE381E}
</style>

<!-- BOARD -->
<div class="board">
    @section('content')
        {!! isset($content) ? $content : '' !!}
    @show
</div>
<!-- /BOARD -->

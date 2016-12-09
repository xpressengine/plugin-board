{{ XeFrontend::css('plugins/board/assets/defaultSkin/css/board.css')->load() }}
{{ XeFrontend::js('plugins/board/assets/build/defaultSkin.js')->appendTo('body')->load() }}

<style>
    .bd_function .bd_like.voted{color:#FE381E}
</style>

<script type="text/javascript">
    var Common = function() {
        var _data = {
            user: {
                isManager: true
            },
            apis: {
                create: '',
                delete: '',
                update: '',
                list: '',
                view: ''
            }
        };

        return {
            get: function (key) {
                return _data[key];
            }
        };
    }();
</script>

<!-- BOARD -->
<div id="boardContainer" class="board">{{--@yield('content', isset($content) ? $content : '')--}}</div>
<!-- /BOARD -->
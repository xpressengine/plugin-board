{{ XeFrontend::css('plugins/board/assets/build/defaultSkin.css')->load() }}
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
                list: 'http://localhost:8088/board1/api/articles',
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
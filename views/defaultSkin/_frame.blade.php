{{ XeFrontend::css('plugins/board/assets/build/defaultSkin.css')->load() }}
{{ XeFrontend::js('plugins/board/assets/build/defaultSkin.js')->appendTo('body')->load() }}

<style>
    .bd_function .bd_like.voted{color:#FE381E}
</style>

<script type="text/javascript">
    var Common = (function() {
        var _data = {
            user: {
                isManager: true
            },
            apis: {
                create: '{{sprintf('/%s/api/create', $instanceConfig->getUrl())}}',
                store: '{{sprintf('/%s/api/store', $instanceConfig->getUrl())}}',
                delete: '{{sprintf('/%s/api/update/[id]', $instanceConfig->getUrl())}}',
                edit: '{{sprintf('/%s/api/edit/[id]', $instanceConfig->getUrl())}}',
                update: '{{sprintf('/%s/api/update/[id]', $instanceConfig->getUrl())}}',
                index: '{{sprintf('/%s/api/articles', $instanceConfig->getUrl())}}',
                view: '{{sprintf('/%s/api/articles/[id]', $instanceConfig->getUrl())}}',
                favorite: '{{sprintf('/%s/api/favorit/[id]', $instanceConfig->getUrl())}}'
            },
            links: {
                settings: ''
            }
        };

        return {
            get: function (key) {
                return _data[key];
            }
        };
    })();
</script>

<!-- BOARD -->
<div id="boardContainer" class="board">{{--@yield('content', isset($content) ? $content : '')--}}</div>
<!-- /BOARD -->
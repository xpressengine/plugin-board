{{ XeFrontend::js('plugins/board/assets/js/board.js')->appendTo('body')->load() }}
{{ XeFrontend::css('plugins/board/assets/css/board.css')->load() }}

@if($isManager === true)
<script type="text/javascript">
//$(function($) {
//    // copy documents
//    $('.__xe_copy').on('click', 'li', function(event) {
//        if (hasChecked() === false) {
//            return;
//        }
//
//        var instanceId = $(event.target).data('value');
//        $('.__xe_copy').find('[name="copyTo"]').val(instanceId);
//        $('.__xe_copy').find('a.__xe_btn_submit').show();
//
//    }).on('click', 'a.__xe_btn_submit', function(event) {
//        event.preventDefault();
//
//        var ids = getCheckedIds(),
//            instanceId = $('.__xe_copy').find('[name="copyTo"]').val();
//        $.ajax({
//            type: 'post',
//            dataType: 'json',
//            data: {id:ids, instanceId: instanceId},
//            url: $(event.target).prop('href'),
//            success: function(response) {
//                document.location.reload();
//            }
//        });
//    });
//
//    $('.__xe_move').on('click', 'li', function(event) {
//        if (hasChecked() === false) {
//            return;
//        }
//
//        var instanceId = $(event.target).data('value');
//        $('.__xe_move').find('[name="moveTo"]').val(instanceId);
//        $('.__xe_move').find('a.__xe_btn_submit').show();
//
//    }).on('click', 'a.__xe_btn_submit', function(event) {
//        event.preventDefault();
//
//        var ids = getCheckedIds(),
//            instanceId = $('.__xe_move').find('[name="moveTo"]').val();
//        $.ajax({
//            type: 'post',
//            dataType: 'json',
//            data: {id:ids, instanceId: instanceId},
//            url: $(event.target).prop('href'),
//            success: function(response) {
//                document.location.reload();
//            }
//        });
//    });
//
//    $('.__xe_to_trash').on('click', 'a:first', function(event) {
//        event.preventDefault();
//        if (hasChecked() === false) {
//            return;
//        }
//        var instanceId = $(event.target).data('value');
//        $('.__xe_to_trash').find('a.__xe_btn_submit').show();
//
//    }).on('click', 'a.__xe_btn_submit', function(event) {
//        event.preventDefault();
//
//        var ids = getCheckedIds();
//        $.ajax({
//            type: 'post',
//            dataType: 'json',
//            data: {id:ids},
//            url: $(event.target).prop('href'),
//            success: function(response) {
//                document.location.reload();
//            }
//        });
//    });
//
//    $('.__xe_delete').on('click', 'a:first', function(event) {
//        event.preventDefault();
//        if (hasChecked() === false) {
//            return;
//        }
//        var instanceId = $(event.target).data('value');
//        $('.__xe_delete').find('a.__xe_btn_submit').show();
//
//    }).on('click', 'a.__xe_btn_submit', function(event) {
//        event.preventDefault();
//
//        var ids = getCheckedIds();
//        $.ajax({
//            type: 'post',
//            dataType: 'json',
//            data: {id:ids},
//            url: $(event.target).prop('href'),
//            success: function(response) {
//                document.location.reload();
//            }
//        });
//    });
//
//
//    var hasChecked = function() {
//        if ($('.bd_manage_check:checked').length == 0) {
//            XE.toast('info', XE.Lang.trans('board::selectPost'));
//            return false;
//        }
//        return true;
//    };
//
//    var getCheckedIds = function() {
//        var checkedIds = [];
//        $('.bd_manage_check:checked').each(function() {
//            checkedIds.push($(this).val());
//        });
//
//        return checkedIds;
//    }
//});
</script>
@endif
<style>
    .bd_function .bd_like.voted{color:#FE381E}
</style>

<!-- BOARD -->
<div class="board">
    @yield('content', isset($content) ? $content : '')
</div>
<!-- /BOARD -->

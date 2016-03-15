$(function() {
    $('.list-option-add').bind('click', function(e) {
        $('#list_options option').each(function() {
            // move to list columns
            if ($(this).prop('selected')) {
                var newOption = $(this).clone();
                if ($('#list_selected').find('[value="'+newOption.val()+'"]').length == 0) {
                    $('#list_selected').append(newOption);
                    $(this).remove();
                }
            }
        });
    });

    $('.list-option-delete').bind('click', function(e) {
        $('#list_selected option').each(function() {
            // move to list columns
            if ($(this).prop('selected')) {
                var newOption = $(this).clone();
                if ($('#list_options').find('[value="'+newOption.val()+'"]').length == 0) {
                    $('#list_options').append(newOption);
                    $(this).remove();
                }
            }
        });
    });

    $('.list-option-up').bind('click', function(e) {
        var options = $('#list_selected option');
        var len = options.length;
        for (var i=0; i<len; i++) {
            var option = $(options[i]);
            if (option.prop('selected')) {
                option.prev().before(option);
            }
        }
    });

    $('.list-option-down').bind('click', function(e) {
        var options = $('#list_selected option');
        var len = options.length;
        for (var i=len-1; i>=0; i--) {
            var option = $(options[i]);
            if (option.prop('selected')) {
                option.next().after(option);
            }
        }
    });

    $('.form-order-up').bind('click', function(e) {
        var options = $('#form_order option');
        var len = options.length;
        for (var i=0; i<len; i++) {
            var option = $(options[i]);
            if (option.prop('selected')) {
                option.prev().before(option);
            }
        }
    });

    $('.form-order-down').bind('click', function(e) {
        var options = $('#form_order option');
        var len = options.length;
        for (var i=len-1; i>=0; i--) {
            var option = $(options[i]);
            if (option.prop('selected')) {
                option.next().after(option);
            }
        }
    });

    $('.form-category-select select').bind('change', function(e) {
        var $o = $(this);
        if ($o.val() == 'true' && $o.data('id') == '') {
            XE.ajax({
                type: 'post',
                dataType: 'json',
                url: $o.data('url'),
                success: function(data) {
                    $o.data('id', data.id);
                    $o.next('button').attr('disabled', false);
                }
            });
        } else {
            $o.next('button').attr('disabled', $o.val() == 'true' ? false : true);
        }
    });

    $('.inheritCheck').bind('click', function(e) {
        var $o = $(this),
            target = $o.data('target'),
            select = $o.data('select');
        if (target != undefined) {
            $o.closest('.form-group').find('[name="'+target+'"]').prop('disabled', $o.prop('checked'));
        } else if (select != undefined) {
            var $group = $o.closest('.form-group').find(select);
            $group.find('button').prop('disabled', $o.prop('checked'));
            $group.find('select').prop('disabled', $o.prop('checked'));
        }
    });

    $('.form-category-select button').bind('click', function(e) {
        var $o = $(this);
        window.open($o.data('href') + '/' + $o.prev('select').data('id'), '_blank');
    });
});

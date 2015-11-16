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
});
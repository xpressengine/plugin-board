$(function($) {
    $('.bd_select_list').on('click', 'a', function(event) {
        event.preventDefault();
        var $target = $(event.target),
            $select = $target.closest('.bd_select_list');

        $select.prev('.bd_select').text($target.text());
        $target.closest('form').find('[name="'+$select.data('name')+'"]').val($target.data('value'));
    });
});

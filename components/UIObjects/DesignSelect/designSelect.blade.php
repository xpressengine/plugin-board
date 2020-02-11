@php
    use Xpressengine\Plugins\Board\Components\UIObjects\DesignSelect\DesignSelectUIObject;
@endphp

@if($scriptInit === true)
    <script>
        jQuery(function($) {
            $('.__xe-dropdown-form .xe-dropdown-menu a').on('click touchstart', function(event) {
                event.preventDefault();
                var $target = $(event.target),
                    $container = $target.closest('.__xe-dropdown-form'),
                    name = $target.closest('.xe-dropdown-menu').data('name'),
                    $input = $container.find('[name="'+name+'"]');

                $input.val($target.data('value'));
                $container.find('button').text($target.text());

                // event trigger for third parties
                $input.trigger( "change" );
            });
        });
    </script>
@endif
<div class="xe-dropdown __xe-dropdown-form">
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-valid-name="{{ xe_trans($label) }}" />
    <button class="xe-btn" type="button" data-toggle="xe-dropdown" aria-expanded="false">{{ $value != $default ? xe_trans($text) : xe_trans($label) }}</button>
    <ul class="xe-dropdown-menu" data-name="{{ $name }}">
        <li @if($value == (string)$default) class="on" @endif><a href="#">{{ xe_trans($label) }}</a></li>

        {!! DesignSelectUIObject::renderList($items, $value) !!}
    </ul>
</div>

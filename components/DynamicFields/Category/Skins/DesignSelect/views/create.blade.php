@if (!$config->get('use', false))
    @include('dynamicField.userActivateLink')
@endif


{!! uio('uiobject/board@select', [
    'name' => $key['item_id'],
    'label' => xe_trans($config->get('label')),
    'value' => Request::old($key['item_id']),
    'items' => $data['selectItems'],
    'disabled' => !$config->get('use', false)
]) !!}
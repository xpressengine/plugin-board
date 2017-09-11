{!! uio('uiobject/board@select', [
    'name' => $key['itemId'],
    'label' => xe_trans($config->get('label')),
    'value' => Request::old($key['itemId']),
    'items' => $data['selectItems'],
]) !!}
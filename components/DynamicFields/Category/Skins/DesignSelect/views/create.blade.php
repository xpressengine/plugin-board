{!! uio('uiobject/board@select', [
    'name' => $key['item_id'],
    'label' => xe_trans($config->get('label')),
    'value' => Request::old($key['itemId']),
    'items' => $data['selectItems'],
]) !!}
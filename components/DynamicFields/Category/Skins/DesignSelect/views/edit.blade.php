{!! uio('uiobject/board@select', [
    'name' => $key['item_id'],
    'label' => xe_trans($config->get('label')),
    'value' => $data['item_id'] != null ? $data['item_id'] : '',
    'items' => $data['selectItems'],
]) !!}
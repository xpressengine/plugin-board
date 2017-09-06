{!! uio('uiobject/board@select', [
    'name' => $key['item_id'],
    'label' => xe_trans($config->get('label')),
    'value' => $data['categoryItem'] != null ? $data['categoryItem']->id : '',
    'items' => $data['selectItems'],
]) !!}
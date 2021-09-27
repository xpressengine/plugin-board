{!! uio('uiobject/board@select', [
    'name' => $key['item_id'],
    'label' => xe_trans($config->get('label')),
    'value' => array_get($data, 'categoryItem') ? array_get($data, 'categoryItem')->id : '',
    'items' => array_get($data, 'selectItems'),
]) !!}
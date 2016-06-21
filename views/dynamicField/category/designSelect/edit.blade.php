{!! uio('uiobject/board@select', [
    'name' => $config->get('id') . 'ItemId',
    'label' => xe_trans($config->get('label')),
    'value' => $item != null ? $item->id : '',
    'items' => $categories,
]) !!}
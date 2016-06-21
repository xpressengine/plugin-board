{!! uio('uiobject/board@select', [
    'name' => $config->get('id') . 'ItemId',
    'label' => xe_trans($config->get('label')),
    'value' => Input::old($config->get('id') . 'ItemId'),
    'items' => $categories,
]) !!}
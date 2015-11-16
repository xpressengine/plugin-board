@if ($scriptInit === true)
    <script type="text/javascript">
        XE.$(function($) {
            $(document).on('change', '.__xe_titleWithSlug [name="title"]', function() {
                var $target = $(this);
                // 글 수정일 경우 자동으로 슬러그를 수정하지 않는다.
                if ($target.data('slug') != '') {
                    return;
                }

                var $container = $target.closest('.__xe_titleWithSlug');
                $container.find('.__xe_slug_edit input').val($target.val());

                checkSlug($container);

                var $parent = $(this).parent();
                $parent.find('.__xe_slug_edit').hide();
                $parent.find('.__xe_slug_show').show();

            }).on('click', '.__xe_slug_show .edit', function(event) {
                event.preventDefault();

                var $container = $(event.target).closest('.__xe_titleWithSlug');

                $container.find('.__xe_slug_show').hide();
                $container.find('.__xe_slug_edit').show();
            }).on('click', '.__xe_slug_edit .ok', function(event) {
                event.preventDefault();

                var $container = $(event.target).closest('.__xe_titleWithSlug');

                checkSlug($container);

                $container.find('.__xe_slug_edit').hide();
                $container.find('.__xe_slug_show').show();
            }).on('click', '.__xe_slug_edit .cancel', function(event) {
                event.preventDefault();

                var $container = $(event.target).closest('.__xe_titleWithSlug');

                $container.find('.__xe_slug_edit').hide();
                $container.find('.__xe_slug_show').show();
            });

            $('.__xe_titleWithSlug').each(function() {
                var $container = $(this);

                if ($container.find('input.title').data('slug') != '') {
                    $container.find('.__xe_slug_show').show();
                }
            });

            function checkSlug($container)
            {
                var id = $container.find('[name="title"]').data('id'),
                        slug = $container.find('.__xe_slug_edit input').val();

                $.ajax({
                    url: '{{ app('xe.board.url')->get('checkSlug') }}',
                    data: {id: id, slug: slug},
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        $container.find('.__xe_slug_edit input').val(res.slug);
                        $container.find('.current-slug').text(res.slug);
                    }
                });
            }
        });

    </script>
@endif

<div class="__xe_titleWithSlug">
    <input type="text" name="{{ $titleDomName }}" class="{{$titleClassName}}" value="{{ $title }}" placeholder="{{ xe_trans('xe::title') }}" data-id="{{ $id }}" data-slug="{{ $slug }}"/>

    <div class="__xe_slug_edit" style="display:none;">
        <span class="edit-slug"><input type="text" name="{{ $slugDomName }}" value="{{ $slug }}"/></span>
        <span><a href="#" class="ok">OK</a></span>
        <span><a href="#" class="cancel">Cancel</a></span>
    </div>

    <div class="__xe_slug_show" style="display:none;">
        <span class="current-slug">{{ $slug }}</span>
        <span><a href="#" class="edit">Edit</a></span>
    </div>
</div>


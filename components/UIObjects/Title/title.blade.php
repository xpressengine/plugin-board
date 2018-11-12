@if ($scriptInit === true)
    <script>
        jQuery(function($) {
            $(document).on('change', '.__xe_titleWithSlug [name="title"]', function() {
                var $target = $(this);
                // 글 수정일 경우 자동으로 슬러그를 수정하지 않는다.
                if ($target.data('slug') != '') {
                    return;
                }

                var $container = $target.closest('.__xe_titleWithSlug');
                $container.find('.__xe_slug_edit input').val($target.val());

                hasSlug($container, function() {
                    $($container).parent().find('.__xe_slug_show').show();
                });

                var $parent = $(this).parent();
                $parent.find('.__xe_slug_edit').hide();

            }).on('click', '.__xe_slug_show .edit', function(event) {
                event.preventDefault();

                var $container = $(event.target).closest('.__xe_titleWithSlug');

                $container.find('.__xe_slug_show').hide();
                $container.find('.__xe_slug_edit').show();
            }).on('click', '.__xe_slug_edit .ok', function(event) {
                event.preventDefault();

                var $container = $(event.target).closest('.__xe_titleWithSlug');

                hasSlug($container, function() {
                    $container.find('.__xe_slug_show').show();
                });

                $container.find('.__xe_slug_edit').hide();

            }).on('click', '.__xe_slug_edit .cancel', function(event) {
                event.preventDefault();

                var $container = $(event.target).closest('.__xe_titleWithSlug');

                $container.find('.__xe_slug_edit').hide();
                $container.find('.__xe_slug_show').show();
            });

            $('.__xe_titleWithSlug').each(function() {
                var $container = $(this);
                if ($container.find('input.__xe_title').data('slug') != '') {
                    $container.find('.__xe_slug_show').show();
                }
            });

            function hasSlug($container, callback) {
                var id = $container.find('[name="title"]').data('id'),
                slug = $container.find('.__xe_slug_edit input').val();

                XE.ajax({
                    url: '{{ app('xe.board.url')->get('hasSlug') }}',
                    data: {id: id, slug: slug},
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        $container.find('.__xe_slug_edit input').val(res.slug);
                        $container.find('.current-slug').text('{{instance_route('slug', ['slug' => ''])}}/' + res.slug);

                        callback();
                    }
                });
            }
        });
    </script>
@endif

<div class="__xe_titleWithSlug">
    <input type="text" name="{{ $titleDomName }}" data-valid-name="{{ xe_trans('board::title') }}" class="xe-form-control __xe_title {{$titleClassName}}" value="{{ $title }}" placeholder="{{ xe_trans('board::enterTitle') }}" data-id="{{ $id }}" data-slug="{{ $slug }}"/>

    <div class="__xe_slug_edit" style="display:none;">
        <i class="xi-link"></i>
        <span class="edit-slug">{{instance_route('slug', ['slug' => ''])}}/<input type="text" name="{{ $slugDomName }}" value="{{ $slug }}"/></span>
        <span><button type="button" class="xe-btn xe-btn-link xe-btn-xs ok">Ok</button></span>
        <span><button type="button" class="xe-btn xe-btn-link xe-btn-xs cancel">Cancel</button></span>
    </div>

    <div class="__xe_slug_show" style="display:none;">
        <i class="xi-link"></i>
        <span class="current-slug">{{instance_route('slug', ['slug' => ''])}}/{{ $slug }}</span>
        <span><button type="button" class="xe-btn xe-btn-link xe-btn-xs edit">Edit</button></span>
    </div>
</div>

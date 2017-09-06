@section('page_title')
    <h2>Share</h2>
    @endsection

            <!-- Main content -->
    <div class="row">
        <div class="col-sm-12">
            <div class="panel-group">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="pull-left">
                            <h3 class="panel-title">{{xe_trans('xe::toggleMenu')}}</h3>
                        </div>
                    </div>
                    <div id="collapseTwo" class="panel-collapse collapse in">
                        <div class="panel-body">
                            {!! $toggleMenuSection !!}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
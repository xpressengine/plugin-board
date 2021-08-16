    @section('page_title')
        <h2>{{xe_trans($_activeMenu->getTitle())}}</h2>
    @endsection

    @section('page_description')@endsection

    <!-- Main content -->
    <div class="row">
        <div class="col-sm-12">
            <div class="panel-group">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="pull-left">
                            <h3 class="panel-title">{{xe_trans($_activeMenu->getTitle())}}</h3>
                            <small><a href="{{$urlHandler->managerUrl('global.toggleMenu')}}" target="_blank">{{xe_trans('xe::moveToParentSettingPage')}}</a></small>
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
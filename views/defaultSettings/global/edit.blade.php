@section('page_title')
    <h2>{{xe_trans('board::boardDetailConfigures')}}</h2>
@endsection

@section('page_description')
@endsection

{{ XeFrontend::js('plugins/board/assets/js/managerSkin.js')->load() }}
    <!-- Main content -->
    <div class="row">
        <div class="col-sm-12">
            <div class="panel-group">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="pull-left">
                            <h3 class="panel-title">{{xe_trans('board::boardDetailConfigures')}}</h3>
                        </div>
                        <div class="pull-right">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" class="btn-link panel-toggle pull-right"><i class="xi-angle-down"></i><i class="xi-angle-up"></i><span class="sr-only">{{xe_trans('fold')}}</span></a>
                        </div>
                    </div>
                    <form method="post" id="board_manage_form" action="{!! $urlHandler->managerUrl('global.update') !!}">
                        <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
                        <div id="collapseOne" class="panel-collapse collapse in">
                            <div class="panel-body">
                                <div class="panel">

                                    <div class="panel-heading">
                                        <div class="pull-left">
                                            <h4 class="panel-title">{{xe_trans('xe::settings')}}</h4>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::perPage')}} <small>{{xe_trans('board::perPageDescription')}} </small></label>
                                                    </div>
                                                    <input type="text" id="" name="perPage" class="form-control" value="{{Input::old('perPage', $config->get('perPage'))}}"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::newArticleTime')}} <small>{{xe_trans('board::newArticleTimeDescription')}} </small></label>
                                                    </div>
                                                    <input type="text" id="" name="newTime" class="form-control" value="{{Input::old('newTime', $config->get('newTime'))}}"/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group form-category-select">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('xe::category')}}</label>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-9">
                                                            <select id="" name="category" class="form-control" data-id="{{ $config->get('categoryId') }}" data-url="{{route('manage.board.board.storeCategory', ['boardId' => $config->get('boardId')])}}">
                                                                <option value="true" {!! $config->get('category') == true ? 'selected="selected"' : '' !!} >Use</option>
                                                                <option value="false" {!! $config->get('category') == false ? 'selected="selected"' : '' !!} >Disuse</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <button type="button" class="btn btn-default pull-right" data-href="{{ route('manage.category.show', ['id' => '']) }}" @if($config->get('category') === false) disabled="disabled" @endif>{{xe_trans('xe::categoryManage')}}</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('xe::comment')}} </label>
                                                    </div>
                                                    <select id="" name="comment" class="form-control">
                                                        <option value="true" {!! $config->get('comment') == true ? 'selected="selected"' : '' !!} >Use</option>
                                                        <option value="false" {!! $config->get('comment') == false ? 'selected="selected"' : '' !!} >Disuse</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('xe::recommend')}} </label>
                                                    </div>
                                                    <select id="" name="assent" class="form-control">
                                                        <option value="true" {!! $config->get('assent') == true ? 'selected="selected"' : '' !!} >Use</option>
                                                        <option value="false" {!! $config->get('assent') == false ? 'selected="selected"' : '' !!} >Disuse</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('xe::discommend')}} </label>
                                                    </div>
                                                    <select id="" name="dissent" class="form-control">
                                                        <option value="true" {!! $config->get('dissent') == true ? 'selected="selected"' : '' !!} >Use</option>
                                                        <option value="false" {!! $config->get('dissent') == false ? 'selected="selected"' : '' !!} >Disuse</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::anonymityUse')}} </label>
                                                    </div>
                                                    <select id="" name="anonymity" class="form-control">
                                                        <option value="true" {!! $config->get('anonymity') == true ? 'selected="selected"' : '' !!} >Use</option>
                                                        <option value="false" {!! $config->get('anonymity') == false ? 'selected="selected"' : '' !!} >Disuse</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::anonymityName')}} <small>{{xe_trans('board::anonymityDescription')}} </small></label>
                                                    </div>
                                                    <input type="text" name="anonymityName" class="form-control" value="{{ Input::old('anonymityName', $config->get('anonymityName')) }}" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::adminEmail')}} <small>{{xe_trans('board::adminEmailDescription')}} </small></label>
                                                    </div>
                                                    <input type="text" name="managerEmail" class="form-control" value="{{ Input::old('managerEmail', $config->get('managerEmail')) }}" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Permission -->
                                @foreach ($perms as $perm)
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="">{{ $perm['title'] }} {{xe_trans('xe::permission')}}</label>
                                                <div class="well">
                                                    {!! uio('permission', $perm) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="panel-footer">
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary"><i class="xi-download"></i>{{xe_trans('xe::save')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

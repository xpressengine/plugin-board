@inject('anonymityHandler', 'Xpressengine\Plugins\Board\AnonymityHandler')

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
                        <small><a href="{{$urlHandler->managerUrl('global.reply')}}" target="_blank">{{xe_trans('xe::moveToParentSettingPage')}}</a></small>
                    </div>
                </div>

                <form method="post" id="board_manage_form" action="{!! $urlHandler->managerUrl('reply.update', ['boardId' => $boardId]) !!}">
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
                                                    <label>
                                                        {{xe_trans('board::protectPost')}}
                                                        <small>{{xe_trans('board::protectPostReplies')}}</small>
                                                    </label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="protectUpdated" @if($config->getPure('protectUpdated') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="protectUpdated" class="form-control" @if($config->getPure('protectUpdated') === null) disabled="disabled" @endif>
                                                    <option value="true" {!! $config->get('protectUpdated', false) == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    <option value="false" {!! $config->get('protectUpdated', false) == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>
                                                        {{xe_trans('board::protectPost')}}
                                                        <small>{{xe_trans('board::protectPostReplies')}}</small>
                                                    </label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="protectDeleted" @if($config->getPure('protectDeleted') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="protectDeleted" class="form-control" @if($config->getPure('protectDeleted') === null) disabled="disabled" @endif>
                                                    <option value="true" {!! $config->get('protectDeleted', false) == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    <option value="false" {!! $config->get('protectDeleted', false) == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>
                                                        {{ xe_trans('board::blockAuthorSelf') }}
                                                        <small>{{ xe_trans('board::blockAuthorSelfDescription') }}</small>
                                                    </label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="blockAuthorSelf" @if($config->getPure('blockAuthorSelf') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="blockAuthorSelf" class="form-control" @if($config->getPure('blockAuthorSelf') === null) disabled="disabled" @endif>
                                                    <option value="true" {!! $config->get('blockAuthorSelf', false) == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    <option value="false" {!! $config->get('blockAuthorSelf', false) == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>
                                                        {{ xe_trans('board::limitedOneTime') }}
                                                    </label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="limitedOneTime" @if($config->getPure('limitedOneTime') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="limitedOneTime" class="form-control" @if($config->getPure('limitedOneTime') === null) disabled="disabled" @endif>
                                                    <option value="true" {!! $config->get('limitedOneTime', false) == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    <option value="false" {!! $config->get('limitedOneTime', false) == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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


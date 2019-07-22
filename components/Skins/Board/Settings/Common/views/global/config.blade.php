@section('page_title')
    <h2>{{xe_trans('board::boardDetailConfigures')}}</h2>
    @endsection

    @section('page_description')
    @endsection

            <!-- Main content -->
    <div class="row">
        <div class="col-sm-12">
            <div class="panel-group">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="pull-left">
                            <h3 class="panel-title">{{xe_trans('board::boardDetailConfigures')}}</h3>
                        </div>
                    </div>
                    <form method="post" id="board_manage_form" action="{!! $urlHandler->managerUrl('global.config.update') !!}">
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
                                                    <input type="text" id="" name="perPage" class="form-control" value="{{Request::old('perPage', $config->get('perPage'))}}"/>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::newArticleTime')}} <small>{{xe_trans('board::newArticleTimeDescription')}} </small></label>
                                                    </div>
                                                    <input type="text" id="" name="newTime" class="form-control" value="{{Request::old('newTime', $config->get('newTime'))}}"/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                {{--<div class="form-group form-category-select">--}}
                                                {{--<div class="clearfix">--}}
                                                {{--<label>{{xe_trans('xe::category')}}</label>--}}
                                                {{--</div>--}}
                                                {{--<div class="row">--}}
                                                {{--<div class="col-sm-9">--}}
                                                {{--<select id="" name="category" class="form-control" data-id="{{ $config->get('categoryId') }}" data-board-id="" data-url="{{route('manage.board.board.storeCategory')}}">--}}
                                                {{--<option value="true" {!! $config->get('category') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>--}}
                                                {{--<option value="false" {!! $config->get('category') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>--}}
                                                {{--</select>--}}
                                                {{--</div>--}}
                                                {{--<div class="col-sm-3">--}}
                                                {{--<button type="button" class="btn btn-default pull-right" data-href="{{ route('manage.category.show', ['id' => '']) }}" @if($config->get('category') === false) disabled="disabled" @endif>{{xe_trans('xe::categoryManage')}}</button>--}}
                                                {{--</div>--}}
                                                {{--</div>--}}
                                                {{--</div>--}}
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('xe::comment')}} </label>
                                                    </div>
                                                    <select id="" name="comment" class="form-control">
                                                        <option value="true" {!! $config->get('comment') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                        <option value="false" {!! $config->get('comment') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
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
                                                        <option value="true" {!! $config->get('assent') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                        <option value="false" {!! $config->get('assent') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('xe::discommend')}} </label>
                                                    </div>
                                                    <select id="" name="dissent" class="form-control">
                                                        <option value="true" {!! $config->get('dissent') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                        <option value="false" {!! $config->get('dissent') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::anonymityUse')}} <small>{{ xe_trans('board::anonymityUseDescription') }}</small></label>
                                                    </div>
                                                    <select id="" name="anonymity" class="form-control">
                                                        <option value="true" {!! $config->get('anonymity') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                        <option value="false" {!! $config->get('anonymity') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::anonymityName')}} <small>{{xe_trans('board::anonymityNameDescription')}} </small></label>
                                                    </div>
                                                    <input type="text" name="anonymityName" class="form-control" value="{{ Request::old('anonymityName', $config->get('anonymityName')) }}" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('xe::orderType')}} </label>
                                                    </div>
                                                    <select id="" name="orderType" class="form-control">
                                                        <option value="">{{xe_trans('xe::select')}}</option>
                                                        @foreach ($handler->getOrders() as $value)
                                                            <option value="{{$value['value']}}" {!! $config->get('orderType') == $value['value'] ? 'selected="selected"' : '' !!} >{{xe_trans($value['text'])}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::noticeInList')}} </label>
                                                    </div>
                                                    <select id="" name="noticeInList" class="form-control">
                                                        <option value="false" {!! $config->get('noticeInList') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                        <option value="true" {!! $config->get('noticeInList') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::adminEmail')}} <small>{{xe_trans('board::adminEmailDescription')}} </small></label>
                                                    </div>
                                                    <input type="text" name="managerEmail" class="form-control" value="{{ Request::old('managerEmail', $config->get('managerEmail')) }}" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::newCommentNotice')}} <small>{{xe_trans('board::newCommentNoticeDescription')}}</small></label>
                                                    </div>
                                                    <select id="" name="newCommentNotice" class="form-control">
                                                        <option value="true" {!! $config->get('newCommentNotice') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                        <option value="false" {!! $config->get('newCommentNotice') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::useCreateCaptcha')}} </label>
                                                    </div>
                                                    <select id="" name="useCaptcha" class="form-control">
                                                        <option value="true" {!! $config->get('useCaptcha') == true ? 'selected="selected"' : '' !!} @if ($captcha->available() !== true) disabled @endif>{{xe_trans('xe::use')}}</option>
                                                        <option value="false" {!! $config->get('useCaptcha') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                    </select>

                                                    @if($captcha->available() !== true)
                                                        <div class="alert alert-warning" role="alert">
                                                            {!! xe_trans('board::masAlertCaptcha') !!}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::useTag')}} </label>
                                                    </div>
                                                    <select id="" name="useTag" class="form-control">
                                                        <option value="true" {!! $config->get('useTag') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                        <option value="false" {!! $config->get('useTag') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::urlType')}} </label>
                                                    </div>
                                                    <select id="" name="urlType" class="form-control">
                                                        <option value="documentId" {!! $config->get('urlType') == 'documentId' ? 'selected="selected"' : '' !!} >{{xe_trans('board::documentId')}}</option>
                                                        <option value="slug" {!! $config->get('urlType') == 'slug' ? 'selected="selected"' : '' !!} >{{xe_trans('board::slug')}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::useDeleteToTrash')}} <small>{{xe_trans('board::useDeleteToTrashDescription')}}</small></label>
                                                    </div>
                                                    <select id="" name="deleteToTrash" class="form-control">
                                                        <option value="true" {!! $config->get('deleteToTrash') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                        <option value="false" {!! $config->get('deleteToTrash') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::secretPost')}} </label>
                                                    </div>
                                                    <select id="" name="secretPost" class="form-control">
                                                        <option value="true" {!! $config->get('secretPost') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                        <option value="false" {!! $config->get('secretPost') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="clearfix">
                                                        <label>{{xe_trans('board::useApprove')}} </label>
                                                    </div>
                                                    <select id="" name="useApprove" class="form-control">
                                                        <option value="true" {!! $config->get('useApprove') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                        <option value="false" {!! $config->get('useApprove') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
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

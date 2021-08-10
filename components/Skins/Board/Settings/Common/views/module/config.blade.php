@inject('anonymityHandler', 'Xpressengine\Plugins\Board\AnonymityHandler')

@section('page_title')
    <h2>{{xe_trans('board::boardDetailConfigures')}}</h2>
@endsection

@section('page_description')@endsection

<!-- Main content -->
    <div class="row">
        <div class="col-sm-12">
            <div class="panel-group">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="pull-left">
                            <h3 class="panel-title">{{xe_trans('board::boardDetailConfigures')}}</h3>
                            <small><a href="{{$urlHandler->managerUrl('global.config')}}" target="_blank">{{xe_trans('xe::moveToParentSettingPage')}}</a></small>
                        </div>
                    </div>
                    <form method="post" id="board_manage_form" action="{!! $urlHandler->managerUrl('config.update', ['boardId' => $boardId]) !!}">
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
                                                    <label>{{xe_trans('board::boardName')}} </label>
                                                </div>
                                                {!! uio('langText', ['placeholder'=>'', 'langKey'=>Request::old('boardName', $config->get('boardName')), 'name'=>'boardName']) !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::perPage')}} <small>{{xe_trans('board::perPageDescription')}} </small></label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="perPage" @if($config->getPure('perPage') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <input type="text" id="" name="perPage" class="form-control" value="{{Request::old('perPage', $config->get('perPage'))}}" @if($config->getPure('perPage') === null) disabled="disabled" @endif/>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::newArticleTime')}} <small>{{xe_trans('board::newArticleTimeDescription')}} </small></label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="newTime" @if($config->getPure('newTime') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <input type="text" id="" name="newTime" class="form-control" value="{{Request::old('newTime', $config->get('newTime'))}}" @if($config->getPure('newTime') === null) disabled="disabled" @endif/>
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
                                                        <select id="" name="category" class="form-control" data-id="{{ $config->get('categoryId') }}" data-board-id="{{$config->get('boardId')}}" data-url="{{$urlHandler->managerUrl('storeCategory')}}">
                                                            <option value="true" {!! $config->get('category') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                            <option value="false" {!! $config->get('category') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <button type="button" class="btn btn-default pull-right" data-href="{{ route('manage.category.show', ['id' => '']) }}" @if($config->get('category') == false) disabled="disabled" @endif>{{xe_trans('xe::categoryManage')}}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('xe::comment')}} </label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="comment" @if($config->getPure('comment') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-9">
                                                        <select id="" name="comment" class="form-control" @if($config->getPure('comment') === null) disabled="disabled" @endif>
                                                            <option value="true" {!! $config->get('comment') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                            <option value="false" {!! $config->get('comment') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <a href="{{route('manage.comment.setting', ['targetInstanceId' => $config->get('boardId')])}}" class="btn">{{xe_trans('xe::settings')}}</a>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::titleHead')}} <small>{{xe_trans('board::titleHeadDescription')}} </small></label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="useTitleHead" @if($config->getPure('useTitleHead') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="useTitleHead" class="form-control" @if($config->getPure('useTitleHead') === null) disabled="disabled" @endif>
                                                    <option value="true" {!! $config->get('useTitleHead') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    <option value="false" {!! $config->get('useTitleHead') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::titleHeadItem')}} <small>{{xe_trans('board::titleHeadItemDescription')}} </small></label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="titleHeadItem" @if($config->getPure('titleHeadItem') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <input type="text" name="titleHeadItem" class="form-control" @if($config->getPure('titleHeadItem') === null) disabled="disabled" @endif value="{{ Request::old('titleHeadItem', $config->get('titleHeadItem')) }}" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('xe::recommend')}} </label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="assent" @if($config->getPure('assent') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="assent" class="form-control" @if($config->getPure('assent') === null) disabled="disabled" @endif>
                                                    <option value="true" {!! $config->get('assent') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    <option value="false" {!! $config->get('assent') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('xe::discommend')}} </label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="dissent" @if($config->getPure('dissent') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="dissent" class="form-control" @if($config->getPure('dissent') === null) disabled="disabled" @endif>
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
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="anonymity" @if($config->getPure('anonymity') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>

                                                <select id="" name="anonymity" class="form-control" @if($config->getPure('anonymity') === null) disabled="disabled" @endif>
                                                    <option value="use" {!! $anonymityHandler->isActivatedUse($config->get('anonymity')) ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    <option value="choose" {!! $anonymityHandler->isActivatedChoose($config->get('anonymity')) ? 'selected="selected"' : '' !!} >임의 사용</option>
                                                    <option value="disuse" {!! $anonymityHandler->isActivatedDisuse($config->get('anonymity')) ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::anonymityName')}} <small>{{xe_trans('board::anonymityNameDescription')}} </small></label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="anonymityName" @if($config->getPure('anonymityName') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <input type="text" name="anonymityName" class="form-control" @if($config->getPure('anonymityName') === null) disabled="disabled" @endif value="{{ Request::old('anonymityName', $config->get('anonymityName')) }}" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('xe::orderType')}} </label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="orderType" @if($config->getPure('orderType') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="orderType" class="form-control" @if($config->getPure('orderType') === null) disabled="disabled" @endif>
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
                                                    <label>{{xe_trans('board::noticeInList')}}</label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="noticeInList" @if($config->getPure('noticeInList') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="noticeInList" class="form-control" @if($config->getPure('noticeInList') === null) disabled="disabled" @endif>
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
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" name="managerEmailInherit" data-target="managerEmail" @if($config->getPure('managerEmail') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <input type="text" name="managerEmail" class="form-control" @if($config->getPure('managerEmail') === null) disabled="disabled" @endif value="{{ Request::old('managerEmail', $config->get('managerEmail')) }}" />
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::newCommentNotice')}} <small>{{xe_trans('board::newCommentNoticeDescription')}}</small></label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="newCommentNotice" @if($config->getPure('newCommentNotice') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="newCommentNotice" class="form-control" @if($config->getPure('newCommentNotice') === null) disabled="disabled" @endif>
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
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="useCaptcha" @if($config->getPure('useCaptcha') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="useCaptcha" class="form-control" @if($config->getPure('useCaptcha') === null || $captcha->available() !== true) disabled="disabled" @endif>
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
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="useTag" @if($config->getPure('useTag') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="useTag" class="form-control" @if($config->getPure('useTag') === null) disabled="disabled" @endif>
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
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="urlType" @if($config->getPure('urlType') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="urlType" class="form-control" @if($config->getPure('urlType') === null) disabled="disabled" @endif>
                                                    <option value="documentId" {!! $config->get('urlType') == 'documentId' ? 'selected="selected"' : '' !!} >{{xe_trans('board::documentId')}}</option>
                                                    <option value="slug" {!! $config->get('urlType') == 'slug' ? 'selected="selected"' : '' !!} >{{xe_trans('board::slug')}}</option>
                                                    <option value="serialNumber" {!! $config->get('urlType') == 'serialNumber' ? 'selected="selected"' : '' !!} >{{xe_trans('board::serialNumber')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::useDeleteToTrash')}} <small>{{xe_trans('board::useDeleteToTrashDescription')}} </small></label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="deleteToTrash" @if($config->getPure('deleteToTrash') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="deleteToTrash" class="form-control" @if($config->getPure('deleteToTrash') === null) disabled="disabled" @endif>
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
                                                    <label>{{xe_trans('board::secretPost')}}</label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="secretPost" @if($config->getPure('secretPost') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="secretPost" class="form-control" @if($config->getPure('secretPost') === null) disabled="disabled" @endif>
                                                    <option value="true" {!! $config->get('secretPost') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    <option value="false" {!! $config->get('secretPost') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::useApprove')}}</label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="useApprove" @if($config->getPure('useApprove') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="useApprove" class="form-control" @if($config->getPure('useApprove') === null) disabled="disabled" @endif>
                                                    <option value="true" {!! $config->get('useApprove') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    <option value="false" {!! $config->get('useApprove') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::useConsultation')}} <small>{{xe_trans('board::useConsultationDescription')}} </small></label>
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" class="inheritCheck" data-target="useConsultation" @if($config->getPure('useConsultation') === null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <select id="" name="useConsultation" class="form-control" @if($config->getPure('useConsultation') === null) disabled="disabled" @endif>
                                                    <option value="true" {!! $config->get('useConsultation') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    <option value="false" {!! $config->get('useConsultation') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::topCommonContent')}} <small>{{xe_trans("board::contentHtmlAvailable")}}</small></label>
                                                </div>
                                                {!! uio('langTextArea', ['placeholder'=>'', 'langKey'=>Request::old('topCommonContent', $config->get('topCommonContent')), 'name'=>'topCommonContent']) !!}
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::bottomCommonContent')}} <small>{{xe_trans("board::contentHtmlAvailable")}}</small></label>
                                                </div>
                                                {!! uio('langTextArea', ['placeholder'=>'', 'langKey'=>Request::old('bottomCommonContent', $config->get('bottomCommonContent')), 'name'=>'bottomCommonContent']) !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::topCommonContentOnlyList')}} <small>{{xe_trans('board::commonContentUseOnlyList')}} </small></label>
                                                </div>
                                                <select id="" name="topCommonContentOnlyList" class="form-control" >
                                                    <option value="true" {!! $config->get('topCommonContentOnlyList') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    <option value="false" {!! $config->get('topCommonContentOnlyList') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::bottomCommonContentOnlyList')}} <small>{{xe_trans('board::commonContentUseOnlyList')}} </small></label>
                                                </div>
                                                <select id="" name="bottomCommonContentOnlyList" class="form-control" >
                                                    <option value="true" {!! $config->get('bottomCommonContentOnlyList') == true ? 'selected="selected"' : '' !!} >{{xe_trans('xe::use')}}</option>
                                                    <option value="false" {!! $config->get('bottomCommonContentOnlyList') == false ? 'selected="selected"' : '' !!} >{{xe_trans('xe::disuse')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::topViewContent')}} <small>{{xe_trans('board::contentHtmlAvailable')}}</small></label>
                                                </div>
                                                {!! uio('langTextArea', ['placeholder'=>'', 'langKey'=>Request::old('topViewContent', $config->get('topViewContent')), 'name'=>'topViewContent']) !!}
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>{{xe_trans('board::bottomViewContent')}} <small>{{xe_trans('board::contentHtmlAvailable')}}</small></label>
                                                </div>
                                                {!! uio('langTextArea', ['placeholder'=>'', 'langKey'=>Request::old('bottomViewContent', $config->get('bottomViewContent')), 'name'=>'bottomViewContent']) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>목록 출력 설정</label>
                                                </div>
                                            </div>

                                            @foreach ($sortListColumns as $column)
                                                <div class="checkbox-inline">
                                                    <label>
                                                        <input type="checkbox" class="__board-sort-lists" name="{{ $column }}" value="{{ $column }}" @if (in_array($column, $config->get('listColumns')) === true) checked @endif>
                                                            @if (isset($dynamicFields[$column]) === false)
                                                                {{ xe_trans('board::' . $column) }}
                                                            @else
                                                                {{ xe_trans($dynamicFields[$column]->get('label')) }}
                                                            @endif
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <div class="clearfix">
                                                    <label>상세화면 출력 순서 설정</label>
                                                </div>
                                            </div>

                                            <div class="table-responsive item-setting">
                                                <table class="table table-sortable">
                                                    <colgroup>
                                                        <col style="width: 200px;">
                                                        <col>
                                                        <col>
                                                    </colgroup>
                                                    <tbody>
                                                    @foreach($sortFormColumns as $columnName)
                                                        @if (isset($dynamicFields[$columnName]) === false)
                                                            <tr>
                                                                <td>
                                                                    <button class="btn handler"><i class="xi-drag-vertical"></i></button>
                                                                    <em class="item-title">{{ xe_trans('board::' . $columnName) }}</em>
                                                                </td>
                                                                <td>
                                                                    <span class="item-subtext">{{ xe_trans('board::' . $columnName . 'ShowDescription') }}</span>
                                                                </td>
                                                                <td>
                                                                    <input type="hidden" name="formColumns[]" value="{{ $columnName }}" />
                                                                </td>
                                                            </tr>
                                                        @else
                                                            <tr>
                                                                <td>
                                                                    <button class="btn handler"><i class="xi-drag-vertical"></i></button>
                                                                    <em class="item-title">{{ xe_trans($dynamicFields[$columnName]->get('label')) }}</em>
                                                                </td>
                                                                <td>
                                                                    <span class="item-subtext"></span>
                                                                </td>
                                                                <td>
                                                                    <input type="hidden" name="formColumns[]" value="{{ $columnName }}" />
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                    </tbody>
                                                </table>
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

<script>
    $(function () {
        $(".table-sortable tbody").sortable({
            handle: '.handler',
            cancel: '',
            update: function( event, ui ) {
            },
            start: function(e, ui) {
                ui.placeholder.height(ui.helper.outerHeight());
                ui.placeholder.css("display", "table-row");
                ui.helper.css("display", "table");
            },
            stop: function(e, ui) {
                $(ui.item.context).css("display", "table-row");
            }
        }).disableSelection();

        $('#board_manage_form').bind('submit', function (e) {
            var $form = $(this)

            $('.__board-sort-lists').each(function () {
                if ($(this).prop('checked') === true) {
                    $form.append($('<input type="hidden" name="listColumns[]">').val($(this).val()))
                }
            })

            $('[name="formColumns[]"]').each(function() {
                $form.append($('<input type="hidden" name="sortFormColumns[]">').val($(this).val()));
            });
        })
    })
</script>

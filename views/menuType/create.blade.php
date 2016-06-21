<div class="panel-group" id="accordion">
    <div class="panel" id="panel2">
        <div class="panel-heading">
            <div class="pull-left">
                <h4 class="panel-title">
                    {{xe_trans('board::boardBasicSetting')}}
                </h4>
            </div>
            <div class="pull-right">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseMenuType" class="btn-link panel-toggle pull-right"><i class="xi-angle-down"></i><i class="xi-angle-up"></i><span class="sr-only">{{xe_trans('xe::fold')}}</span></a>
            </div>
        </div>
        <div id="collapseMenuType" class="panel-collapse">
            <div class="panel-body">
                <div class="form-group">
                    <label>Board Name</label>
                    <input type="text" name="boardName" class="form-control" value="{{Input::old('boardName', $config->get('boardName'))}}"/>
                </div>
                <div class="form-group">
                    <label>Skin</label>
                    <select name="skinId" class="form-control">
                        @foreach($skins as $key=>$skin)
                        <option value="{{$skin->getId()}}">{{$skin->getTitle()}}</option>
                        @endforeach
                    </select>

                </div>

                <div class="form-group">

                    <p><span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span> For web master's<br>{{xe_trans('board::msgCannotChangeThisSetting')}}</p>

                    <label>Table Division {{ $config->get('boardId') }}</label>
                    <select name="division" class="form-control">
                        <option value="true" {{($config->get('division') == true) ? 'selected="selected"' : ''}}>{{xe_trans('xe::use')}}</option>
                        <option value="false" {{($config->get('division') == false) ? 'selected="selected"' : ''}}>{{xe_trans('xe::disuse')}}</option>
                    </select>

                </div>
                <div class="form-group">

                    <label>Revision</label>
                    <select name="revision" class="form-control">
                        <option value="true" {{($config->get('revision') == true) ? 'selected="selected"' : ''}}>{{xe_trans('xe::use')}}</option>
                        <option value="false" {{($config->get('revision') == false) ? 'selected="selected"' : ''}}>{{xe_trans('xe::disuse')}}</option>
                    </select>
                </div>

            </div>
        </div>
    </div>
</div>


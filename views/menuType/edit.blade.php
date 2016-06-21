<div class="panel-group" id="accordion">
    <div class="panel" id="panel2">
        <div class="panel-heading">
            <div class="pull-left">
                <h3 class="panel-title">
                    {{xe_trans('board::boardBasicSetting')}}
                </h3>
            </div>
            <div class="pull-right">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseMenuTypeBoard" class="btn-link panel-toggle pull-right"><i class="xi-angle-down"></i><i class="xi-angle-up"></i><span class="sr-only">{{xe_trans('xe::fold')}}</span></a>
            </div>
        </div>
        <div id="collapseMenuTypeBoard" class="panel-collapse collapse in">
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
                    <label>Table Division<br><small>{{($config->get('division') == true) ? xe_trans('xe::use') : xe_trans('xe::disuse')}}</small></label>
                </div>

                <div class="form-group">
                    <label>Revision<br><small>{{($config->get('revision') == true) ? xe_trans('xe::use') : xe_trans('xe::disuse')}}</small></label>
                </div>
            </div>
        </div>
    </div>
</div>

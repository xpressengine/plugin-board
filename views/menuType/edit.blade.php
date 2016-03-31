<div class="panel-group" id="accordion">
    <div class="panel" id="panel2">
        <div class="panel-heading">
            <div class="pull-left">
                <h3 class="panel-title">
                    게시판 기본 설정
                </h3>
            </div>
            <div class="pull-right">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseMenuTypeBoard" class="btn-link panel-toggle pull-right"><i class="xi-angle-down"></i><i class="xi-angle-up"></i><span class="sr-only">메뉴닫기</span></a>
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
                    <label>Table Division<br><small>{{($config->get('division') == true) ? 'Use' : 'Disuse'}}</small></label>
                </div>

                <div class="form-group">
                    <label>Revision<br><small>{{($config->get('revision') == true) ? 'Use' : 'Disuse'}}</small></label>
                </div>
            </div>
        </div>
    </div>
</div>

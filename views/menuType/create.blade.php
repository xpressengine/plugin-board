<div class="panel-group" id="accordion">
    <div class="panel" id="panel2">
        <div class="panel-heading">
            <div class="pull-left">
                <h4 class="panel-title">
                    게시판 기본 설정
                </h4>
            </div>
            <div class="pull-right">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseMenuType" class="btn-link panel-toggle pull-right"><i class="xi-angle-down"></i><i class="xi-angle-up"></i><span class="sr-only">메뉴닫기</span></a>
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

                    <p><span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span> For web master's<br>이 설정은 등록 할 때만 처리 됩니다. 설정 변경 시 이 항목은 제공되지 않습니다.</p>

                    <label>Table Division {{ $config->get('boardId') }}</label>
                    <select name="division" class="form-control">
                        <option value="true" {{($config->get('division') == true) ? 'selected="selected"' : ''}}>Use</option>
                        <option value="false" {{($config->get('division') == false) ? 'selected="selected"' : ''}}>Disuse</option>
                    </select>

                </div>
                <div class="form-group">

                    <label>Revision</label>
                    <select name="revision" class="form-control">
                        <option value="true" {{($config->get('revision') == true) ? 'selected="selected"' : ''}}>Use</option>
                        <option value="false" {{($config->get('revision') == false) ? 'selected="selected"' : ''}}>Disuse</option>
                    </select>
                </div>

            </div>
        </div>
    </div>
</div>


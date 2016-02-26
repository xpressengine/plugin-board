<div class="panel-group" id="accordion">
    <div class="panel panel-default" id="panel2">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-target="#collapseMenuType"
                   href="#collapseMenuType">
                    게시판 기본 설정
                </a>
            </h4>

        </div>
        <div id="collapseMenuType" class="panel-collapse collapse">
            <div class="panel-body">

                <dl>
                    <dt>Board Name</dt>
                    <dd><input type="text" name="boardName" class="form-control" value="{{Input::old('boardName', $config->get('boardName'))}}"/></dd>
                    <dt>Skin</dt>
                    <dd>
                        <select name="skinId" class="form-control">
                            @foreach($skins as $key=>$skin)
                                <option value="{{$skin->getId()}}">{{$skin->getTitle()}}</option>
                            @endforeach
                        </select>
                    </dd>
                </dl>

                <span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span> For web master's
                <p>이 설정은 등록 할 때만 처리 됩니다. 설정 변경 시 이 항목은 제공되지 않습니다.</p>
                <div style="padding:10px; border-top:1px solid #ccc;">

                    <dl>
                        <dt>Table Division {{ $config->get('boardId') }}</dt>
                        <dd>
                            <select name="division" class="form-control">
                                <option value="true" {{($config->get('division') == true) ? 'selected="selected"' : ''}}>Use</option>
                                <option value="false" {{($config->get('division') == false) ? 'selected="selected"' : ''}}>Disuse</option>
                            </select>
                        </dd>
                    </dl>

                    <dl>
                        <dt>Revision</dt>
                        <dd>
                            <select name="revision" class="form-control">
                                <option value="true" {{($config->get('revision') == true) ? 'selected="selected"' : ''}}>Use</option>
                                <option value="false" {{($config->get('revision') == false) ? 'selected="selected"' : ''}}>Disuse</option>
                            </select>
                        </dd>
                    </dl>
                </div>

            </div>
        </div>
    </div>
</div>


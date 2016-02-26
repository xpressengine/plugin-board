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

                <dl>
                    <dt>Table Division</dt>
                    <dd>
                        {{($config->get('division') == true) ? 'Use' : 'Disuse'}}
                    </dd>
                </dl>

                <dl>
                    <dt>Revision</dt>
                    <dd>
                        {{($config->get('revision') == true) ? 'Use' : 'Disuse'}}
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

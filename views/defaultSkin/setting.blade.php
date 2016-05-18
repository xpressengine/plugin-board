<div class="row">
    <div class="col-sm-12">
        <div class="xe-form-group">
            <label for="">{{xe_trans('xe::list')}}</label>
            <label><input type="checkbox" class="inheritCheck" data-select=".listColumns" @if($config->getPure('listColumns') == null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}</label>
            <div class="form-inline listColumns">
                <div class="xe-form-group">
                    <select class="xe-form-control" id="list_options" size="8" multiple="multiple">
                        @foreach ($listOptions as $columnName)
                            <option value="{{$columnName}}">{{$columnName}}</option>
                        @endforeach
                    </select>
                    <div>
                        <button type="button" class="btn btn-default list-option-add">추가</button>
                    </div>

                </div>
                <div class="xe-form-group">
                    <select class="xe-form-control" id="list_selected" size="8" multiple="multiple" @if($config->getPure('listColumns') == null) disabled="disabled" @endif>
                        @foreach ($listColumns as $columnName)
                            <option value="{{$columnName}}">{{$columnName}}</option>
                        @endforeach
                    </select>
                    <div>
                        <button type="button" class="btn btn-default list-option-up">위로</button>
                        <button type="button" class="btn btn-default list-option-down">아래로</button>
                        <button type="button" class="btn btn-default list-option-delete">삭제</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="xe-form-group">
            <label for="">{{xe_trans('xe::input')}}</label>
            <label><input type="checkbox" class="inheritCheck" data-select=".formColumns" @if($config->getPure('formColumns') == null) checked="checked" @endif />{{ xe_trans('xe::inheritMode') }}</label>
            <div class="xe-form-group formColumns">
                <select class="xe-form-control" id="form_order" size="8" multiple="multiple" @if($config->getPure('formColumns') == null) disabled="disabled" @endif>
                    @foreach ($formColumns as $columnName)
                        <option value="{{$columnName}}">{{$columnName}}</option>
                    @endforeach
                </select>
                <div>
                    <button type="button" class="btn btn-default form-order-up">위로</button>
                    <button type="button" class="btn btn-default form-order-down">아래로</button>
                </div>
            </div>
        </div>
    </div>
</div>

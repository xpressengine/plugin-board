<div class="board-contents">
    <h2>{{ xe_trans('xe::identification') }}</h2>
    <p>{{ xe_trans('xe::enterYourPassword') }}</p>

    <form method="post" action="{{ $urlHandler->get('certify') }}">
        <input type="hidden" name="_token" value="{{{ Session::token() }}}" />
        <input type="hidden" name="referer" class="form-control" value="{{$referer}}" />
        <input type="hidden" name="id" class="form-control" value="{{$doc->id}}" />

        <input type="email" name="email" class="form-control" value="" placeholder="{{ xe_trans('xe::email') }}"/>
        <input type="password" name="certifyKey" class="form-control" value="" placeholder="{{ xe_trans('xe::password') }}"/>

        <div class="btns">
            <div class="btn-left">
                <a class="btn btn-default" href="{{ URL::previous() }}">{{ xe_trans('xe::back') }}</a>
            </div>
            <div class="btn-right">
                <button type="submit" class="btn btn-defalut">{{ xe_trans('xe::confirm') }}</button>
            </div>
        </div>
    </form>
</div>
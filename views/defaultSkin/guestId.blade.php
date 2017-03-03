{{ XeFrontend::rule('board', $rules) }}

{!! XeFrontend::css('assets/core/member/auth.css')->load() !!}
<div class="member">
    <h1>{{ xe_trans('xe::identification') }}</h1>
    <form action="{{ $urlHandler->get('guest.certify', ['id' => $item->id]) }}" method="post" data-rule="board">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="referrer" value="{{$referrer}}" />
        <input type="hidden" name="id" value="{{$item->id}}" />

        <fieldset>
            <legend>{{ xe_trans('xe::identification') }}</legend>
            <div class="auth-group">
                <label for="name" class="sr-only">{{ xe_trans('xe::email') }}</label>
                <input name="email" type="email" class="xe-form-control" value="{{ old('email') }}" placeholder="{{ xe_trans('xe::email') }}">
            </div>
            <div class="auth-group">
                <label for="pwd" class="sr-only">{{ xe_trans('xe::password') }}</label>
                <input name="certifyKey" type="password" class="xe-form-control" placeholder="{{ xe_trans('xe::password') }}">
            </div>

            <button type="submit" class="xe-btn xe-btn-primary">{{ xe_trans('xe::confirm') }}</button>
        </fieldset>
    </form>
</div>

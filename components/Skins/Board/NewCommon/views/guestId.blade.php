{{ XeFrontend::rule('board', $rules) }}

{!! XeFrontend::css('assets/core/user/auth.css')->load() !!}
<div class="user xe-board-body">
    <h1 class="xe-board-body__title">{{ xe_trans('xe::identification') }}</h1>
    <form action="{{ $urlHandler->get('guest.certify', ['id' => $item->id]) }}" method="post" data-rule="board">
        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
        <input type="hidden" name="referrer" value="{{$referrer}}"/>
        <input type="hidden" name="id" value="{{$item->id}}"  />

        <fieldset>
            <legend>{{ xe_trans('xe::identification') }}</legend>
            <div class="auth-group xe-board-body__input">
                <label for="name" class="sr-only">{{ xe_trans('xe::email') }}</label>
                <input name="email" type="email" class="xe-form-control xe-board-body__input-email" value="{{ old('email') }}" placeholder="{{ xe_trans('xe::email') }}">
            </div>
            <div class="auth-group xe-board-body__input">
                <label for="pwd" class="sr-only">{{ xe_trans('xe::password') }}</label>
                <input name="certify_key" type="password" class="xe-form-control xe-board-body__input-password" placeholder="{{ xe_trans('xe::password') }}">
            </div>

            <button type="submit" class="xe-list-board__btn">{{ xe_trans('xe::confirm') }}</button>
        </fieldset>
    </form>
</div>

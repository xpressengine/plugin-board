@if ($scriptInit)
    {{ XeFrontend::js('plugins/board/assets/js/BoardTags.js')->appendTo('body')->load() }}
@endif

<div id="{{ $id }}" class="{{ $class }}" data-tags="{{ $strTags }}" data-url="{{$url}}">
    <vue-tags-input v-model="tag" :tags="tags" @tags-changed="update" :autocomplete-items="autocompleteItems" placeholder="{{ $placeholder }}"></vue-tags-input>
</div>

<style>
.__xe-board-tag .vue-tags-input .ti-input {
  max-width: 100%;
}

.ti-input[data-v-61d92e31] {
  border: none !important;
}

.vue-tags-input[data-v-61d92e31] {
  max-width: none;
  line-height: 55px;
}
</style>

@if ($scriptInit)
    {{ XeFrontend::js('plugins/board/assets/js/BoardTags.js')->appendTo('body')->load() }}
@endif

<div id="{{ $id }}" class="{{ $class }}" data-tags="{{ $strTags }}" data-url="{{$url}}">
    <vue-tags-input v-model="tag" :tags="tags" @tags-changed="update" :autocomplete-items="autocompleteItems" placeholder="{{ $placeholder }}"></vue-tags-input>
</div>

<style>
  .xe-select-label {
    height: 60px;
  }

  .__xe-board-tag .vue-tags-input .ti-input {
    max-width: 100%;
  }

  .ti-input[data-v-61d92e31] {
    border: none !important;
  }
  .vue-tags-input[data-v-61d92e31] {
    max-width: none;
    line-height: 60px;
    border-bottom: 1px solid #c0c0c0;
  }

  .ti-tag.ti-invalid[data-v-61d92e31], .ti-tag.ti-tag.ti-deletion-mark[data-v-61d92e31] {
    background-color: rgba(0, 0, 0, 0.8);
    border-radius: 0;
  }
</style>
import Vue from 'vue'
import VueTagsInput from '@johmun/vue-tags-input'

window.jQuery(function ($) {
  const $container = $('.__xe-board-tag')
  const $form = $container.closest('form')
  const vm = new Vue({
    el: '.__xe-board-tag',
    components: {
      VueTagsInput
    },

    data () {
      return {
        tag: '',
        tags: [],
        autocompleteItems: [],
        searchItem: null
      }
    },

    methods: {
      update (newTags) {
        this.autocompleteItems = []
        this.tags = newTags
      },
      initItems () {
        const tags = $container.data('tags')
        // console.debug('initItems', $container.data('tags'))
        if (tags) {
          $container.data('tags').forEach(val => {
            this.tags.push({text: val})
          })
        }

        const url = XE.config.getters['router/origin'] + '/' + $container.data('url')
        this.searchItem = XE.Utils.debounce((keyword) => {
          window.XE.ajax({
            url: url,
            data: {
              string: keyword
            },
            type: 'get',
            dataType: 'json',
            success: (res) => {
              const searchItems = []
              res.map(item => {
                this.autocompleteItems.push({ text: item.word })
              })
              return searchItems
            }
          })
        }, 500)
      },
      searchItems () {
        if (this.tag.length === 0) return

        this.autocompleteItems = []
        this.searchItem(this.tag)
      }
    },

    computed: {
      tagsArray () {
        let values = []
        this.tags.forEach(val => {
          values.push(val.text)
        })
        return values
      }
    },
    watch: {
      'tag': 'searchItems'
    }
  })

  vm.initItems()

  $form.on('submit', function (event) {
    const $this = $(this)
    const tags = vm.tagsArray

    $this.find('input[type=hidden].paramTags').remove()

    tags.forEach((val) => {
      $this.append(`<input type="hidden" class="paramTags" name="_tags[]" value="${val}">`)
    })
  })
})

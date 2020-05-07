$(function () {
    $('.xe-dropdown--menu-item').on('click', function (e) {
        e.preventDefault()
        
        var $this = $(this)
        var $container = $(this).closest('.new-select')
        var $parent = $(this).closest('.xe-dropdown__button')
        var name = $(this).closest('.xe-dropdown--menu').data('name')
        var $nameInput = $container.find('[name=' + name + ']')

        $parent.find('.xe-dropdown--menu-item').removeClass('on')
        $this.addClass('on')
        $nameInput.val($this.data('value'))
        $container.find('button').text($this.text())

        $nameInput.trigger('change')
    })
})

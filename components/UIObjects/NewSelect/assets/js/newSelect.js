$(function () {
    $('.xe-list-board-header--dropdown-menu-item').on('click', function (e) {
        e.preventDefault()
        
        var $this = $(this)
        var $container = $(this).closest('.xe-list-board-header--dropdown')
        var $parent = $(this).closest('.xe-list-board-header--dropdown__button')
        var name = $('.xe-list-board-header--dropdown-menu').data('name')
        var $nameInput = $container.find('[name=' + name + ']')
        
        console.log(name)
        
        $parent.find('.xe-list-board-header--dropdown-menu-item').removeClass('on')
        $this.addClass('on')
        $nameInput.val($this.data('value'))
        $container.find('button').text($this.text())
        
        $nameInput.trigger('change')
    })
})

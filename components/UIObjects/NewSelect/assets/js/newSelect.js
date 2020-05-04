$(function () {
    $('.xe-list-board-header--dropdown-menu-item').on('click', function (e) {
        e.preventDefault()
        
        var $this = $(this)
        var $parent = $(this).closest('.xe-list-board-header--dropdown__button')
        var $container = $(this).closest('.xe-list-board-header--dropdown')
        
        $parent.find('.xe-list-board-header--dropdown-menu-item').removeClass('on')
        $this.addClass('on')
        $container.find('button').text($this.text())
    })
})

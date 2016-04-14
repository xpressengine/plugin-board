var XeBoardSkin = {
    VoteBox: React.createClass({
        getInitialState: function () {
            this.request('show', '');

            return {
                data: {
                    display: {assent: false, dissent: false},
                    counts: {assent:0, dissent:0},
                    voteAt: null  // 참여 안됨
                }
            };
        },

        request: function (action, option) {
            var self = this;

            if (action == undefined) {
                action = 'show';
            }

            var type = 'post';
            if (action == 'show' || action == 'users') {
                type = 'get';
            }

            if (option == undefined) {
                option = '';
            }

            var params = {id:this.props.id};

            var url = this.props.url + '/' + action;
            if (option != '') {
                url = url + '/' + option;
            }

            XE.ajax({
                url: url,
                type: type,
                dataType: 'json',
                data: params
            }).done(function (json) {
                self.setState({data: json});
            });

        },

        componentDidMount: function() {
        },

        render: function () {
            return React.DOM.div({
                    className:'board_document_votebox btn-group'
                },
                React.createElement(XeBoardSkin.VoteButton, $.extend({}, this.state.data, {option:'assent', cb:this.request})),
                React.createElement(XeBoardSkin.VoteButton, $.extend({}, this.state.data, {option:'dissent', cb:this.request}))
            )
        }
    }),

    VoteButton: React.createClass({
        onclick: function(e) {
            var action = 'add';
            if (this.props.voteAt == this.props.option) {
                action = 'remove';
            }

            this.props.cb(action, this.props.option);
        },

        render: function() {

            var classNames = ['btn', 'btn-'+this.props.option];

            if (this.props.voteAt == this.props.option) {
                classNames.push('voted');
            } else if (this.props.voteAt != null) {
                classNames.push('disabled');
            }

            var iconClassName = 'glyphicon-chevron-' + (this.props.option == 'assent' ? 'up' : 'down');
            var count = this.props.option == 'assent' ? this.props.counts.assent : this.props.counts.dissent;
            var display = this.props.display[this.props.option] == false ? 'none' : 'block';

            return React.DOM.button({
                    className: classNames.join(' '),
                    onClick: this.onclick,
                    style:{display: display}
                }
                , React.DOM.span({className:'glyphicon ' + iconClassName})
                , React.DOM.br({})
                , React.DOM.span({style:{margin:'10px 10px'}}, count)
            );
        }
    })
};

$(function($) {
    if ($('.__xe_vote_document').length) {
        React.render(
            React.createElement(XeBoardSkin.VoteBox, {
                url: $('.__xe_vote_document').attr('data-url'),
                id: $('.__xe_vote_document').attr('data-id')
            }),
            $('.__xe_vote_document')[0]
        );
    }

    if ($('.__xe_manage_menu_document').length) {
        React.render(
            React.createElement(ToggleMenu, {
                type: "module/board@board/" +  $('.__xe_manage_menu_document').attr('data-instance-id'),
                identifier: $('.__xe_manage_menu_document').attr('data-id'),
                align: 'right',
                //class: '',
                //text: ' • • • ',
                //itemClass: '',
                html: $('.__xe_manage_menu_document').html(),
                data: {
                    id: $('.__xe_manage_menu_document').attr('data-id')
                }
            }),
            $('.__xe_manage_menu_document')[0]
        );
    }

    $('.__board_form').on('click', '.__xe_btn_preview', function(event) {
        event.preventDefault();

        var form = $(this).parents('form');

		var currentUrl = form.attr('action');
		var currentTarget = form.attr('target');
		var pieces = currentUrl.split('/');
		pieces[pieces.length-1] = 'preview';
		form.attr('action', pieces.join('/'));
		form.attr('target', '_blank');
		form.submit();

		form.attr('action', currentUrl);
		form.attr('target', currentTarget === undefined ? '' : currentTarget);
    }).on('click', '.__xe_btn_submit', function(event) {
        event.preventDefault();
        var form = $(this).closest('form');
        form.trigger('submit');
    });
});


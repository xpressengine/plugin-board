System.import('vendor:/react-tag-input').then(function() {
    System.amdRequire(['react', 'react-dom', 'jquery', 'react-tag-input'], function(React, ReactDOM, $, TagInput) {

        $.noConflict();

        var $container = $('#xeBoardTagWrap');
        var ReactTags = TagInput.WithContext;
        var BoardTags = React.createClass({
            getInitialState: function() {
                return {
                    tags: this.props.tags || [],
                    suggestions: []
                };
            },
            handleDelete: function(i) {
                var tags = this.state.tags;
                tags.splice(i, 1);
                this.setState({tags: tags});
            },
            handleAddition: function(tag) {
                var tags = this.state.tags;
                tags.push({
                    id: tags.length + 1,
                    text: tag
                });
                this.setState({tags: tags});
            },
            handleInputChange: function(value) {
                var self = this;

                if(value.length > 1) {
                    $.ajax({
                        url: $container.data('url'),
                        data: {
                            string: value
                        },
                        type: 'get',
                        dataType: 'json',
                        success: function(suggestions) {
                            self.setState(function(state, props) {
                                var items = [];
                                $.each(suggestions, function(index, item) {
                                    items.push(item.word);
                                });
                                state.suggestions = items;
                            });
                        }
                    });
                }
            },
            render: function() {
                var tags = this.state.tags;
                var suggestions = this.state.suggestions;
                var placeholder = $container.data('placeholder');

                return (
                    <div>
                        <ReactTags placeholder={placeholder}
                                   allowDeleteFromEmptyInput={false}
                                   autofocus={false}
                                   tags={tags}
                                   suggestions={suggestions}
                                   handleDelete={this.handleDelete}
                                   handleAddition={this.handleAddition}
                                   handleInputChange={this.handleInputChange}
                        />
                    </div>
                );
            }
        });



        ReactDOM.render(<BoardTags tags={JSON.parse($container.data('tags'))} />, document.getElementById('xeBoardTagWrap'));
    });
});

$(function($) {
    var $container = $('#xeBoardTagWrap');

    $container.closest('form').on('submit', function (event) {
        var $this = $(this),
            tagSet = [];

        $this.find("input[type=hidden].paramReactTags").remove();

        $container.find('.ReactTags__tag').text(function (i, v) {
            // text 에 'x'(삭제버튼) 이 포함되어 있음
            v = v.substring(0, v.length - 1);
            if ($.inArray(v, tagSet) === -1) {
                $this.append("<input type='hidden' class='paramReactTags' name='_tags[]' value='" + v + "'>");
                tagSet.push(v);
            }
        });
    });
});

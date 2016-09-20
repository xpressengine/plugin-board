System.import('vendor:/react-tag-input').then(function() {
    System.amdRequire(['react', 'react-dom', 'jquery', 'react-tag-input'], function(React, ReactDOM, $, TagInput) {

    $.noConflict();

    var $container = $('#xeBoardTagWrap');
    var ReactTags = TagInput.WithContext;
    var BoardTags = React.createClass({
        getInitialState: function() {
            return {
                tags: [],
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
                            state.suggestions = suggestions;
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

    ReactDOM.render(<BoardTags />, document.getElementById('xeBoardTagWrap'));
    });
});
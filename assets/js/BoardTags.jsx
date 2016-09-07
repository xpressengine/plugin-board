System.amdRequire(['react', 'react-dom', 'react-tag-input'], function(React, ReactDOM, TagInput) {
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
                    url: "/editor/hashTag",
                    data: {
                        string: value
                    },
                    type: 'get',
                    dataType: 'json',
                    success: function(suggestions) {
                        self.setState(function(state, props) {
                            // state.suggestions = suggestions;
                            state.suggestions = ['aa','aa1','aa2','aa3','aa4','as5'];
                        })
                    }
                });
            }
        },
        render: function() {
            var tags = this.state.tags;
            var suggestions = this.state.suggestions;

            return (
                <div>
                    <ReactTags placeholder="태그를 입력하세요."
                               allowDeleteFromEmptyInput={false}
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
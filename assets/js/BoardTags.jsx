System.amdRequire('react-tag-input', function(tag) {
    var ReactTags = tag.WithContext;
    //
    // var ReactTags = React.createClass({
    //     getInitialState: function() {
    //         return {
    //             test: 'test'
    //         }
    //     },
    //     render: function() {
    //         return (
    //             <div className="asdf"> { this.state.test } </div>
    //         )
    //     }
    // });

    console.log(ReactTags);

    var BoardTags = React.createClass({
        getInitialState: function() {
            return {
                tags: [ {id: 1, text: "Apples"} ],
                suggestions: ["Banana", "Mango", "Pear", "Apricot"]
            }
        },
        handleDelete: function(i) {
            let tags = this.state.tags;
            tags.splice(i, 1);
            this.setState({tags: tags});
        },
        handleAddition: function(tag) {
            let tags = this.state.tags;
            tags.push({
                id: tags.length + 1,
                text: tag
            });
            this.setState({tags: tags});
        },
        handleDrag: function(tag, currPos, newPos) {
            let tags = this.state.tags;

            // mutate array
            tags.splice(currPos, 1);
            tags.splice(newPos, 0, tag);

            // re-render
            this.setState({ tags: tags });
        },
        render: function() {
            let tags = this.state.tags;
            let suggestions = this.state.suggestions;
            return (
                <div>
                    <ReactTags tags={tags}
                               suggestions={suggestions}
                               handleDelete={this.handleDelete}
                               handleAddition={this.handleAddition}
                               handleDrag={this.handleDrag} />
                </div>
            )
        }
    });

    ReactDOM.render(<BoardTags />, document.getElementById('xeBoardTagWrap'));
});



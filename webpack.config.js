var path = require('path')
var webpack = require('webpack')

module.exports = {
  entry: {
    'assets/js/BoardTags': './assets/js/src/BoardTags.jsx',
    'assets/js/board': './assets/js/src/board.js'
  },
  output: {
    path: path.resolve(__dirname, './'),
    filename: '[name].js'
  },
  plugins: [
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: JSON.stringify('production')
      }
    })
  ],
  module: {
    loaders: [
      {
        test: /(\.js|\.jsx)$/,
        loader: 'babel-loader',
        exclude: /node_modules/,
        query: {
          presets: ['es2015', 'react'],
          cacheDirectory: true
        }
      }
    ]
  },
  resolve: {
    extensions: ['.js', '.jsx']
  },
  externals: {
    window: 'window',
    moment: 'moment'
  }
}

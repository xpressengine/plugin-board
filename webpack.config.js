var path = require('path');
var webpack = require('webpack');
var CommonsChunkPlugin = require('webpack/lib/optimize/CommonsChunkPlugin');
var CleanWebpackPlugin = require('clean-webpack-plugin');

module.exports = {
  devtool: 'source-map',
  entry: {
    'assets/js/build/BoardTags': [
      './assets/js/BoardTags.jsx'
    ],
    'assets/js/build/board': [
      './assets/js/board.js'
    ],
  },
  output: {
    path: path.resolve(__dirname, './'),
    filename: '[name].js',
  },
  plugins: [
    new webpack.NoErrorsPlugin(),
    new webpack.optimize.UglifyJsPlugin({ minimize: true, compress: { warnings: false }, sourceMap: false}),
    new webpack.DefinePlugin({
      'process.env': {
        // This has effect on the react lib size
        NODE_ENV: JSON.stringify('production'),
      },
    }),
    new CleanWebpackPlugin(['build'], {
      root: path.join(__dirname, './assets/js'),
      verbose: true,
      dry: false,
      exclude: []
    }),

  ],
  module: {
    loaders: [
      {
        test: /(\.js|\.jsx)$/,
        loader: 'babel-loader',
        exclude: /node_modules/,
        query: {
          presets: ['es2015', 'react'],
          cacheDirectory: true,
        },
      },
    ],
  },
  resolve: {
    extensions: ['', '.js', '.jsx'],
  },
  externals: {
    window: 'window',
  },
};

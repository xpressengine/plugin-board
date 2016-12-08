var path = require('path');
var webpack = require('webpack');
var webpackMerge = require('webpack-merge');
var CommonsChunkPlugin = require('webpack/lib/optimize/CommonsChunkPlugin');
var CleanWebpackPlugin = require('clean-webpack-plugin');

var prodConfig = require('./webpack.prod.config');
var devConfig = require('./webpack.dev.config');
var target = true;//(process.env.npm_lifecycle_event === 'build')? true : false;

var common = {
  devtool: 'source-map',
  entry: {
    'assets/build/defaultSkin': './assets/defaultSkin/js/index.js'
  },
  output: {
    path: path.resolve(__dirname, './'),
    filename: '[name].js',
  },
  plugins: [
    new CleanWebpackPlugin(['build'], {
      root: path.join(__dirname, './assets/build'),
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
          cacheDirectory: true,
          presets: ['es2015', 'stage-0', 'react']
        }
      },
      {
        test: /\.css$/,
        loader: 'style!css-loader?modules&importLoaders=1&localIdentName=[name]__[local]___[hash:base64:5]'
      }
    ],
  },
  resolve: {
    extensions: ['', '.js', '.jsx'],
  },
  externals: {
    window: 'window',
  },
};

var config;

if (target) {
  config = webpackMerge(common, prodConfig);
} else {
  config = webpackMerge(common, devConfig);
}

module.exports = config;
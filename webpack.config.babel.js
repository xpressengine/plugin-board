import path from 'path'
import webpack from 'webpack'
import CopyWebpackPlugin from 'copy-webpack-plugin'
import { resolveAlias } from '../../webpack.config.babel'

export default {
  entry: {
    'assets/js/BoardTags': './assets/js/src/BoardTags.jsx',
    'assets/js/board': './assets/js/src/board.js'
  },
  output: {
    path: path.resolve(__dirname, './'),
    filename: '[name].js'
  },
  plugins: [
    new CopyWebpackPlugin([
      {
        context: path.resolve(__dirname, 'assets/js/src'),
        from: '**/*',
        to: path.resolve(__dirname, 'assets/js'),
        ignore: [
          '**/*.jsx',
          'board.js'
        ]
      }
    ]),
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: JSON.stringify('production')
      }
    }),
    new webpack.DllReferencePlugin({
      context: path.resolve(__dirname, './'),
      manifest: require('../../resources/assets/vendor-manifest.json')
    }),
    new webpack.DllReferencePlugin({
      context: path.resolve(__dirname, './'),
      manifest: require('../../resources/assets/common-manifest.json')
    })
  ],
  module: {
    loaders: [
      {
        test: /(\.js|\.jsx)$/,
        loader: 'babel-loader',
        exclude: /node_modules/,
        query: {
          cacheDirectory: true
        }
      }
    ]
  },
  resolve: {
    'alias': resolveAlias,
    extensions: ['.js', '.jsx']
  },
  externals: {
    window: 'window'
  }
}

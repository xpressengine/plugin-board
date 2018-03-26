import path from 'path'
import webpack from 'webpack'
import CopyWebpackPlugin from 'copy-webpack-plugin'

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
    extensions: ['.js', '.jsx']
  },
  externals: {
    window: 'window'
  }
}

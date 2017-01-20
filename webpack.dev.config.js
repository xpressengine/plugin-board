var webpack = require('webpack');

module.exports = {
  devtool: 'eval-source-map',
  plugins: [
    new webpack.NoErrorsPlugin(),
    new webpack.optimize.DedupePlugin(), //중복 모듈 제거
  ],
};

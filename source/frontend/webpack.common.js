const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

module.exports = {
  entry: './src/index.ts',
  module: {
    rules: [
      {
        test: /\.ts$/,
        loader: 'ts-loader',
        exclude: /node_modules/,
        options: {
          appendTsSuffixTo: [/\.vue$/],
        },
      },
      {
        test: /\.(png|svg|jpg|gif)$/,
        use: ['file-loader'],
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },
    ],
  },
  resolve: {
    extensions: ['.ts', '.js', '.vue'],
    alias: {
      vue$: 'vue/dist/vue.esm.js',
    },
  },
  plugins: [
    new VueLoaderPlugin(),
  ],
  externals: {
    axios: 'axios',
    lodash: '_',
    vue: 'Vue',
    'vue-router': 'VueRouter',
  },
    output: {
        path: path.resolve(__dirname, './dist'),
    },
};

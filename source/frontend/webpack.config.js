const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

module.exports = {
    entry: './src/index.ts',
    mode: 'development',
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.ts$/,
                loader: 'ts-loader',
                exclude: /node_modules/,
                options: {
                    appendTsSuffixTo: [/\.vue$/],
                }
            }
        ],
    },
    resolve: {
        extensions: [ '.ts', '.vue' ],
        alias: {
            'vue$': 'vue/dist/vue.esm.js'
        }
    },
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, '../../deploy/web/dist')
    },
    plugins: [
        new VueLoaderPlugin(),
    ],
    watch: true
};
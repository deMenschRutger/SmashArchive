const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

module.exports = {
    entry: './src/index.ts',
    // TODO Also include jsonwebtoken as an external (doesn't have a minified version).
    externals: {
        axios: 'axios',
        lodash: '_',
        vue: 'Vue',
        'vue-router': 'VueRouter',
    },
    mode: 'development',
    module: {
        rules: [
            {
                test: /\.ts$/,
                loader: 'ts-loader',
                exclude: /node_modules/,
                options: {
                    appendTsSuffixTo: [/\.vue$/],
                }
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader',
            },
        ],
    },
    resolve: {
        extensions: [ '.ts', '.js', '.vue' ],
        alias: {
            'vue$': 'vue/dist/vue.esm.js',
        }
    },
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, '../../source/symfony/public/dist')
    },
    plugins: [
        new VueLoaderPlugin(),
    ],
    watch: true
};
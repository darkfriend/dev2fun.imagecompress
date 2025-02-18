const webpack = require('webpack')
const { merge } = require('webpack-merge')
const TerserPlugin = require('terser-webpack-plugin')

const commonConfig = require('./webpack.config.common')
const environment = require('./env/dev.env')

const publicPath = ((dir) => {
    let publicPath
    switch (dir) {
        case 'devserver':
            publicPath = '/bitrix/js/dev2fun.imagecompress/vue/'
            break
        case 'server':
            publicPath = '/bitrix/js/dev2fun.imagecompress/vue/'
            break
        default:
            publicPath = '/bitrix/js/dev2fun.imagecompress/vue/'
            break
    }
    return publicPath
})(process.env.DIR)

const path = require('node:path')

const PATHS = {
    src: path.join(__dirname, '../src'),
    // dist: path.join(__dirname, '../dist', destinationDir),
    dist: path.join(__dirname, '../../../../js/dev2fun.imagecompress/vue'),
    // assets: 'assets/',
}

const webpackConfig = merge(commonConfig, {
    mode: 'development',
    devtool: 'cheap-module-source-map',
    // entry: {
    //     vendor: ['bootstrap_vue', 'icons'],
    // },
    output: {
        path: PATHS.dist,
        publicPath,
        filename: 'js/[name].bundle.js',
        chunkFilename: 'js/[name].[contenthash:8].chunk.js',
    },
    optimization: {
        minimizer: [
            new TerserPlugin({
                // cache: true,
                parallel: true,
                terserOptions: {
                    warnings: false,
                    ie8: false,
                    output: {
                        comments: false,
                    },
                },
                extractComments: false,
            }),
        ],
        splitChunks: {
            // chunks: 'all',
            maxInitialRequests: Infinity,
            minSize: 0,
        },
    },
    plugins: [
        new webpack.EnvironmentPlugin(environment),
        new webpack.HotModuleReplacementPlugin(),
        new webpack.optimize.ModuleConcatenationPlugin(),
    ],
})

module.exports = webpackConfig

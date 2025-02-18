const path = require('node:path')

const webpack = require('webpack')
const { merge } = require('webpack-merge')
// const CssMinimizerPlugin = require('css-minimizer-webpack-plugin')

const TerserPlugin = require('terser-webpack-plugin')
// const CompressionPlugin = require('compression-webpack-plugin')
const commonConfig = require('./webpack.config.common')

const isProd = process.env.NODE_ENV === 'production'
const destinationDir = process.env.PROJECT
const environment = isProd ? require('./env/prod.env') : require('./env/staging.env')


const publicPath = ((dir) => {
    let publicPath
    switch (dir) {
        case 'dev':
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

const PATHS = {
    src: path.join(__dirname, '../src', destinationDir),
    // dist: path.join(__dirname, '../dist', destinationDir),
    dist: path.join(__dirname, '../../../../js/dev2fun.imagecompress/vue'),
    assets: 'assets/',
}

const webpackConfig = merge(commonConfig, {
    mode: 'production',
    devtool: false,
    output: {
        clean: true,
        path: PATHS.dist,
        publicPath,
        filename: 'js/[name].bundle.js',
        chunkFilename: 'js/[name].[contenthash:8].chunk.js',
    },
    optimization: {
        minimize: true,
        minimizer: [
            // new CssMinimizerPlugin(),
            new TerserPlugin({
                // cache: true,
                parallel: true,
                terserOptions: {
                    ecma: 6,
                    ie8: false,
                    compress: {
                        passes: 2,
                        drop_console: true,
                        warnings: false,
                    },
                    output: {
                        comments: false,
                    },
                },
                extractComments: false,
            }),
        ],
        // flagIncludedChunks: true,
        splitChunks: {
            // chunks: "all",
            chunks: 'async',
            minSize: 20000,
            minChunks: 2,
            maxAsyncRequests: 5,
            maxInitialRequests: 3,
            automaticNameDelimiter: '~',
            // name: true,
            cacheGroups: {
                vendor: {
                    test: /[\\/]node_modules[\\/]/,
                    name(module) {
                        let packageName = module.context.match(/[\\/]node_modules[\\/](.*?)([\\/]|$)/)?.[1]
                        if (!packageName) {
                            packageName = 'vendor'
                        }
                        return `npm.${packageName.replace('@', '')}`
                    },
                    minSize: 20000,
                    reuseExistingChunk: true,
                },
                // styles: {
                //     chunks: 'all',
                //     test: /\.css$/,
                //     name: 'styles',
                //     enforce: true,
                //     minSize: 20000,
                //     priority: 2,
                //     reuseExistingChunk: true
                // },
            },
        },
    },
    plugins: [
        new webpack.EnvironmentPlugin(environment),

        // new CompressionPlugin({
        //     filename: '[path].gz[query]',
        //     algorithm: 'gzip',
        //     test: new RegExp('\\.(js|css)$'),
        //     threshold: 10240,
        //     minRatio: 0.8
        // }),
        new webpack.ids.HashedModuleIdsPlugin(),
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: 'true',
            __VUE_PROD_DEVTOOLS__: 'false',
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: 'false',
        }),
    ],
})

if (!isProd) {
    webpackConfig.devtool = 'source-map'

    if (process.env.npm_config_report) {
        // const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
        // webpackConfig.plugins.push(new BundleAnalyzerPlugin());
    }
}

module.exports = webpackConfig

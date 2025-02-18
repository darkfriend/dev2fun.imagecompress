const path = require('path')
const webpack = require('webpack')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')

const { VueLoaderPlugin } = require('vue-loader')
const { CleanWebpackPlugin } = require('clean-webpack-plugin')
const isDevServer = false

let environment = {}
switch (process.env.NODE_ENV) {
    case 'development':
        environment = require('./env/dev.env')
        break
    case 'production':
        environment = require('./env/prod.env')
        break
    case 'staging':
        environment = require('./env/staging.env')
        break
}

const PATHS = {
    src: path.join(__dirname, '../src'),
    srcJs: path.join(__dirname, '../src'),
    dist: path.join(__dirname, '../dist'),
    core: path.join(__dirname, '../'),
    svg: path.join(__dirname, '../src/assets/svg'),
    assets: 'assets/',
}

webpackConfig = {
    externals: {
        paths: PATHS,
    },
    entry: {
        main: path.join(PATHS.src, './main.ts'),
        // polyfill: '@babel/polyfill',
    },
    stats: {
        children: false,
        chunks: false,
        chunkModules: false,
        modules: false,
        reasons: false,
    },
    module: {
        rules: [
            {
                test: /\.(scss|css)$/,
                use: [
                    // Note: Only style-loader works for me !!!
                    // 'vue-style-loader',
                    // 'style-loader',
                    // MiniCssExtractPlugin.loader,
                    isDevServer
                        ? 'style-loader'
                        : {
                              loader: MiniCssExtractPlugin.loader,
                              options: {
                                  esModule: true,
                              },
                          },

                    { loader: 'css-loader' },
                    // {loader: 'css-loader', options: {importLoaders: 1}},
                    { loader: 'postcss-loader' },
                    { loader: 'sass-loader' },
                ],
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader',
                // include: [PATHS.src],
                options: {
                    loaders: {
                        // Since sass-loader (weirdly) has SCSS as its default parse mode, we map
                        // the "scss" and "sass" values for the lang attribute to the right configs here.
                        // other preprocessors should work out of the box, no loader config like this necessary.
                        scss: ['vue-style-loader', 'css-loader', 'sass-loader?indentedSyntax'],
                        sass: ['vue-style-loader', 'css-loader', 'sass-loader?indentedSyntax'],
                    },
                    // other vue-loader options go here
                },
            },
            {
                test: /\.ts$/,
                loader: 'ts-loader',
                options: {
                    appendTsSuffixTo: [/\.vue$/],
                    transpileOnly: true,
                },
            },
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/,
            },

            {
                test: /\.svg$/,
                type: 'asset',
                generator: {
                    filename: 'images/svg/[name][ext]',
                },
            },

            {
                test: /\.(png|jpe?g|gif|ico)$/,
                // exclude: path.resolve(__dirname, 'src'),
                type: 'asset/resource',
                // loader: 'file-loader',
                generator: {
                    filename: 'images/[name][ext]',
                },
            },
            {
                test: /\.(woff(2)?|ttf|eot|otf)(\?v=\d+\.\d+\.\d+)?$/,
                // exclude: path.resolve(__dirname, 'src'),
                type: 'asset/resource',
                // loader: 'file-loader',
                generator: {
                    filename: 'fonts/[name][ext]',
                },
            },
        ],
    },
    resolve: {
        symlinks: false,
        alias: {
            // 'vue$': 'vue/dist/vue.runtime.js',
            // 'vue$': 'vue/dist/vue.runtime.esm.js',
            // 'vue$': '@vue/runtime-dom',
            vue$: 'vue/dist/vue.esm-bundler.js',
            '@': path.join(__dirname, '../src'),
            '@svg': PATHS.svg,
            // 'bootstrap-vue$': 'bootstrap-vue/src/index.js',
            // vue: 'vue/dist/vue.esm.js',
            // 'moment': 'moment/src/moment' // ->  (gzip 87kb | parsed 385) to (gzip 118kb | parsed 604)
        },
        extensions: ['.*', '.ts', '.js', '.vue', '.json', '.scss', '.tsx'],
    },
    // devServer: {
    //     historyApiFallback: true,
    //     noInfo: true,
    //     overlay: true
    // },
    performance: {
        hints: false,
    },
    devtool: '#eval-source-map',
    plugins: [
        new CleanWebpackPlugin(),
        new VueLoaderPlugin(),
        new webpack.DefinePlugin({
            __PROJECT: JSON.stringify(process.env.PROJECT),
            __PROJECT_DIR: JSON.stringify(process.env.DIR),
            __PROJECT_TYPE: JSON.stringify(process.env.TYPE),
            __ENV: JSON.stringify(environment),
            'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV),
            'process.env.VUE_APP_I18N_FALLBACK_LOCALE': JSON.stringify(process.env.VUE_APP_I18N_FALLBACK_LOCALE),
            'process.env.BASE_URL': '/personal',
        }),
        new webpack.IgnorePlugin({
            resourceRegExp: /^\.\/locale$/,
            contextRegExp: /moment$/,
        }),
        new MiniCssExtractPlugin({
            filename: 'css/bundle.css',
            chunkFilename: 'css/[id].[contenthash].css',
        }),
        // new StatsWriterPlugin({
        //   filename: "stats.json" // Default
        // })
    ],
}
module.exports = webpackConfig

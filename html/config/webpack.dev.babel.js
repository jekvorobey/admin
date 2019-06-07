import webpack from 'webpack';
import path from 'path';
import WriteFilePlugin from 'write-file-webpack-plugin';
import PreloadWebpackPlugin from 'preload-webpack-plugin';

const port = 3000;

export default {
    output: {
        path: path.resolve(__dirname, '../build'),
        filename: 'scripts/[name].js',
        publicPath: '/build/',
        pathinfo: false,
    },
    devServer: {
        historyApiFallback: true,
        host: '0.0.0.0',
        port,
        public: `localhost:${port}`,
        publicPath: '/',
        open: true,
        hot: true,
        compress: true,
        overlay: true,
        stats: {
            assets: false,
            builtAt: false,
            entrypoints: false,
            hash: false,
            modules: false,
            version: false,
        },
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                include: [
                    path.resolve(__dirname, '../src'),
                    path.resolve(__dirname, '../node_modules/swiper'),
                    path.resolve(__dirname, '../node_modules/dom7'),
                    path.resolve(__dirname, '../node_modules/vue-for-range'),
                    path.resolve(__dirname, '../node_modules/resize-detector'),
                    path.resolve(__dirname, '../node_modules/vue-clamp'),
                    path.resolve(__dirname, '../node_modules/v-scroll-lock'),
                ],
                use: [
                    {
                        loader: 'cache-loader',
                        options: {
                            cacheDirectory: path.resolve(__dirname, '../node_modules/.cache/cache-loader'),
                        },
                    },
                    {
                        loader: 'babel-loader',
                        options: {
                            cacheDirectory: true,
                        },
                    },
                ],
            },
            {
                test: /\.css$/,
                include: path.resolve(__dirname, '../src'),
                use: [
                    // {
                    //     /* На бэке стили должны отделяться в отдельные файлы, чтобы указываться в списке зависимостей webpack-assets.json. */
                    //     loader: MiniCssExtractPlugin.loader,
                    //     options: {
                    //         /* publicPath нужен для указания основной директории относительно стилей. Т.к. стили лежат внутри styles, то выходим на один уровень вверх. Иначе такие зависимости, как шрифты, будут искать внутри styles. */
                    //         publicPath: '../',
                    //     },
                    // },
                    'style-loader',
                    {
                        loader: 'css-loader',
                        options: {
                            importLoaders: 1,
                            sourceMap: true,
                        },
                    },
                    {
                        loader: 'postcss-loader',
                        options: {
                            sourceMap: true,
                        },
                    },
                ],
            },
            {
                test: /\.css$/,
                include: path.resolve(__dirname, '../node_modules'),
                //use: [MiniCssExtractPlugin.loader, 'css-loader'],
                use: ['style-loader', 'css-loader'],
            },
            {
                test: /\.(jpe?g|png)$/,
                include: path.resolve(__dirname, '../src/images'),
                use: [
                    {
                        loader: 'url-loader',
                        options: {
                            name: 'images/[name].[ext]',
                            limit: 10 * 1024,
                        },
                    },
                ],
            },
            {
                test: /\.svg$/,
                include: path.resolve(__dirname, '../src/images'),
                exclude: path.resolve(__dirname, '../src/images/sprite'),
                loader: 'svg-url-loader',
                options: {
                    name: 'images/[name].[ext]',
                    limit: 10 * 1024,
                    noquotes: true,
                },
            },
            {
                test: /\.woff2?$/,
                include: path.resolve(__dirname, '../src/fonts'),
                use: [
                    {
                        loader: 'url-loader',
                        options: {
                            limit: 8192,
                            name: 'fonts/[name].[ext]',
                        },
                    },
                ],
            },
        ],
    },
    //devtool: 'source-map',
    plugins: [
        // new MiniCssExtractPlugin({
        //     /* При добавлении хэша перестанет работать Critical CSS. */
        //     filename: 'styles/[name].css',
        // }),
        //...generateHtmlPlugins('dev'),
        new PreloadWebpackPlugin({
            include: 'allAssets',
            fileWhitelist: [/fonts(\..*)?\.js/],
        }),
        new WriteFilePlugin({
            test: /^(?!.*(hot)).*/,
        }),
        new webpack.HotModuleReplacementPlugin(),
    ],
};

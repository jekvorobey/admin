import path from 'path';
import MiniCssExtractPlugin from 'mini-css-extract-plugin';
import TerserPlugin from 'terser-webpack-plugin';
import OptimizeCSSAssetsPlugin from 'optimize-css-assets-webpack-plugin';
import ImageminWebpWebpackPlugin from 'imagemin-webp-webpack-plugin';
import ImageminPlugin from 'imagemin-webpack-plugin';
import imageminMozJpeg from 'imagemin-mozjpeg';
import CompressionPlugin from 'compression-webpack-plugin';
import AssetsPlugin from 'assets-webpack-plugin';

import cleanOldAssets from './helpers/cleanOldAssets';

export default {
    output: {
        path: path.resolve(__dirname, '../../public/assets'),
        filename: 'scripts/[name].[chunkhash:4].js',
        publicPath: '/assets/',
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                /* Исходный код Swiper и Dom7 не транспайлится разработчиками,
                так что необходимо сделать это самим */
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
                exclude: path.resolve(__dirname, '../node_modules'),
                use: [
                    MiniCssExtractPlugin.loader,
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
                use: [MiniCssExtractPlugin.loader, 'css-loader'],
            },
            {
                test: /\.(jpe?g|png)$/,
                include: path.resolve(__dirname, '../src/images/responsive'),
                use: [
                    {
                        loader: 'cache-loader',
                        options: {
                            cacheDirectory: path.resolve(__dirname, '../node_modules/.cache/cache-loader'),
                        },
                    },
                    {
                        loader: 'responsive-loader',
                        options: {
                            outputPath: 'images',
                            name: '[name]-[width].[hash:4].[ext]',
                            min: 480,
                            max: 1200,
                            steps: 4,
                            quality: 100,
                            placeholder: true,
                        },
                    },
                ],
            },
            {
                test: /\.(jpe?g|png)$/,
                include: path.resolve(__dirname, '../src/images'),
                exclude: path.resolve(__dirname, '../src/images/responsive'),
                use: [
                    {
                        loader: 'cache-loader',
                        options: {
                            cacheDirectory: path.resolve(__dirname, '../node_modules/.cache/cache-loader'),
                        },
                    },
                    {
                        loader: 'url-loader',
                        options: {
                            name: 'images/[name].[hash:4].[ext]',
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
                    name: 'images/[name].[hash:4].[ext]',
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
                            name: 'fonts/[name].[hash:4].[ext]',
                        },
                    },
                ],
            },
        ],
    },
    devtool: 'source-map',
    optimization: {
        minimizer: [
            new TerserPlugin({
                cache: true,
                parallel: true,
                sourceMap: true,
                terserOptions: {
                    output: {
                        comments: false,
                        ascii_only: true,
                    },
                },
            }),
            new OptimizeCSSAssetsPlugin({
                cssProcessorOptions: {
                    map: {
                        annotation: true,
                    },
                },
            }),
        ],
        /* Настройки Bundle Splitting. */
        namedChunks: true,
        splitChunks: {
            chunks: 'all',
            cacheGroups: {
                vendors: {
                    test: /[/\\]node_modules[/\\]/,
                },
            },
        },
        // splitChunks: {
        //     /* Выделяются commons, vendor. Для кита отдельный бандл, невлияющий на сплиттинг. */
        //     cacheGroups: {
        //         commons: {
        //             chunks: chunk => chunk.name !== 'ui-kit',
        //             minChunks: 2,
        //             maxInitialRequests: 5,
        //             minSize: 0,
        //         },
        //         vendor: {
        //             test: /node_modules/,
        //             chunks: chunk => chunk.name !== 'ui-kit',
        //             priority: 10,
        //             enforce: true,
        //         },
        //     },
        // },
        runtimeChunk: true,
    },
    performance: {
        maxEntrypointSize: 1000000,
        maxAssetSize: 700000,
    },
    stats: {
        assets: false,
        builtAt: false,
        entrypoints: false,
        hash: false,
        modules: false,
        version: false,
    },
    plugins: [
        new MiniCssExtractPlugin({
            /* При добавлении хэша перестанет работать Critical CSS. */
            filename: 'styles/[name].[chunkhash:4].css',
        }),
        /* imagemin-webp-webpack-plugin используется для преобразования jpeg/png в webp-формат с сохранением исходных файлов.
        Внутри плагин использует imagemin-webp, так что настройки оптимизации прописываются здесь, а не в imagemin-webpack-plugin. */
        new ImageminWebpWebpackPlugin({
            config: [
                {
                    test: /\.(jpe?g|png)$/,
                    options: {
                        quality: 80,
                        method: 6,
                    },
                },
            ],
        }),
        new ImageminPlugin({
            /* Для jpeg используется mozJpeg. */
            jpegtran: null,
            /* Для png используется pngQuant. */
            optipng: null,
            /* gif не обрабатывается. */
            gifsicle: null,
            pngquant: {
                quality: '65-90',
                speed: 1,
            },
            plugins: [
                imageminMozJpeg({
                    quality: 75,
                }),
            ],
        }),
        new CompressionPlugin({
            test: /\.js$|\.css$/,
            filename: '[path].gz[query]',
            threshold: 8192,
            cache: true,
        }),
        new AssetsPlugin({
            /* Здесь указывается путь до места хранения манифеста. Пока падает в корень. */
            path: path.resolve(__dirname, '../../public/assets'),
            fullPath: false,
            entrypoints: true,
            includeAllFileTypes: false,
            fileTypes: ['js', 'css', 'gz'],
            prettyPrint: true,
        }),
        cleanOldAssets(),
    ],
};

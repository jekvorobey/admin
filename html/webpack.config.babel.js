import path from 'path';
import merge from 'webpack-merge';

import VueLoaderPlagin from 'vue-loader/lib/plugin';
import generateEntrypoints from './config/helpers/generateEntrypoints';
import loadPresets from './config/helpers/loadPresets';

import devConfig from './config/webpack.dev.babel';
import prodConfig from './config/webpack.prod.babel';

export default ({ mode, presets }) =>
    merge(
        {
            mode: mode === 'dev' ? 'development' : 'production',
            entry: generateEntrypoints(mode),
            module: {
                rules: [
                    {
                        test: /\.vue$/,
                        include: [path.resolve(__dirname, 'src'), path.resolve(__dirname, 'node_modules')],
                        use: ['vue-loader'],
                    },
                    {
                        test: /\.hbs$/,
                        include: path.resolve(__dirname, 'src'),
                        use: [
                            {
                                loader: 'cache-loader',
                                options: {
                                    cacheDirectory: path.resolve(__dirname, 'node_modules/.cache/cache-loader'),
                                },
                            },
                            {
                                loader: 'handlebars-loader',
                                options: {
                                    helperDirs: [path.resolve(__dirname, 'src/templates/helpers')],
                                    partialDirs: [path.resolve(__dirname, 'src/templates/partials')],
                                    inlineRequires: '/images/',
                                },
                            },
                        ],
                    },
                    {
                        test: /\.svg$/,
                        include: path.resolve(__dirname, 'src/images/sprite'),
                        loader: 'svg-sprite-loader',
                        options: {
                            symbolId: 'icon-[name]',
                        },
                    },
                    {
                        test: /\.png$/,
                        include: path.resolve(__dirname, 'src/images'),
                        loader: 'file-loader',
                        options: {
                            name: 'images/[name].[ext]',
                        },
                    }
                ],
            },
            resolve: {
                symlinks: false,
                alias: {
                    vue$: 'vue/dist/vue.esm.js',
                },
            },
            plugins: [new VueLoaderPlagin()],
        },
        mode === 'dev' ? devConfig : prodConfig,
        loadPresets(presets)
    );

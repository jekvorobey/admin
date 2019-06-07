import WebpackBundleAnalyzer from 'webpack-bundle-analyzer';

export default {
    plugins: [new WebpackBundleAnalyzer.BundleAnalyzerPlugin()],
};

const path = require('path');
const { BundleStatsWebpackPlugin } = require('bundle-stats-webpack-plugin');

module.exports = {
    mode: 'development', // Set the mode to development
    entry: './src/js/index.js', // Change this path if your entry file is located elsewhere
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'assets/js'),
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                },
            },
            {
                test: /\.scss$/,
                use: ['style-loader', 'css-loader', 'sass-loader'],
            },
        ],
    },
    plugins: [
        new BundleStatsWebpackPlugin({
            baseline: true,
            html: false,
            json: false,
            outDir: '../',
            stats: {
                assets: true,
                chunks: true,
                modules: true,
                builtAt: true,
                hash: true,
                moduleAssets: false,
            },
        }),
    ],
    devtool: 'eval-source-map', // Add the devtool option for better debugging experience
    stats: {
        preset: 'summary',
        assets: true,
        chunks: true,
        modules: true,
        builtAt: true,
        hash: true,
    },
};

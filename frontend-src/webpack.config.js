const path = require('path');
const { BundleStatsWebpackPlugin } = require('bundle-stats-webpack-plugin');

module.exports = {
    mode: 'development', // Set the mode to development
    entry: './src/js/index.js', // Entry point for React app
    output: {
        filename: 'index.js',
        path: path.resolve(__dirname, '../frontend-public/assets/js'), // Output directory for bundled JS
    },
    // Resolve configuration for React and JSX
    resolve: {
        extensions: ['.js', '.jsx'],
    },
    module: {
        rules: [
            // Rule for handling JavaScript and JSX files
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    // Babel options for React
                    options: {
                        presets: ['@babel/preset-env', '@babel/preset-react'] // React preset
                    }
                },
            },
            // Rule for handling SCSS files
            {
                test: /\.scss$/,
                use: ['style-loader', 'css-loader', 'sass-loader'],
            },
            // Rule for handling images and other assets
            {
                test: /\.(png|jpe?g|gif|svg|ico)$/i,
                type: 'asset/resource', // Use asset/resource for images.
                generator: {
                    filename: '../images/[name][ext]' // Output images to assets/images
                }
            },
            // Rule for handling fonts
            {
                test: /\.(woff|woff2|eot|ttf|otf)$/i,
                type: 'asset/resource',
                generator: {
                    filename: '../fonts/[name][ext]' // Output fonts to assets/fonts
                }
            }
        ],
    },
    plugins: [
        new BundleStatsWebpackPlugin({
            baseline: true,
            html: false,
            json: false,
            outDir: '../../',
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

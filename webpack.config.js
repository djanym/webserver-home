const path = require('path');

module.exports = {
    mode: 'development', // Set the mode to development
    entry: './src/js/index.js', // Change this path if your entry file is located elsewhere
    output: {
        filename: 'index.js',
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
        ],
    },
    devtool: 'eval-source-map', // Add the devtool option for better debugging experience
};

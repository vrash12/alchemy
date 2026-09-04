const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');

module.exports = {
  entry: './src/index.tsx',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'AlchemyResourcesPlugin.js',
    clean: true,
  },
  resolve: {
    extensions: ['.tsx', '.ts', '.js'],
  },
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        exclude: /node_modules/,
        use: 'ts-loader',
      },
    ],
  },
  plugins: [
    new CopyPlugin({
      patterns: [{ from: 'manifest.json', to: 'manifest.json' }],
    }),
  ],
  devServer: {
    static: path.resolve(__dirname, 'dist'),
    port: 4701,
    headers: {
      'Access-Control-Allow-Origin': '*',
    },
  },
};

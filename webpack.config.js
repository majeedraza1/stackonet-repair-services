const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

const config = require('./config.json');

let plugins = [];
let entryPoints = {
	admin: [
		'./assets/src/admin/main.js',
		'./assets/scss/admin.scss'
	],
	frontend: [
		'./assets/src/frontend/main.js',
		'./assets/scss/frontend.scss'
	],
};

plugins.push(new MiniCssExtractPlugin({
	filename: "../css/[name].css"
}));

plugins.push(new BrowserSyncPlugin({
	proxy: config.proxyURL
}));

plugins.push(new VueLoaderPlugin());

module.exports = (env, argv) => ({
	"entry": entryPoints,
	"output": {
		"path": path.resolve(__dirname, 'assets/js'),
		"filename": argv.mode === 'production' ? '[name].min.js' : '[name].js'
	},
	"devtool": argv.mode === 'production' ? false : 'source-map',
	"module": {
		"rules": [
			{
				"test": /\.js$/,
				"exclude": /node_modules/,
				"use": {
					"loader": "babel-loader",
					"options": {
						presets: ['@babel/preset-env']
					}
				}
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader'
			},
			{
				"test": /\.scss$/,
				"use": [
					"vue-style-loader",
					"style-loader",
					MiniCssExtractPlugin.loader,
					"css-loader",
					"postcss-loader",
					"sass-loader"
				]
			}
		]
	},
	optimization: {
		minimizer: [
			new UglifyJsPlugin({cache: true, parallel: true, sourceMap: false}),
			new OptimizeCSSAssetsPlugin({})
		]
	},
	resolve: {
		alias: {
			'vue$': 'vue/dist/vue.esm.js'
		},
		extensions: ['*', '.js', '.vue', '.json']
	},
	"plugins": plugins
});

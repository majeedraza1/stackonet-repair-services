const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserPlugin = require('terser-webpack-plugin');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const autoprefixer = require('autoprefixer');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const package = require('./package.json');
const config = require('./config.json');

let plugins = [];
let entryPoints = {
	// vendors: Object.keys(package.dependencies),
	admin: [
		'./assets/src/admin/main.js',
		'./assets/src/admin/become-a-tech/main.js',
		'./assets/src/admin/rent-a-center/main.js',
		'./assets/src/admin/survey/main.js',
		'./assets/src/admin/carrier-store/main.js',
		'./assets/src/admin/spot-appointment/main.js',
		'./assets/src/admin/support-ticket/main.js',
		'./assets/src/scss/admin.scss'
	],
	frontend: [
		'./assets/src/frontend/main.js',
		'./assets/src/frontend/testimonial.js',
		'./assets/src/frontend/pricing/main.js',
		'./assets/src/frontend/reschedule/main.js',
		'./assets/src/frontend/manager-registration/main.js',
		'./assets/src/frontend/become-a-tech/main.js',
		'./assets/src/frontend/support-ticket/main.js',
		'./assets/src/scss/frontend.scss',
	],
	'payment-form': [
		'./assets/src/frontend/payment-form/main.js',
	],
	'my-account': [
		'./assets/src/frontend/my-account/main.js',
		'./assets/src/scss/my-account.scss',
	],
	'rent-center': [
		'./assets/src/frontend/rent-a-center/main.js',
		'./assets/src/scss/frontend-rent-center.scss',
	],
	'frontend-dashboard': [
		'./assets/src/frontend/dashboard/main.js',
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
		"filename": '[name].js'
	},
	"devtool": argv.mode === 'production' ? false : 'eval-source-map',
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
					{
						loader: "postcss-loader",
						options: {
							plugins: () => [autoprefixer()],
						},
					},
					{
						loader: "sass-loader",
						options: {
							includePaths: ['./node_modules'],
						},
					}
				]
			},
			{
				test: /\.(png|je?pg|gif|svg|eot|ttf|woff|woff2)$/,
				use: [
					{
						loader: 'file-loader',
						options: {},
					},
				],
			},
		]
	},
	optimization: {
		minimizer: [
			new TerserPlugin(),
			new OptimizeCSSAssetsPlugin({})
		],
		splitChunks: {
			cacheGroups: {
				vendors: {
					test: /[\\/]node_modules[\\/]/,
					name: 'vendors',
					enforce: true,
					chunks: 'all'
				},
				mdl: {
					test: /[\\/]material-design-lite[\\/]/,
					name: 'mdl',
					enforce: true,
					chunks: 'all'
				},
			}
		}
	},
	resolve: {
		alias: {
			'vue$': 'vue/dist/vue.esm.js',
			'@': path.resolve('./assets/src/'),
		},
		modules: [
			path.resolve('./node_modules'),
			path.resolve(path.join(__dirname, 'assets/src/')),
		],
		extensions: ['*', '.js', '.vue', '.json']
	},
	"plugins": plugins
});

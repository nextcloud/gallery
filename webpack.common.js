const path = require('path')
const { VueLoaderPlugin } = require('vue-loader')
const StyleLintPlugin = require('stylelint-webpack-plugin')

// sass plugin to implement js configs into scss
const sass = require('node-sass')
const sassUtils = require('node-sass-utils')(sass)
const sassVars = require('./src/assets/grid-sizes')

const packageJson = require('./package.json')
const appName = packageJson.name

module.exports = {
	entry: path.join(__dirname, 'src', 'main.js'),
	output: {
		path: path.resolve(__dirname, './js'),
		publicPath: '/js/',
		filename: `${appName}.js`,
		chunkFilename: 'chunks/[name]-[hash].js'
	},
	module: {
		rules: [
			{
				test: /\.css$/,
				use: ['vue-style-loader', 'css-loader', 'postcss-loader']
			},
			{
				test: /\.scss$/,
				use: [
					'vue-style-loader',
					'css-loader',
					'postcss-loader',
					{
						loader: 'sass-loader',
						options: {
							functions: {
								'get($keys)': function(keys) {
									keys = keys.getValue().split('.');
									let result = sassVars
									for (let i = 0; i < keys.length; i++) {
										result = result[keys[i]]
									}
									result = sassUtils.castToSass(result)
									console.log(result)
									return result
								}
							}
						}
					}
				]
			},
			{
				test: /\.(js|vue)$/,
				use: 'eslint-loader',
				exclude: /node_modules/,
				enforce: 'pre'
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader',
				exclude: /node_modules/
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: /node_modules(?!(\/|\\)(hot-patcher|webdav)(\/|\\))/
			},
			{
				test: /\.svg$/,
				// illustrations
				loader: 'svg-inline-loader'
			}
		]
	},
	plugins: [new VueLoaderPlugin(), new StyleLintPlugin()],
	resolve: {
		extensions: ['*', '.js', '.vue'],
		symlinks: false
	}
}

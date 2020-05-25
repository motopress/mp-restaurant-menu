const path = require('path');

const config = {
	entry: {
		'./media/js/mce-mp-restaurant-menu-plugin': './media/js/mce-mp-restaurant-menu-plugin.js',
		'./media/js/mp-restaurant-menu': './media/js/mp-restaurant-menu.js',
	},
	output: {
		path: path.resolve(__dirname),
		filename: '[name].min.js',
	},
	module: {
		rules: [
		  {
			test: /\.js$/,
			exclude: /node_modules/,
			use: {
			  loader: "babel-loader"
			},
		  }
		]
	},

	externals: {
		'react'    : 'React',
		'react-dom': 'ReactDOM',
		'lodash'   : 'lodash'
	},
};

module.exports = config;

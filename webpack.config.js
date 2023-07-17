const { merge } = require('webpack-merge')
const path = require('path');

const nextcloudWebpackConfig = require('@nextcloud/webpack-vue-config')

module.exports = merge(nextcloudWebpackConfig, {
	optimization: process.env.NODE_ENV === 'production'
		? { chunkIds: 'deterministic' }
		: {},
	devServer: {
		port: 3000, // use any port suitable for your configuration
		host: '0.0.0.0', // to accept connections from outside container
	},
	module: {
		rules: [
			{
				resourceQuery: /raw/,
				type: 'asset/source',
			},
		],
	},
	resolve: {
		alias: {
			apps: path.resolve(__dirname, '../../apps'),
			variables: path.resolve(__dirname, '../../core/css/variables.scss'),
			functions: path.resolve(__dirname, '../../core/css/functions.scss')
		},
		fallback: {
			apps: path.resolve(__dirname, '../../apps-extra'),
		},
	}
})

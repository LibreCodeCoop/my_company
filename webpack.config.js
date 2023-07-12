const { merge } = require('webpack-merge')

const nextcloudWebpackConfig = require('@nextcloud/webpack-vue-config')

module.exports = merge(nextcloudWebpackConfig, {
	optimization: process.env.NODE_ENV === 'production'
		? { chunkIds: 'deterministic' }
		: {},
	devServer: {
		port: 3000, // use any port suitable for your configuration
		host: '0.0.0.0', // to accept connections from outside container
	}
})

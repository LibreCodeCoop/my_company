const { merge } = require('webpack-merge')

const nextcloudWebpackConfig = require('@nextcloud/webpack-vue-config')

const commonWebpackConfig = require('./webpack.common.config.js')

module.exports = merge(nextcloudWebpackConfig, commonWebpackConfig)

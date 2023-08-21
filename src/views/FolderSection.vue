<template>
	<iframe class="form"
		:src="iframeUrl" />
</template>

<script>
import { loadState } from '@nextcloud/initial-state'

export default {
	name: 'FolderSection',
	props: {
		url: {
			type: String,
			default: '',
		},
	},
	computed: {
		iframeUrl() {
			if (this.url) {
				return this.url
			}
			const menu = loadState('my_company', 'menu-sections', [])
			return menu.filter(item => item.id == this.$route.params.id)[0].url
		},
	},
}
</script>

<style lang="scss" scoped>
.form {
	width: 100%;
	height: 100%;
}
</style>

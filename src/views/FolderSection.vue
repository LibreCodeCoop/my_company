<template>
	<iframe class="form"
		:src="url" />
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
	mounted() {
		if (!this.url) {
			const menu = loadState('my_company', 'menu-sections', [])
			this.url = menu.filter(item => item.id === this.$route.params.id)[0].url
		}
	},
}
</script>

<style lang="scss" scoped>
.form {
	width: 100%;
	height: 100%;
}
</style>

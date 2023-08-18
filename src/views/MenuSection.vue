<template>
	<iframe class="form"
		:src="url" />
</template>

<script>
export default {
	name: 'MenuSection',
	props: {
		url: {
			type: String,
			default: '',
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

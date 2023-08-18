<template>
	<iframe class="form"
		:src="url" />
</template>

<script>
export default {
	name: 'Section',
	props: {
		url: {
			type: String,
			default: '',
		},
	},
	mounted() {
		console.log(this.url)
	},
}
</script>

<style lang="scss" scoped>
.form {
	width: 100%;
	height: 100%;
}
</style>

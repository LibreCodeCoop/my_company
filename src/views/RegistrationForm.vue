<template>
	<iframe class="form"
		:src="src" />
</template>

<script>

import { generateUrl } from '@nextcloud/router'

export default {
	name: 'RegistrationForm',
	data() {
		return {
			src: null,
		}
	},
	mounted() {
		this.src = generateUrl('/apps/my_company/registration/embedded-form-view')

		window.addEventListener('message', this.formSaved)
	},
	unmounted() {
		window.removeEventListener('message', this.formSaved)
	},
	methods: {
		formSaved(event) {
			if (event.origin !== window.location.protocol + '//' + window.location.host) {
				return
			}

			if (event.data.type !== 'form-saved') {

			}
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

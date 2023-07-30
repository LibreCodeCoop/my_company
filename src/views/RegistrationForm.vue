<template>
	<iframe class="form"
		:src="src" />
</template>

<script>

import axios from '@nextcloud/axios'
import { generateUrl, generateOcsUrl } from '@nextcloud/router'

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
				return
			}
			axios.get(generateOcsUrl('apps/my_company/api/v1/profile'))
			.then(({ data }) => {
				this.$router.push({
					name: 'registration',
					params: { signUuid: data.signUuid },
				})
			})
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

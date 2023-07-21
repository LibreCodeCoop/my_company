<template>
	<div>
		<NcEmptyContent :title="t('my_company', 'Registration')">
			<template #icon>
				<PlaylistPlus />
			</template>
		</NcEmptyContent>
		<div class="flex">
			<div class="list-items">
				<NcButton :wide="true"
					@click="downloadFileEmpty(registrationFormFileEmpty)">
					<template #icon>
						<Download />
					</template>
					{{ t('my_company', 'Download the form') }}
				</NcButton>
			</div>
			<div class="list-items">
				<NcButton :wide="true"
					@click="uploadPdfFile()">
					<template #icon>
						<Upload />
					</template>
					{{ t('my_company', 'Upload form as PDF') }}
				</NcButton>
			</div>
			<div v-if="registrationFormFileExists && !registrationFormSigned" class="list-items">
				<NcButton :wide="true"
					@click="signForm()">
					<template #icon>
						<FileSign />
					</template>
					{{ t('my_company', 'Sign your form') }}
				</NcButton>
			</div>
		</div>
	</div>
</template>

<script>

import axios from '@nextcloud/axios'
import { loadState } from '@nextcloud/initial-state'
import { generateOcsUrl } from '@nextcloud/router'

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'

import Download from 'vue-material-design-icons/Download.vue'
import FileSign from 'vue-material-design-icons/FileSign.vue'
import PlaylistPlus from 'vue-material-design-icons/PlaylistPlus.vue'
import Upload from 'vue-material-design-icons/Upload.vue'

export default {
	name: 'RegistrationForm',
	components: {
		NcButton,
		NcEmptyContent,
		Download,
		FileSign,
		Upload,
		PlaylistPlus,
	},
	data() {
		console.log(loadState('my_company', 'registration-form-file-exists'))
		return {
			registrationFormSigned: loadState('my_company', 'registration-form-signed'),
			registrationFormFileEmpty: loadState('my_company', 'registration-form-file-empty'),
			registrationFormFileExists: loadState('my_company', 'registration-form-file-exists'),
		}
	},
	methods: {
		downloadFileEmpty(registrationFormFileEmpty) {
			try {
				const link = document.createElement('a')
				link.setAttribute('download', registrationFormFileEmpty.name)
				link.setAttribute('href', registrationFormFileEmpty.url)
				document.body.appendChild(link)
				link.click()
				document.body.removeChild(link)
			} catch (e) {
				console.error(e)
			}
		},
		signForm() {
			const url = generateOcsUrl('/apps/my_company/api/v1/registration/sign')
			axios.post(url)
		},
		async upload(file) {
			const formData = new FormData()
			formData.append('file', file)
			const url = generateOcsUrl('/apps/my_company/api/v1/registration/upload-pdf')
			axios.post(url, formData, {
				headers: {
					'Content-Type': 'multipart/form-data',
				},
			})
		},
		uploadPdfFile() {
			const input = document.createElement('input')
			input.accept = 'application/pdf'
			input.type = 'file'

			input.onchange = async (ev) => {
				const file = ev.target.files[0]

				if (file) {
					this.upload(file)
				}

				input.remove()
			}

			input.click()
		},
	},
}
</script>

<style lang="scss" scoped>
.flex {
	display: flex;
	flex-wrap: wrap;
	position: relative;
	justify-content: center;
	align-items: center;
	max-width: 220px;
	margin: 0 auto;
}
.list-items {
	width: 100%;
	flex: none;
	display: flex;
	justify-content: center;
	align-items: center;
	&+ .list-items {
		margin-top: 10px;
	}
}
</style>

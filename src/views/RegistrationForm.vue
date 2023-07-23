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
					@click="downloadFile(registrationFormFileEmpty)">
					<template #icon>
						<Download />
					</template>
					{{ t('my_company', 'Download the blank form') }}
				</NcButton>
			</div>
			<div class="list-items">
				<NcButton :wide="true"
					@click="uploadPdfFile()">
					<template #icon>
						<NcLoadingIcon v-if="uploading"/>
						<FileSign v-else/>
					</template>
					<template #default v-if="registrationFormFileExists && !registrationFormSigned">{{ t('my_company', 'Replace the uploaded form') }}</template>
					<template #default v-else-if="registrationFormFileExists && registrationFormSigned">{{ t('my_company', 'Replace the signed form') }}</template>
					<template #default v-else>{{ t('my_company', 'Upload as PDF file') }}</template>
				</NcButton>
			</div>
			<NcNoteCard v-if="uploadErrorMessage"
				type="error">
				{{ uploadErrorMessage }}
			</NcNoteCard>
			<div v-if="registrationFormFileExists && !registrationFormSigned" class="list-items">
				<NcButton :wide="true"
					@click="signForm()">
					<template #icon>
						<NcLoadingIcon v-if="signing"/>
						<FileSign v-else/>
					</template>
					{{ t('my_company', 'Sign your form') }}
				</NcButton>
			</div>
			<div v-if="registrationFormSigned" class="list-items">
				<NcButton :wide="true"
					@click="viewSigned()">
					<template #icon>
						<Certificate />
					</template>
					{{ t('my_company', 'View signed form') }}
				</NcButton>
			</div>
			<NcNoteCard v-if="registrationFormSigned"
				type="warning">
				{{ t('my_company', 'Document already signed. Wait to be approved. If you want to replace the signed document, send a new PDF file.') }}
			</NcNoteCard>
		</div>
	</div>
</template>

<script>

import axios from '@nextcloud/axios'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl, generateOcsUrl } from '@nextcloud/router'

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import NcLoadingIcon from '@nextcloud/vue/dist/Components/NcLoadingIcon.js'
import NcNoteCard from '@nextcloud/vue/dist/Components/NcNoteCard.js'

import Certificate from 'vue-material-design-icons/Certificate.vue'
import Download from 'vue-material-design-icons/Download.vue'
import FileSign from 'vue-material-design-icons/FileSign.vue'
import PlaylistPlus from 'vue-material-design-icons/PlaylistPlus.vue'
import Upload from 'vue-material-design-icons/Upload.vue'

export default {
	name: 'RegistrationForm',
	components: {
		NcButton,
		NcEmptyContent,
		NcLoadingIcon,
		NcNoteCard,
		Certificate,
		Download,
		FileSign,
		Upload,
		PlaylistPlus,
	},
	data() {
		return {
			registrationFormSigned: loadState('my_company', 'registration-form-signed'),
			registrationFormFileEmpty: loadState('my_company', 'registration-form-file-empty'),
			registrationFormFileExists: loadState('my_company', 'registration-form-file-exists'),
			signing: false,
			uploading: false,
			uploadErrorMessage: '',
		}
	},
	methods: {
		downloadFile(registrationFormFileEmpty) {
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
			this.signing = true
			axios.post(url).then(() => {
				this.registrationFormSigned = true
				this.signing = false
			})
		},
		viewSigned() {
			window.location.href = generateUrl(
				'/apps/libresign/p/validation/' +
				this.registrationFormSigned +
				'?path=' + btoa('/apps/my_company/registration-form')
			)
		},
		async upload(file) {
			this.uploading = true
			this.registrationFormFileExists = false
			this.uploadErrorMessage = ''
			const formData = new FormData()
			formData.append('file', file)
			const url = generateOcsUrl('/apps/my_company/api/v1/registration/upload-pdf')
			axios.post(url, formData, {
				headers: {
					'Content-Type': 'multipart/form-data',
				},
			}).then(() => {
				this.registrationFormSigned = false
				this.registrationFormFileExists = true
			}).catch(error => {
				this.uploadErrorMessage = error.response.data.message
			}).finally(() => {
				this.uploading = false
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
	max-width: 400px;
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

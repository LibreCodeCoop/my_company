<template>
	<div>
		<NcEmptyContent :title="t('my_company', 'Registration')">
			<template #icon>
				<PlaylistPlus />
			</template>
		</NcEmptyContent>
		<div class="flex">
			<div class="list-items">
				<NcButton v-if="!approved"
					:wide="true"
					@click="fillForm()">
					<template #icon>
						<FileDocument />
					</template>
					<template v-if="!formFilled" #default>
						{{ t('my_company', 'Fill your registration form') }}
					</template>
					<template v-else #default>
						{{ t('my_company', 'Fill registration form with new data') }}
					</template>
				</NcButton>
				<NcButton v-if="approved || signUuid"
					:wide="true"
					@click="viewSigned()">
					<template #icon>
						<FileDocumentCheck />
					</template>
					{{ t('my_company', 'View your registration data') }}
				</NcButton>
			</div>
			<NcNoteCard v-if="formFilled && !approved"
				type="warning">
				{{ t('my_company', 'Data saved. Wait to be approved. If you want to replace your data, fill again the registration form.') }}
			</NcNoteCard>
		</div>
	</div>
</template>

<script>

import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import NcLoadingIcon from '@nextcloud/vue/dist/Components/NcLoadingIcon.js'
import NcNoteCard from '@nextcloud/vue/dist/Components/NcNoteCard.js'

import FileDocument from 'vue-material-design-icons/FileDocument.vue'
import FileDocumentEdit from 'vue-material-design-icons/FileDocumentEdit.vue'
import FileDocumentCheck from 'vue-material-design-icons/FileDocumentCheck.vue'
import PlaylistPlus from 'vue-material-design-icons/PlaylistPlus.vue'

export default {
	name: 'Registration',
	components: {
		NcButton,
		NcEmptyContent,
		NcLoadingIcon,
		NcNoteCard,
		FileDocument,
		FileDocumentEdit,
		FileDocumentCheck,
		PlaylistPlus,
	},
	data() {
		return {
			formFilled: loadState('my_company', 'registration-form-filled', false),
			signUuid: loadState('my_company', 'registration-form-sign-uuid', ''),
			approved: loadState('my_company', 'registration-approved', false),
			signing: false,
			uploading: false,
			uploadErrorMessage: '',
			signErrorMessage: '',
		}
	},
	methods: {
		async fillForm() {
			await this.$router.push({
				name: 'registration-form',
			})
		},
		viewSigned() {
			window.location.href = generateUrl(
				'/apps/libresign/p/validation/'
				+ this.signUuid
				+ '?path=' + btoa('/apps/my_company'),
			)
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
	display: gid;
	justify-content: center;
	align-items: center;
	&+ .list-items {
		margin-top: 20px;
	}
}
.button-vue--wide {
	margin-top: 1rem;
}
</style>

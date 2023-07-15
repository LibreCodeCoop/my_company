<template>
	<div>
		<div id="personal-settings">
			<h2 class="hidden-visually">
				{{ t('my_company', 'Security') }}
			</h2>
			<PasswordSection />
			<AuthTokenSection :tokens="tokens"
				:can-create-token="canCreateToken" />
		</div>
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'

// eslint-disable-next-line n/no-missing-import, import/no-unresolved
import AuthTokenSection from 'apps/settings/src/components/AuthTokenSection.vue'
// eslint-disable-next-line n/no-missing-import, import/no-unresolved
import PasswordSection from 'apps/settings/src/components/PasswordSection.vue'

export default {
	name: 'Security',
	components: {
		PasswordSection,
		AuthTokenSection,
	},

	data() {
		return {
			tokens: loadState('settings', 'app_tokens'),
			canCreateToken: loadState('settings', 'can_create_app_token'),
		}
	},
}
</script>

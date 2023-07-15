<template>
	<div>
		<div id="personal-settings">
			<h2 class="hidden-visually">{{ t('my_company', 'Security') }}</h2>
			<PasswordSection />
			<AuthTokenSection
				:tokens="tokens"
				:canCreateToken="canCreateToken"
				/>
		</div>
	</div>
</template>

<script>
import PasswordSection from 'apps/settings/src/components/PasswordSection.vue'
import AuthTokenSection from 'apps/settings/src/components/AuthTokenSection.vue'

import { loadState } from '@nextcloud/initial-state'

export default {
	name: 'Profile',

	data() {
		return {
			tokens: loadState('settings', 'app_tokens'),
			canCreateToken: loadState('settings', 'can_create_app_token'),
		}
	},
	components: {
		PasswordSection,
		AuthTokenSection,
	},
}
</script>

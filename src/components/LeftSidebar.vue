<template>
	<NcAppNavigation :aria-label="t('my_company', 'Main menu')">
		<template #list>
			<NcAppNavigationItem :to="{name: 'home'}"
				v-if="approved"
				:title="t('my_company', 'Home')"
				icon="icon-home"
				:exact="true" />
			<NcAppNavigationItem :to="{name: 'registration-form'}"
				:title="t('my_company', 'Registration form')">
				<template #icon>
					<PlaylistPlus />
				</template>
			</NcAppNavigationItem>
		</template>
	</NcAppNavigation>
</template>

<script>
import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation.js'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js'
import { loadState } from '@nextcloud/initial-state'

import PlaylistPlus from 'vue-material-design-icons/PlaylistPlus.vue'

export default {
	name: 'LeftSidebar',
	components: {
		NcAppNavigation,
		NcAppNavigationItem,
		PlaylistPlus,
	},
	data() {
		return {
			approved: loadState('my_company', 'approved', false),
		}
	},
}
</script>

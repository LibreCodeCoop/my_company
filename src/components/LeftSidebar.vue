<template>
	<NcAppNavigation :aria-label="t('my_company', 'Main menu')">
		<template #list>
			<NcAppNavigationItem v-if="approved"
				:to="{name: 'home'}"
				:title="t('my_company', 'Home')"
				icon="icon-home"
				:exact="true" />
			<NcAppNavigationItem :to="{name: 'registration'}"
				:title="t('my_company', 'Registration form')"
				:exact="true">
				<template #icon>
					<PlaylistPlus />
				</template>
			</NcAppNavigationItem>

			<template v-if="approved">
				<NcAppNavigationItem v-for="section in sections"
					:to="{ name: 'section', params: {url: section.url, id: section.id} }"
					:key="section.id"
					:title="section.name">
					<template #icon v-if="section.icon">
						<NcIconSvgWrapper :svg="section.icon" />
					</template>
					<template #icon v-else>
						<PlaylistPlus />
					</template>
				</NcAppNavigationItem>
			</template>
		</template>
	</NcAppNavigation>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'

import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation.js'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js'
import NcIconSvgWrapper from '@nextcloud/vue/dist/Components/NcIconSvgWrapper.js'

import PlaylistPlus from 'vue-material-design-icons/PlaylistPlus.vue'


export default {
	name: 'LeftSidebar',
	components: {
		NcAppNavigation,
		NcAppNavigationItem,
		NcIconSvgWrapper,
		PlaylistPlus,
	},
	data() {
		return {
			approved: loadState('my_company', 'registration-approved', false),
			sections: loadState('my_company', 'menu-sections', []),
		}
	},
}
</script>

<template>
	<NcAppNavigation :aria-label="t('my_company', 'Main menu')">
		<template #list>
			<NcAppNavigationItem :to="{name: 'home'}"
				:title="t('my_company', 'Home')"
				icon="icon-home"
				:exact="true" />
			<NcAppNavigationItem :to="{name: 'notifications'}"
				:title="t('my_company', 'Notifications')"
				:exact="true">
				<NcIconSvgWrapper slot="icon" :svg="iconNotifications" />
			</NcAppNavigationItem>
		</template>
	</NcAppNavigation>
</template>

<script>
// eslint-disable-next-line n/no-missing-import, import/no-unresolved
import iconNotifications from 'apps/announcementcenter/img/announcementcenter.svg?raw'

import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation.js'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js'
import NcIconSvgWrapper from '@nextcloud/vue/dist/Components/NcIconSvgWrapper.js'

export default {
	name: 'LeftSidebar',
	components: {
		NcAppNavigation,
		NcAppNavigationItem,
		NcIconSvgWrapper,
	},
	data() {
		return {
			iconNotifications,
		}
	},
}
</script>

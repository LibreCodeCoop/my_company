<template>
	<NcAppNavigation :aria-label="t('my_company', 'Main menu')">
		<template #list>
			<NcAppNavigationItem
				:to="{name: 'home'}"
				:title="t('my_company', 'Home')"
				icon="icon-home"
				:exact="true"
				/>
			<NcAppNavigationItem
				:to="{name: 'profile'}"
				:title="t('my_company', 'Profile')"
				icon="icon-user"
				/>
		</template>
	</NcAppNavigation>
</template>

<script>
import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation.js'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js'

export default {
	name: 'LeftSidebar',
	components: {
		NcAppNavigation,
		NcAppNavigationItem,
	},
}
</script>

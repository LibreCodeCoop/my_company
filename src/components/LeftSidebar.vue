<template>
	<NcAppNavigation :aria-label="t('my_company', 'Main menu')">
		<NcAppNavigationItem
			:title="t('my_company', 'Profile')"
			icon="icon-user"
			/>
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
	}
}
</script>

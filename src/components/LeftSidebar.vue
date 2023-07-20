<template>
	<NcAppNavigation :aria-label="t('my_company', 'Main menu')">
		<template #list>
			<NcAppNavigationItem :to="{name: 'home'}"
				:title="t('my_company', 'Home')"
				icon="icon-home"
				:exact="true" />
			<NcAppNavigationItem :to="{name: 'payments'}"
				:title="t('my_company', 'Payments')"
				:exact="true">
				<NcIconSvgWrapper slot="icon" :svg="iconPaymentsRaw" />
			</NcAppNavigationItem>
		</template>
	</NcAppNavigation>
</template>

<script>
import NcAppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation.js'
import NcAppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem.js'
import NcIconSvgWrapper from '@nextcloud/vue/dist/Components/NcIconSvgWrapper.js'

// eslint-disable-next-line import/no-unresolved
import iconPaymentsRaw from '../../img/payments.svg?raw'

export default {
	name: 'LeftSidebar',
	components: {
		NcAppNavigation,
		NcAppNavigationItem,
		NcIconSvgWrapper,
	},
	data() {
		return {
			iconPaymentsRaw,
		}
	},
}
</script>

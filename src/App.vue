<template>
	<NcContent app-name="my_company">
		<LeftSidebar />
		<NcAppContent>
			<router-view />
		</NcAppContent>
	</NcContent>
</template>

<script>
import NcAppContent from '@nextcloud/vue/dist/Components/NcAppContent.js'
import NcContent from '@nextcloud/vue/dist/Components/NcContent.js'

import LeftSidebar from './components/LeftSidebar.vue'

export default {
	name: 'App',
	components: {
		LeftSidebar,
		NcAppContent,
		NcContent,
	},
}
</script>

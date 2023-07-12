<template>
	<NcContent app-name="my_company">
		<router-view />
	</NcContent>
</template>

<script>
import NcContent from '@nextcloud/vue/dist/Components/NcContent.js'

export default {
	name: 'App',
	components: {
		NcContent,
	},
}
</script>

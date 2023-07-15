<template>
	<div>
		<div id="personal-settings">
			<h2 class="hidden-visually">{{ t('my_company', 'Security') }}</h2>
			<PasswordSection />
		</div>
	</div>
</template>

<script>
import PasswordSection from 'apps/settings/src/components/PasswordSection.vue'

export default {
	name: 'Profile',

	components: {
		PasswordSection,
	},
}
</script>

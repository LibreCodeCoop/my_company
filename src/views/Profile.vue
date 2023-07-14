<template>
	<div>
		<div id="personal-settings">
			<AvatarSection />
			<div class="personal-settings-setting-box">
				<DisplayNameSection />
			</div>
			<div class="personal-settings-setting-box">
				<EmailSection />
			</div>
		</div>
	</div>
</template>

<script>
import AvatarSection from 'settings/components/PersonalInfo/AvatarSection.vue'
import DisplayNameSection from 'settings/components/PersonalInfo/DisplayNameSection.vue'
import EmailSection from 'settings/components/PersonalInfo/EmailSection/EmailSection.vue'

export default {
	name: 'Profile',

	components: {
		AvatarSection,
		DisplayNameSection,
		EmailSection,
	},
}
</script>

<style lang="scss" scoped>
@import 'settingsCss/settings.scss';
</style>

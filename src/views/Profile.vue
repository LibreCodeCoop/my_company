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
			<div class="personal-settings-setting-box">
				<PhoneSection />
			</div>
			<div class="personal-settings-setting-box">
				<BiographySection />
			</div>
		</div>
	</div>
</template>

<script>
import AvatarSection from 'settings/components/PersonalInfo/AvatarSection.vue'
import DisplayNameSection from 'settings/components/PersonalInfo/DisplayNameSection.vue'
import EmailSection from 'settings/components/PersonalInfo/EmailSection/EmailSection.vue'
import PhoneSection from 'settings/components/PersonalInfo/PhoneSection.vue'
import BiographySection from 'settings/components/PersonalInfo/BiographySection.vue'

export default {
	name: 'Profile',

	components: {
		AvatarSection,
		DisplayNameSection,
		EmailSection,
		PhoneSection,
		BiographySection,
	},
}
</script>

<style lang="scss" scoped>
@import 'settingsCss/settings.scss';
</style>

<template>
	<div>
		<div id="personal-settings">
			<h2 class="hidden-visually">{{ t('my_company', 'Personal info') }}</h2>
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
import AvatarSection from 'apps/settings/src/components/PersonalInfo/AvatarSection.vue'
import DisplayNameSection from 'apps/settings/src/components/PersonalInfo/DisplayNameSection.vue'
import EmailSection from 'apps/settings/src/components/PersonalInfo/EmailSection/EmailSection.vue'
import PhoneSection from 'apps/settings/src/components/PersonalInfo/PhoneSection.vue'
import BiographySection from 'apps/settings/src/components/PersonalInfo/BiographySection.vue'

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
@import 'apps/settings/css/settings.scss';
</style>

<template>
	<div id="emptycontent">
		<img :src="svgIcon" class="svg-icon">
		<h2>{{ t('my_company', 'Welcome to your company!') }}</h2>
		<p class="emptycontent-additional">
			{{ t('my_company', 'Stay up to date about all news from your company.') }}
		</p>
		<div id="shareRoomContainer" />
	</div>
</template>

<script>
import SvgIconDark from '../../img/app-dark.svg'
import SvgIcon from '../../img/app.svg'

export default {
	name: 'Home',
	data() {
		return {
			icon: SvgIcon,
		}
	},

	computed: {
		svgIcon() {
			return this.isDarkTheme() ? SvgIcon : SvgIconDark
		},
	},
	methods: {
		isDarkTheme() {
			// Nextcloud uses --background-invert-if-dark for dark theme filters in CSS
			// Values:
			// - 'invert(100%)' for dark theme
			// - 'no' for light theme
			return window.getComputedStyle(document.body).getPropertyValue('--background-invert-if-dark') === 'invert(100%)'
		},
	},
}
</script>

<style lang="scss" scoped>
.svg-icon {
	width: 50px;
}

</style>

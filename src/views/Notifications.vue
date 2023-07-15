<template>
	<div>
		<div id="personal-settings">
			<h2 class="hidden-visually">
				{{ t('my_company', 'Notifications') }}
			</h2>
			<transition-group name="fade-collapse" tag="div">
				<Announcement v-for="announcement in announcements"
					:key="announcement.id"
					:is-admin="isAdmin"
					:author-id="announcement.author_id"
					v-bind="announcement"
					@click="onClickAnnouncement" />
			</transition-group>
		</div>
	</div>
</template>

<script>
// eslint-disable-next-line n/no-missing-import, import/no-unresolved
import { getAnnouncements } from 'apps/announcementcenter/src/services/announcementsService.js'

import { loadState } from '@nextcloud/initial-state'

// eslint-disable-next-line n/no-missing-import, import/no-unresolved
import Announcement from 'apps/announcementcenter/src/Components/Announcement.vue'

export default {
	name: 'Notifications',
	components: {
		Announcement,
	},

	data() {
		return {
			tokens: loadState('settings', 'app_tokens'),
			canCreateToken: loadState('settings', 'can_create_app_token'),
			isAdmin: false,
			commentsView: null,
			activeId: 0,
		}
	},

	computed: {
		announcements() {
			const announcements = this.$store.getters.announcements
			return announcements.sort((a1, a2) => {
				return a2.time - a1.time
			})
		},
	},

	async mounted() {
		await this.loadAnnouncements()
	},

	methods: {
		async loadAnnouncements() {
			const response = await getAnnouncements()
			const announcements = response.data?.ocs?.data || []

			announcements.forEach(announcement => {
				this.$store.dispatch('addAnnouncement', announcement)
			})
		},
	},
}
</script>

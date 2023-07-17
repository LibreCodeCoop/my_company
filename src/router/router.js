import Vue from 'vue'
import VueRouter from 'vue-router'

import { generateUrl } from '@nextcloud/router'

import Home from '../views/Home.vue'
import Profile from '../views/Profile.vue'
import Security from '../views/Security.vue'

Vue.use(VueRouter)

export default new VueRouter({
	mode: 'history',
	base: generateUrl('/apps/my_company'),
	linkActiveClass: 'active',

	routes: [
		{
			path: '/',
			component: Home,
			name: 'home',
		},
		{
			path: '/profile',
			component: Profile,
			name: 'profile',
		},
		{
			path: '/security',
			component: Security,
			name: 'security',
		},
	],
})

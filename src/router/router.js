import Vue from 'vue'
import VueRouter from 'vue-router'

import { generateUrl } from '@nextcloud/router'

import Home from '../views/Home.vue'
import RegistrationForm from '../views/RegistrationForm.vue'

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
			path: '/registration-form',
			component: RegistrationForm,
			name: 'registration-form',
		},
	],
})

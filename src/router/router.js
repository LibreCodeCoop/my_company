import Vue from 'vue'
import VueRouter from 'vue-router'

import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'

import Home from '../views/Home.vue'
import RegistrationForm from '../views/RegistrationForm.vue'

Vue.use(VueRouter)

const approved = loadState('my_company', 'approved', false)

if (approved) {
	var routes = [
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
	]
} else {
	var routes = [
		{
			path: '/',
			component: RegistrationForm,
			name: 'registration-form',
		},
	]
}

export default new VueRouter({
	mode: 'history',
	base: generateUrl('/apps/my_company'),
	linkActiveClass: 'active',

	routes: routes,
})

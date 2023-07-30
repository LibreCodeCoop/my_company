import Vue from 'vue'
import VueRouter from 'vue-router'

import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'

import Home from '../views/Home.vue'
import Registration from '../views/Registration.vue'
import RegistrationForm from '../views/RegistrationForm.vue'

Vue.use(VueRouter)

const approved = loadState('my_company', 'approved', false)
let routes = {}

if (approved) {
	routes = [
		{
			path: '/',
			component: Home,
			name: 'home',
		},
		{
			path: '/registration',
			component: Registration,
			name: 'registration',
		},
	]
} else {
	routes = [
		{
			path: '/',
			component: Registration,
			name: 'registration',
			props: true,
		},
		{
			path: '/registration/form',
			component: RegistrationForm,
			name: 'registration-form',
		},
	]
}

export default new VueRouter({
	mode: 'history',
	base: generateUrl('/apps/my_company'),
	routes,
})

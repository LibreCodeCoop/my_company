import Vue from 'vue'
import VueRouter from 'vue-router'

import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'

import Home from '../views/Home.vue'
import MenuSection from '../views/MenuSection.vue'
import Registration from '../views/Registration.vue'
import RegistrationForm from '../views/RegistrationForm.vue'

Vue.use(VueRouter)

const approved = loadState('my_company', 'registration-approved', false)
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
		{
			path: '/section/:id',
			component: MenuSection,
			name: 'section',
			props: true,
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

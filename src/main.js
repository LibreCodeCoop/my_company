/*
 * @copyright Copyright (c) 2023 Vitor Mattos <vitor@php.rio>
 *
 * @author Vitor Mattos <vitor@php.rio>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


import Vue from 'vue'
import VueRouter from 'vue-router'

import { generateFilePath } from '@nextcloud/router'
import { getRequestToken } from '@nextcloud/auth'
import { translate, translatePlural } from '@nextcloud/l10n'

import App from './App.vue'

import './init.js'
import router from './router/router.js'
import store from './store/index.js'

Vue.mixin({ methods: { t, n } })
Vue.prototype.t = translate
Vue.prototype.n = translatePlural

// CSP config for webpack dynamic chunk loading
// eslint-disable-next-line
__webpack_nonce__ = btoa(getRequestToken())

// Correct the root of the app for chunk loading
// OC.linkTo matches the apps folders
// OC.generateUrl ensure the index.php (or not)
// We do not want the index.php since we're loading files
// eslint-disable-next-line
__webpack_public_path__ = generateFilePath('my_company', '', 'js/')

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

Vue.use(VueRouter)

// Force redirect if rewrite enabled but accessed through index.php
if (window.location.pathname.split('/')[1] === 'index.php' && OC.config.modRewriteWorking) {
	router.push({ name: 'root' })
}

const instance = new Vue({
	el: '#app-content-vue',
	name: 'MyCompany',
	router,
	store,
	render: h => h(App),
})

window.store = store

// make the instance available to global components that might run on the same page
if (!window.OCA.MyCompany) {
	window.OCA.MyCompany = {}
}
OCA.MyCompany.instance = instance

export default instance

import Vue from 'vue'
import Vuex, { Store } from 'vuex'

import storeConfig from './storeConfig.js'

Vue.use(Vuex)

export default new Store(storeConfig)

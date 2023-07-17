// eslint-disable-next-line n/no-missing-import, import/no-unresolved
import announcementsStore from 'apps/announcementcenter/src/store/announcementsStore.js'
import Vue from 'vue'
import Vuex, { Store } from 'vuex'

import storeConfig from './storeConfig.js'

Vue.use(Vuex)

export default new Store({ ...storeConfig, ...announcementsStore })

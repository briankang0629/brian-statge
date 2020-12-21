import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import './plugins/axios'
import './plugins/sweetalert2'
import './plugins/bootstrap-vue'
import './plugins/calendar'
import './plugins/datetime'
import './plugins/image/file-uploader'
import './vendor'
import qs from 'qs'
import i18n from './i18n'

//global
Vue.mixin({
    data: function () {
        return {
            qs: qs
        }
    }
})

Vue.config.productionTip = false

new Vue({
    router,
    store,
	i18n,
    render: h => h(App)
}).$mount('#app');

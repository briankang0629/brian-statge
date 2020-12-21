import Vue from 'vue';
import VCalendar from 'v-calendar';

Vue.use(VCalendar);

//註冊傳送值所需要的格式
Vue.use({
	install: function (Vue) {
		//region -- 註冊特殊事件名稱 -------------------------
		Vue.prototype.$calendar = {
			/**
			 * date 輸出格式為date YYYY-MM-DD
			 *
			 * @return {object} date
			 */
			date() {
				return {
					type: 'string',
					mask: 'YYYY-MM-DD',
				}
			},

			/**
			 * dateTime 輸出格式為dateTime YYYY-MM-DD HH:mm:ss
			 *
			 * @return {object} dateTime
			 */
			dateTime() {
				return {
					type: 'string',
					mask: 'YYYY-MM-DD HH:mm:ss',
				}
			},
		}
	}
});

import Vue from 'vue'
import Notifications from 'vue-notification'

Vue.use(Notifications)

//註冊傳送值所需要的格式
Vue.use({

	install: function (Vue) {
		//region -- 註冊特殊事件名稱 -------------------------
		Vue.mixin({
			methods: {
				/**
				 * notify
				 *
				 * @param {object} setting
				 * @return {object} date
				 */
				notify( setting ) {
					let notifyData = {}

					switch (setting.status) {
						case 'success':
							notifyData = {
								type: 'success',
								text: setting.msg
							}
							break;
						case 'error':
							notifyData = {
								type: 'error',
								title: '#' + setting.errorCode,
								text: setting.msg
							}
							break;

					}

					this.$notify(notifyData);
				},
			}
		});
	}
});

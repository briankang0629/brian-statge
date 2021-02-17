import Vue from 'vue'
import swal from 'sweetalert2'

Vue.prototype.$Swal = swal.mixin({
	icon: 'success',
	showCancelButton: false
})

//註冊傳送值所需要的格式
Vue.use({
	install: function (Vue) {
		//region -- 註冊特殊事件名稱 -------------------------
		Vue.prototype.$Swal = {
			/**
			 * ask 動作前確認視窗
			 *
			 * @param object setting
			 * @return {object} date
			 */
			ask(setting) {
				return swal.fire({
					title: setting.title,
					text: setting.text,
					icon: 'warning',
					confirmButtonText: setting.confirmText,
					cancelButtonText: setting.cancelText,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					showCancelButton: true,
					reverseButtons: true
				})
			},

		}
	}
});

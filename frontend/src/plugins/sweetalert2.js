import Vue from 'vue'
import swal from 'sweetalert2'

Vue.prototype.$Swal = swal.mixin({
	icon: 'success',
	showCancelButton: false
})
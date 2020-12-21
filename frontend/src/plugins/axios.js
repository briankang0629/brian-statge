"use strict"

import Vue from 'vue'
import Axios from "axios"

let config = {
	baseURL: (process.env.NODE_ENV === 'production') ? 'https://admin.kang-web.tk/backend/' : '/',
	timeout: 60 * 1000, // Timeout
	// withCredentials: true, // Check cross-site Access-Control
}

export const axios = Axios.create(config)

axios.interceptors.request.use(
	function (config) {
		// Do something before request is sent
		return config
	},
	function (error) {
		// Do something with request error
		return Promise.reject(error)
	}
)

// Add a response interceptor
axios.interceptors.response.use(
	function (response) {
		// Do something with response data
		return response
	},
	function (error) {
		// Do something with response error
		return Promise.reject(error)
	}
)

Plugin.install = function (Vue) {
	Vue.axios = axios
	window.axios = axios
	Object.defineProperties(Vue.prototype, {
		axios: {
			get() {
				return axios
			}
		},
		$axios: {
			get() {
				return axios
			}
		},
	})
}

Vue.use(Plugin)

export default Plugin

import Vue from 'vue'
import Vuex from 'vuex'
import {axios} from "../plugins/axios"

Vue.use(Vuex)

export default new Vuex.Store({
	state: {
		auth: {},
		token: ''
	},
	mutations: {
		setAuthData(state, data) {
			state.auth = data
		}
	},
	actions: {
		/**
		 * checkAuth 驗證登入狀況
		 *
		 * @since 0.0.1
		 * @version 0.0.1
		 */
		checkAuth({commit}) {
			const token = localStorage.getItem('auth') ? JSON.parse(localStorage.getItem('auth')).token : ''
			let sentData = {
				method: 'get',
				url: '/api/auth',
				headers: {
					'Authorization': token,
				}
			}
			return new Promise((resolve, reject) => {
				axios.request(sentData).then(({data}) => {
					//存到state變數
					commit('setAuthData', data)
					//resolve
					resolve(data)
				}).catch(({response}) => {
					reject(response)
				})
			})
		},

		/**
		 * authRequest 請求前驗正登入狀態
		 *
		 * @since 0.0.1
		 * @version 0.0.1
		 */
		authRequest({state}, request) {// eslint-disable-line no-unused-vars
			const token = localStorage.getItem('auth') ? JSON.parse(localStorage.getItem('auth')).token : ''
			let sentData = {
				method: request.method,
				url: request.url,
				headers: {
					'Authorization': token,
				}
			}
			if (request.method === 'get') {
				sentData.params = request.params
			}
			if ((request.method === 'post') || (request.method === 'put')) {
				sentData.data = request.data
			}
			return new Promise((resolve, reject) => {
				axios.request(sentData).then(({data}) => {
					resolve(data);
				}).catch(({response}) => {
					reject(response.data)
				})
			})
		},

		/**
		 * request 無token狀態請求
		 *
		 * @since 0.0.1
		 * @version 0.0.1
		 */
		request({state}, request) {// eslint-disable-line no-unused-vars
			let sentData = {
				method: request.method,
				url: request.url,
			}

			if (request.method === 'get') {
				sentData.params = request.params
			}
			if ((request.method === 'post') || (request.method === 'put')) {
				sentData.data = request.data
			}
			return new Promise((resolve, reject) => {
				axios.request(sentData).then(({data}) => {
					resolve(data);
				}).catch(({response}) => {
					reject(response.data)
				})
			})
		},

		/**
		 * checkAuthGoogle Google登入用
		 *
		 * @since 0.0.1
		 * @version 0.0.1
		 */
		checkAuthGoogle({commit}, params) {// eslint-disable-line no-unused-vars
			let sentData = {
				method: 'get',
				url: '/api/auth/google',
				params: {
					code: params.code,
				}
			}
			return new Promise((resolve, reject) => {
				axios.request(sentData).then(({data}) => {
					//set localStorage
					localStorage.setItem('auth', JSON.stringify({
						token: data.token,
						account: data.account,
						name: data.name
					}))
					commit('setAuthData', data)
					resolve(data)
				}).catch(({response}) => {
					reject(response)
				})
			})
		},

		/**
		 * requestQueue 批量多筆請求
		 *
		 * @since 0.0.1
		 * @version 0.0.1
		 */
		requestQueue({commit}, request) {// eslint-disable-line no-unused-vars
			//批量請求儲存的promise 陣列
			const promisesAll = []

			//紀錄失敗次數
			let failedCount = 0

			//token
			const token = localStorage.getItem('auth') ? JSON.parse(localStorage.getItem('auth')).token : ''

			//批量請求
			for (const id of request.idArray) {
				//傳送參數
				let sentData = {
					method: request.method,
					url: request.url + '/' + id,
					headers: {
						'Authorization': token,
					}
				}

				//累加請求
				promisesAll.push(
					new Promise((resolve, reject) => {
						axios.request(sentData).then(({data}) => {
							resolve(data);
						} ,(err) => {
							resolve(err);
							failedCount++;
						});
					}),
				);
			}

			//全部請求 promise all 回傳
			return new Promise((resolve, reject) => {
				Promise.all(promisesAll).then((responses) => {
					if (failedCount === promisesAll.length) {
						reject(responses);
						return;
					}
					resolve(responses);
				});
			});
		},

	},
	modules: {}
})

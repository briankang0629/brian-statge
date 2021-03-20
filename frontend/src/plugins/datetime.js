/**
 * datetime 時間計算插件
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/12/04
 */

//Vue
import Vue from 'vue'

//擴充功能
String.prototype.padStart = function padStart(targetLength, padString) {
	targetLength = targetLength >> 0
	padString = String(typeof padString !== 'undefined' ? padString : ' ')
	if (this.length > targetLength) return String(this)
	else {
		targetLength = targetLength - this.length
		if (targetLength > padString.length) padString += padString.repeat(targetLength / padString.length)
		return padString.slice(0, targetLength) + String(this)
	}
}

Vue.use({
	install: (Vue) => {
		//region -- 註冊特殊事件名稱 -------------------------
		Vue.prototype.$dateTime = {
			/**
			 * 取年
			 *
			 * @param {int} unixTimeStamp 毫秒時間戳
			 */
			year(unixTimeStamp = Date.now()) {
				return new Date(unixTimeStamp).getFullYear()
			},
			/**
			 * 取月
			 *
			 * @param {int} unixTimeStamp 毫秒時間戳
			 */
			month(unixTimeStamp = Date.now()) {
				return (new Date(unixTimeStamp).getMonth() + 1).toString().padStart(2, '0')
			},
			/**
			 * 取日
			 *
			 * @param {int} unixTimeStamp 毫秒時間戳
			 */
			day(unixTimeStamp = Date.now()) {
				return (new Date(unixTimeStamp).getDate()).toString().padStart(2, '0')
			},
			/**
			 * 取時
			 *
			 * @param {int} unixTimeStamp 毫秒時間戳
			 */
			hour(unixTimeStamp = Date.now()) {
				return (new Date(unixTimeStamp).getHours()).toString().padStart(2, '0')
			},
			/**
			 * 取分
			 *
			 * @param {int} unixTimeStamp 毫秒時間戳
			 */
			minute(unixTimeStamp = Date.now()) {
				return (new Date(unixTimeStamp).getMinutes()).toString().padStart(2, '0')
			},
			/**
			 * 取秒
			 *
			 * @param {int} unixTimeStamp 毫秒時間戳
			 */
			second(unixTimeStamp = Date.now()) {
				return (new Date(unixTimeStamp).getSeconds()).toString().padStart(2, '0')
			},
			/**
			 * 取當前日期
			 *
			 * @param {int} unixTimeStamp 毫秒時間戳
			 */
			nowDate(unixTimeStamp = Date.now()) {
				return `${this.year()}-${this.month()}-${this.day()}`
			},
			/**
			 * 取當月第一天
			 */
			monthFirstDate() {
				return `${this.year()}-${this.month()}-${this.day(new Date().setDate(1))}`
			},
			/**
			 * 取當月最後一天
			 */
			monthLastDate() {
				let date = new Date();
				let lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
				return `${this.year()}-${this.month()}-${this.day(new Date().setDate(lastDay))}`
			},
			/**
			 * 取當前時間
			 *
			 * @param {int} unixTimeStamp 毫秒時間戳
			 */
			nowTime(unixTimeStamp = Date.now()) {
				return `${this.year()}-${this.month()}-${this.day()} ${this.hour()}:${this.minute()}:${this.second()}`
			},
			/**
			 * 取隔天日期
			 *
			 * @param {int} unixTimeStamp 毫秒時間戳
			 */
			tomorrow(unixTimeStamp = Date.now()) {
				return `${this.year()}-${this.month()}-${(new Date(unixTimeStamp).getDate()+1).toString().padStart(2, '0')}`
			}
		}

		//endregion -- 註冊特殊事件名稱 ----------------------
	}
})
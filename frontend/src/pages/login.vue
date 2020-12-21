<template>
	<div class="hold-transition login-page">
		<div class="login-box">
			<div class="login-logo">
				<a><b>後台管理系統</b></a>
			</div>
			<!-- /.login-logo -->
			<div class="card">
				<div class="card-body login-card-body">
					<p class="login-box-msg">管理系統登入</p>

					<form @submit.prevent="login">
						<div class="input-group mb-3">
							<input type="text" class="form-control" :placeholder="$t('user.account')" v-model="user.account">
							<div class="input-group-append">
								<div class="input-group-text">
									<span class="fas fa-envelope"></span>
								</div>
							</div>
						</div>
						<div class="input-group mb-3">
							<input type="password" class="form-control" :placeholder="$t('user.password')" v-model="user.password">
							<div class="input-group-append">
								<div class="input-group-text">
									<span class="fas fa-lock"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-8">
								<div class="icheck-primary">
									<input type="checkbox" v-model="rememberAccount" @click="remember()">
									<label for="remember">
										記住帳號
									</label>
								</div>
							</div>
							<!-- /.col -->
							<div class="col-12">
								<button class="btn btn-info btn-block">登入</button>
							</div>
							<!-- /.col -->
						</div>
						<hr/>
						<div class="social-auth-links text-center mb-3">
							<a href="#" class="btn btn-block btn-primary">
								<i class="fab fa-facebook mr-2"></i> Facebook 登入
							</a>
							<a href="#" class="btn btn-block btn-danger" @click="loginGoogle()">
								<i class="fab fa-google-plus mr-2"></i> Google 登入
							</a>
						</div>
					</form>
				</div>
				<!-- /.login-card-body -->
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				user: {
					account: '',
					password: '',
				},
				rememberAccount: false
			}
		},
		mounted() {
			//驗證當前登入狀況
			this.$store.dispatch('checkAuth').then(() => {
				this.$router.push('/')
			}).catch(() => {
				localStorage.removeItem('auth');
				// //取前一頁網址 判訂第三方登入失敗
				if (this.$router.history._startLocation.substring(0, 12) == '/auth/google') {
					this.$Swal.fire({
						icon: 'error',
						title: this.$t('common.failed'),
						text: 'Google 登入失敗',
						confirmButtonText: 'OK',
					})
				} else if (this.$router.history._startLocation.substring(0, 13) == '/auth/facebook') {
					this.$Swal.fire({
						icon: 'error',
						title: this.$t('common.failed'),
						text: 'Facebook 登入失敗',
						confirmButtonText: 'OK',
					})
				}
			})

			//記憶帳號
			if (localStorage.getItem('account')) {
				this.rememberAccount = true
				this.user.account = localStorage.getItem('account')
			}
		},
		methods: {

			/**
			 * remember 記憶帳號
			 *
			 * @since 0.0.1
			 * @version 0.0.1
			 */
			remember() {
				this.rememberAccount = !this.rememberAccount
			},

			/**
			 * login 登入
			 *
			 * @since 0.0.1
			 * @version 0.0.1
			 */
			login() {
				let data = this.qs.stringify(this.user);
				this.axios.post('/api/login', data).then(response => {
					if (response.data.status == 'success') {
						//set localStorage
						localStorage.setItem('auth', JSON.stringify({
							token: response.data.token,
							account: this.user.account,
							name: this.user.name
						}))
						//預設繁體中文
						localStorage.setItem('language', 'zh-tw')

						//權限存localStorage
						localStorage.setItem('permission', JSON.stringify(response.data.permission))

						//若要記住帳號
						if (this.rememberAccount) {
							localStorage.setItem('account', this.user.account)
						} else {
							localStorage.removeItem('account')
						}

						this.$Swal.fire({
							icon: response.data.status,
							title: this.$t('common.success'),
							text: response.data.msg,
							confirmButtonText: 'OK',
						}).then(() => {
							window.location.href = '/';
						});
					}
				}).catch(error => {
					this.$Swal.fire({
						icon: error.response.data.status,
						title: this.$t('common.failed'),
						text: error.response.data.msg,
					})
					this.account = '';
					this.password = '';
				})

			},

			/**
			 * loginGoogle Google登入
			 *
			 * @since 0.0.1
			 * @version 0.0.1
			 */
			loginGoogle() {
				this.axios.post('/api/login/google').then(response => {
					if (response.data.status == 'success') {
						//預設繁體中文
						localStorage.setItem('language', 'zh-tw')
						location.href = response.data.url
					}
				}).catch(error => {
					this.$Swal.fire({
						icon: error.response.data.status,
						title: this.$t('common.failed'),
						text: error.response.data.msg,
					})
				})
			},
		}
	}
</script>
<style>
	.login-page {
		background-color: #343a40;
	}
	.login-card-body {
		border-radius: 5px;
		background-color: #ececec;
	}

	.login-logo a b {
		color: white;
	}
</style>
<template>
	<div class="content-header" id="breadcrumb">
		<div class="container-fluid">
			<div class="row mb-4">
				<div class="col-sm-6">
					<h4 class="m-0 text-dark font-weight-bold">{{page ? page : $t('menu.dashboard')}}</h4>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><router-link to="/">{{$t('menu.dashboard')}}</router-link></li>
						<li class="breadcrumb-item" v-if="page"> {{page}} </li>
						<li class="breadcrumb-item active" v-if="subPage">{{subPage}}</li>
					</ol>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				page: '',
				subPage: '',
			}
		},
		mounted () {
			this.init()
		},
		watch: {
			$route (){
				this.init()
			}
		},
		methods: {
			init() {
				//網址陣列
				let routeArr = this.$route.path.split('/');

				//麵包屑處理
				if(routeArr.length > 2) {
					this.page = this.$t('menu.' + routeArr[1]);
					this.subPage = this.$t('menu.' + this.$route.name)
				}

			}
		}
	}
</script>
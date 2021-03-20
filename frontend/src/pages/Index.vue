<template>
	<div class="content-wrapper">
		<breadcrumb/>
        <section class="content">
			<div class="row">
				<div class="col-lg-3 col-6">
					<!-- small box -->
					<div class="small-box bg-info">
						<div class="inner">
							<h3>{{ dashboard.orderTotal }}</h3>

							<p>{{ $t('dashboard.orderCount') }}</p>
						</div>
						<div class="icon">
                            <i class="fas fa-shopping-bag"></i>
						</div>
						<a href="#" class="small-box-footer">{{ $t('dashboard.seeMore') }} <i class="fas fa-arrow-circle-right"></i></a>
					</div>
				</div>
				<!-- ./col -->
				<div class="col-lg-3 col-6">
					<!-- small box -->
					<div class="small-box bg-success">
						<div class="inner">
							<h3>{{ dashboard.orderSale }}<sup style="font-size: 5px">NT$</sup></h3>

							<p>{{ $t('dashboard.saleCount') }}</p>
						</div>
						<div class="icon">
                            <i class="fas fa-wallet"></i>
						</div>
						<a href="#" class="small-box-footer">{{ $t('dashboard.seeMore') }} <i class="fas fa-arrow-circle-right"></i></a>
					</div>
				</div>
				<!-- ./col -->
				<div class="col-lg-3 col-6">
					<!-- small box -->
					<div class="small-box bg-warning">
						<div class="inner">
							<h3>{{ dashboard.userTotal }}</h3>
							<p>{{ $t('dashboard.userCount') }}</p>
						</div>
						<div class="icon">
                            <i class="fas fa-users"></i>
						</div>
						<a href="#" class="small-box-footer">{{ $t('dashboard.seeMore') }} <i class="fas fa-arrow-circle-right"></i></a>
					</div>
				</div>
				<!-- ./col -->
				<div class="col-lg-3 col-6">
					<!-- small box -->
					<div class="small-box bg-danger">
						<div class="inner">
							<h3>0</h3>

							<p>{{ $t('dashboard.userRegisterCount') }}</p>
						</div>
						<div class="icon">
                            <i class="fas fa-user-clock"></i>
						</div>
						<a href="#" class="small-box-footer">{{ $t('dashboard.seeMore') }} <i class="fas fa-arrow-circle-right"></i></a>
					</div>
				</div>
				<!-- ./col -->
			</div>
            <!-- chart -->
			<div class="row">
				<div class="col-lg-6 col-12">
					<div class="card card-danger">
						<div class="card-header">
							<h3 class="card-title"> <i class="fas fa-chart-bar"></i> {{ $t('dashboard.saleRecord') }}</h3>

							<div class="card-tools">
								<button type="button" class="btn btn-tool" data-card-widget="collapse">
									<i class="fas fa-minus"></i>
								</button>
								<button type="button" class="btn btn-tool" data-card-widget="remove">
									<i class="fas fa-times"></i>
								</button>
							</div>
						</div>
						<div class="card-body">
							<canvas id="myChart" style="min-height: 250px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
						</div>
					</div>
				</div>
				<div class="col-lg-6 col-12">
					<div class="card card-success">
						<div class="card-header">
							<h3 class="card-title"><i class="fas fa-chart-bar"></i> {{ $t('dashboard.productView') }}</h3>

							<div class="card-tools">
								<button type="button" class="btn btn-tool" data-card-widget="collapse">
									<i class="fas fa-minus"></i>
								</button>
								<button type="button" class="btn btn-tool" data-card-widget="remove">
									<i class="fas fa-times"></i>
								</button>
							</div>
						</div>
						<div class="card-body">
							<canvas id="DonutChart" style="min-height: 250px; height: 400px; max-height: 400px; max-width: 100%;"></canvas>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</template>
<script>
	import Chart from 'chart.js'
	export default {
		layout: 'admin',
		components: {
			breadcrumb: () => import('@/components/common/breadcrumb.vue'),
		},
		data() {
			return {
				'dashboard': {
					'orderTotal' : 0,
					'orderSale' : 0,
					'userTotal' : 0,
				}
			}
		},
		mounted() {
            //init
            this.init();
		},
		methods: {

            /**
             * init 初始化
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            init() {
                return Promise.all([
                    this.getOrderByMonth(),
                    this.getProductView(),
                    this.getOrderTotal(),
                    this.getOrderSale(),
                    this.getUserTotal()
                ])
            },

            /**
             * getOrderByMonth 月銷售訂單
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getOrderByMonth() {
                const request = {
                    method: 'get',
                    url: '/api/order/report/groupBy/month',
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    new Chart(document.getElementById("myChart"), {
                        type: "line",
                        data: {
                            labels: response.data.map(function (item) {
                                return item.month;
                            }),
                            datasets: [
                                {
                                    label: this.$t('dashboard.orderCount'),
                                    backgroundColor: "rgba(225,10,10,0.3)",
                                    borderColor: "rgba(225,103,110,1)",
                                    borderWidth: 1,
                                    pointStrokeColor: "#fff",
                                    pointStyle: "crossRot",
                                    data: response.data.map(function (item) {
                                        return item.total;
                                    }),
                                    cubicInterpolationMode: "monotone",
                                    spanGaps: "false",
                                    fill: "false"
                                }
                            ]
                        },
                        options: {

                        }

                    });
                }).catch((error) => {
                    this.$root.notify(error)
                })

            },

            /**
             * getProductView 商品觀看數
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getProductView() {
                const request = {
                    method: 'get',
                    url: '/api/product/view',
                    params: {
                        perPage: 5
                    }
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    new Chart(document.getElementById("DonutChart"), {
                        type: "doughnut",
                        data: {
                            labels: response.data.map(function (item) {
                                return item.name;
                            }),
                            datasets: [
                                {
                                    data: response.data.map(function (item) {
                                        return item.view;
                                    }),
                                    backgroundColor: [
                                        'red',
                                        'green',
                                        'skyblue',
                                        'pink',
                                        'yellow',
                                    ],
                                }
                            ]
                        },
                        options: {}
                    })

                }).catch((error) => {
                    this.$root.notify(error)
                })

            },

			/**
			 * getOrderTotal 訂單總數
			 *
			 * @since 0.0.1
			 * @version 0.0.1
			 */
			getOrderTotal() {
				const request = {
					method: 'get',
					url: '/api/order/report/total',
				}

				this.$store.dispatch('authRequest', request).then((response) => {
					this.dashboard.orderTotal = response.data.total
				}).catch((error) => {
					this.$root.notify(error)
				})
			},

			/**
			 * getOrderSale 訂單銷量
			 *
			 * @since 0.0.1
			 * @version 0.0.1
			 */
			getOrderSale() {
				const request = {
					method: 'get',
					url: '/api/order/report/sale',
				}

				this.$store.dispatch('authRequest', request).then((response) => {
					this.dashboard.orderSale = response.data.total
				}).catch((error) => {
					this.$root.notify(error)
				})
			},

			/**
			 * getUserTotal 會員總數
			 *
			 * @since 0.0.1
			 * @version 0.0.1
			 */
			getUserTotal() {
				const request = {
					method: 'get',
					url: '/api/user/report/total',
				}

				this.$store.dispatch('authRequest', request).then((response) => {
					this.dashboard.userTotal = response.data.total
				}).catch((error) => {
					this.$root.notify(error)
				})
			},
		}
	}
</script>

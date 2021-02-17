<template>
	<div class="content-wrapper">
		<breadcrumb/>
        <section class="content">
			<div class="row">
				<div class="col-lg-3 col-6">
					<!-- small box -->
					<div class="small-box bg-info">
						<div class="inner">
							<h3>150</h3>

							<p>New Orders</p>
						</div>
						<div class="icon">
							<i class="ion ion-bag"></i>
						</div>
						<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
					</div>
				</div>
				<!-- ./col -->
				<div class="col-lg-3 col-6">
					<!-- small box -->
					<div class="small-box bg-success">
						<div class="inner">
							<h3>53<sup style="font-size: 20px">%</sup></h3>

							<p>Bounce Rate</p>
						</div>
						<div class="icon">
							<i class="ion ion-stats-bars"></i>
						</div>
						<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
					</div>
				</div>
				<!-- ./col -->
				<div class="col-lg-3 col-6">
					<!-- small box -->
					<div class="small-box bg-warning">
						<div class="inner">
							<h3>44</h3>

							<p>User Registrations</p>
						</div>
						<div class="icon">
							<i class="ion ion-person-add"></i>
						</div>
						<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
					</div>
				</div>
				<!-- ./col -->
				<div class="col-lg-3 col-6">
					<!-- small box -->
					<div class="small-box bg-danger">
						<div class="inner">
							<h3>65</h3>

							<p>Unique Visitors</p>
						</div>
						<div class="icon">
							<i class="ion ion-pie-graph"></i>
						</div>
						<a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
					</div>
				</div>
				<!-- ./col -->
			</div>
			<div class="row">
				<div class="col-lg-6 col-12">
					<div class="card card-danger">
						<div class="card-header">
							<h3 class="card-title">Line Chart</h3>

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
							<h3 class="card-title">Donut Chart</h3>

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
		mounted() {
			let ctx1 = document.getElementById("myChart");
			let ctx2 = document.getElementById("DonutChart");

			/**
			 * myChart
			 */
			let myChart = new Chart(ctx1, {// eslint-disable-line no-unused-vars
				type: "line",
				data: {
					labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
					datasets: [
						{
							label: "test1",
							backgroundColor: "rgba(225,10,10,0.3)",
							borderColor: "rgba(225,103,110,1)",
							borderWidth: 1,
							pointStrokeColor: "#fff",
							pointStyle: "crossRot",
							data: [65, 59, 0, 81, 56, 10, 40, 22, 32, 54, 10, 30],
							cubicInterpolationMode: "monotone",
							spanGaps: "false",
							fill: "false"
						}
					]
				},
				options: {

				}

			});

			/**
			 *
			 */
			let DonutChart = new Chart(ctx2, {// eslint-disable-line no-unused-vars
				type: "doughnut",
				data: {
					// These labels appear in the legend and in the tooltips when hovering different arcs
					labels: [
						'Red',
						'Yellow',
						'Blue'
					],
					datasets: [{
						data: [10, 20, 30],
						backgroundColor: [
							'#FA0001',
							'green',
							'skyblue',
						],
					}]
				},
				options: {

				}
			});

		},
		methods: {
			click() {
				console.log(this.$store.state.auth)
				this.$Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'error msg'
				})
			},
			mounted() {
				this.$store.dispatch('checkAuth')
			},
			clickApi() {
				this.axios
					.get('/v1/bpi/currentprice.json')
					.then(response => {
						console.log(response.data.bpi);
						this.info = response.data.bpi
					})
					.catch(error => {
						console.log(error)
						this.errored = true
					})
					.finally(() => this.loading = false)

				// let Request = {
				//     Method: 'get',
				//     Uri: '/v1/bpi/currentprice.json',
				//     Params: {}
				// }
				// this.$store.dispatch('AuthRequest', Request).then((Response) => {
				//     console.log(Response);
				//     // this.PriceStrategiesList = Response.data
				//     // this.PageData.currentPage = Response.pagination.page
				//     // this.PageData.perPage = Response.pagination.perPage
				//     // this.PageData.totalItems = Response.pagination.totalItems
				//     // this.PageData.totalPages = Response.pagination.totalPages
				// }).catch((Error) => {
				//     alert(Error)
				// })
			},
		}
	}
</script>
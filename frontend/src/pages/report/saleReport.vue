<template>
    <section class="content" id="logRecord-list">
        <div class="container-fluid">
            <!-- Tool Bar-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="float-sm-right">
                        <a class="btn bg-success mr-2 mb-3">
                            <i class="fas fa-file"></i> {{ $t('common.export') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <b-overlay :show="isLoading" rounded="sm">
                <div class="row">
                    <div class="col-12">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-bar"></i> {{ $t('dashboard.saleRecord') }}
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" @click="getSaleReport(); chartMethod === 'line' ? chartMethod = 'bar' : chartMethod = 'line'">
                                        {{ $t('chart.change') }} {{ $t('chart.' + (chartMethod === 'line' ? 'bar' : 'line')) }}
                                    </button>
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                                        <i class="fas fa-times"></i>
                                    </button>

                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="saleReport"
                                        style="min-height: 250px; height: 400px; max-height: 600px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-table">
                    <div class="card-header">
                        <label class="badge bg-success">{{ $t('common.searchBar') }}</label>
                        <b-row class="justify-content-md-left">

                            <!-- 開始時間 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('report.saleReport.startDate') }}：</label>
                                <v-date-picker v-model="sentData.startDate" :model-config="dateConfig">
                                    <template v-slot="{ inputValue, inputEvents }">
                                        <div class="flex items-center">
                                            <input type="text" class="form-control"
                                                   :value="inputValue"
                                                   readonly
                                                   v-on="inputEvents"
                                            />
                                        </div>
                                    </template>
                                </v-date-picker>
                            </b-col>

                            <!-- 結束時間 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('report.saleReport.endDate') }}：</label>
                                <v-date-picker v-model="sentData.endDate" :model-config="dateConfig">
                                    <template v-slot="{ inputValue, inputEvents }">
                                        <div class="flex items-center">
                                            <input type="text" class="form-control"
                                                   :value="inputValue"
                                                   readonly
                                                   v-on="inputEvents"
                                            />
                                        </div>
                                    </template>
                                </v-date-picker>
                            </b-col>

                            <!-- 訂單狀況 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('report.saleReport.orderStatus') }} ：</label>
                                <b-form-select v-model="sentData.orderStatus">
                                    <option v-for="(item, index) in $t('orderStatus')" :value="index" :key="index">
                                        {{ item }}
                                    </option>
                                    <template #first>
                                        <b-form-select-option :value="null">-- {{ $t('common.choose') }} --
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-col>

                            <!-- 搜尋類型 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('report.saleReport.timeType') }} ：</label>
                                <b-form-select v-model="sentData.timeType">
                                    <option v-for="(item, index) in $t('dateRange')" :value="index" :key="index">
                                        {{ item }}
                                    </option>
                                    <template #first>
                                        <b-form-select-option :value="null">-- {{ $t('common.choose') }} --
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-col>

                            <!-- 搜尋 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('report.saleReport.operate') }} ：</label>
                                <div class="col-sm-12">
                                    <b-button variant="outline-success" class="my-2 my-sm-0 ml-2"
                                              @click="getSaleReport()"><i class="fas fa-search"></i>
                                        {{ $t('message.search') }}
                                    </b-button>
                                </div>
                            </b-col>
                        </b-row>

                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped text-nowrap table-bordered">
                            <thead>
                            <tr>
                                <th>{{ $t('report.saleReport.time') }}</th>
                                <th>{{ $t('report.saleReport.orderCount') }}</th>
                                <th>{{ $t('report.saleReport.saleTotal') }}</th>
                                <th>{{ $t('report.saleReport.commission') }}</th>
                                <th>{{ $t('report.saleReport.cashPay') }}</th>
                                <th>{{ $t('report.saleReport.creditPay') }}</th>
                                <th>{{ $t('report.saleReport.ATMPay') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- 時間區間資料 -->
                            <tr v-for="(data, index) in saleReport" :key="index">
                                <td class="text-primary">{{ data.time }}</td>
                                <td>{{ data.count }}</td>
                                <td>{{ data.total }}</td>
                                <td>{{ data.commission }}</td>
                                <td>{{ data.cash }}</td>
                                <td>{{ data.credit }}</td>
                                <td>{{ data.ATM }}</td>
                            </tr>

                            <!-- 小計 -->
                            <tr v-show="sumTotal.count">
                                <td class="text-success"><b>{{ $t('report.saleReport.sumTotal') }}</b></td>
                                <td class="text-danger">{{ sumTotal.count }}</td>
                                <td class="text-danger">{{ sumTotal.total }}</td>
                                <td class="text-danger">{{ sumTotal.commission }}</td>
                                <td class="text-danger">{{ sumTotal.cash }}</td>
                                <td class="text-danger">{{ sumTotal.credit }}</td>
                                <td class="text-danger">{{ sumTotal.ATM }}</td>
                            </tr>
                            </tbody>
                        </table>
                        <div v-if="!isLoading && !saleReport.length" class="text-notfound text-center">
                            <span class="text-danger">
                                {{ $t('common.notFound') }}
                            </span>
                        </div>
                    </div>
                </div>
            </b-overlay>
        </div>

    </section>
</template>

<script>
import Chart from "chart.js";

export default {
    layout: 'admin',
    data() {
        return {
            //銷售報表
            saleReport: {},
            //小計欄位
            sumTotal: {},
            //圖表物件
            chartObject: {},
            //請求參數
            sentData: {
                orderStatus: null,
                timeType: 'day',
                startDate: this.$dateTime.monthFirstDate(),
                endDate: this.$dateTime.monthLastDate(),
            },
            //chart
            chartMethod: 'bar',
            //方法
            method: '',
            //是否loading
            isLoading: false,
            //傳送時間格式
            dateConfig: this.$calendar.date(),
        }
    },
    computed: {},
    mounted() {
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
                this.getSaleReport(),
            ])
        },

        /**
         * getLogRecord 取操作日誌
         *
         * @since 0.0.1
         * @version 0.0.1
         */
        getSaleReport() {
            this.isLoading = true
            const request = {
                method: 'get',
                url: '/api/report/saleReport',
                params: {
                    startDate: this.sentData.startDate,
                    endDate: this.sentData.endDate,
                    timeType: this.sentData.timeType,
                }
            }

            //圖表物件生成前要將先前的刪除
            if (typeof this.chartObject.chart !== 'undefined') {
                this.chartObject.destroy()
            }

            // if (this.sentData.account !== '') request.params.account = this.sentData.account

            this.$store.dispatch('authRequest', request).then((response) => {
                //數據報表
                this.saleReport = Object.values(response.data.total)
                this.sumTotal = response.data.sumTotal

                //圖形報表
                this.chartObject = new Chart(document.getElementById("saleReport"), {
                    type: this.chartMethod,
                    data: {
                        labels: this.saleReport.map(function (item) {
                            return item.time;
                        }),
                        datasets: [
                            {
                                label: this.$t('report.saleReport.orderCount'),
                                backgroundColor: '#cb83b3',
                                borderColor: '#cb83b3',
                                spanGaps: "false",
                                fill: "false",
                                data: this.saleReport.map(function (item) {
                                    return item.count;
                                }),
                            },
                            {
                                label: this.$t('report.saleReport.saleTotal'),
                                backgroundColor: '#02F78E',
                                borderColor: '#02F78E',
                                spanGaps: "false",
                                fill: "false",
                                data: this.saleReport.map(function (item) {
                                    return item.total;
                                }),
                            },
                            {
                                label: this.$t('report.saleReport.cashPay'),
                                backgroundColor: '#92C1FF',
                                borderColor: '#92C1FF',
                                spanGaps: "false",
                                fill: "false",
                                data: this.saleReport.map(function (item) {
                                    return item.cash;
                                }),
                            },
                            {
                                label: this.$t('report.saleReport.creditPay'),
                                backgroundColor: '#EEDF7C',
                                borderColor: '#EEDF7C',
                                spanGaps: "false",
                                fill: "false",
                                data: this.saleReport.map(function (item) {
                                    return item.credit;
                                }),
                            },
                            {
                                label: this.$t('report.saleReport.ATMPay'),
                                backgroundColor: 'lightgreen',
                                borderColor: 'lightgreen',
                                spanGaps: "false",
                                fill: "false",
                                data: this.saleReport.map(function (item) {
                                    return item.ATM;
                                }),
                            },
                        ]
                    },
                    options: {}

                });

                this.isLoading = false
            }).catch((error) => {
                this.$root.notify(error)
            })
        },

    }
}
</script>

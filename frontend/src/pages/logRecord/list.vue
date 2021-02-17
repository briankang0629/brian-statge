<template>
    <section class="content" id="logRecord-list">
        <div class="container-fluid">
            <!-- Tool Bar-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="float-sm-right" v-show="false">
                        <a class="btn bg-success mr-2 mb-3" @click="create()">
                            <i class="fas fa-plus"></i> {{ $t('common.create') }}
                        </a>
                        <a class="btn bg-danger mr-2 mb-3" @click="remove()">
                            <i class="fas fa-trash"></i> {{ $t('common.delete') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <b-overlay :show="isLoading" rounded="sm">
                <div class="card card-table">
                    <div class="card-header">
                        <label class="badge bg-success">{{ $t('common.searchBar') }}</label>
                        <b-row class="justify-content-md-center">
                            <!-- 帳號 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('logRecord.account') }}：</label>
                                <b-form-input class="mr-sm-2" v-model="sentData.account"
                                              :placeholder="$t('logRecord.account')"></b-form-input>
                            </b-col>

                            <!-- 開始時間 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('logRecord.startDate') }}：</label>
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
                                <label class="mr-sm-2 ml-2">{{ $t('logRecord.endDate') }}：</label>
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

                            <!-- 身份 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('logRecord.class') }}：</label>
                                <b-form-select v-model="sentData.class">
                                    <option v-for="(item, index) in $t('identity')" :value="index" :key="index">
                                        {{item}}
                                    </option>
                                    <template #first>
                                        <b-form-select-option :value="null" disabled>-- {{ $t('common.choose') }} --
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-col>

                            <!-- 操作類型 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('logRecord.type') }} ：</label>
                                <b-form-select v-model="sentData.logId">
                                    <option v-for="(item, index) in logRecordSetting" :value="index" :key="index">
                                        {{ $t('logRecordSetting.' + item.class) }}
                                    </option>
                                    <template #first>
                                        <b-form-select-option :value="null" disabled>-- {{ $t('common.choose') }} --
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-col>

                            <!-- 搜尋 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('logRecord.operate') }} ：</label>
                                <div class="col-sm-12">
                                    <b-button variant="outline-success" class="my-2 my-sm-0 ml-2" @click="getLogRecord()"><i class="fas fa-search"></i> {{ $t('message.search') }}
                                    </b-button>
                                </div>
                            </b-col>
                        </b-row>

                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped text-nowrap">
                            <thead>
                            <tr>
                                <th>{{ $t('logRecord.logRecordId') }}</th>
                                <th>{{ $t('logRecord.name') }}</th>
                                <th>{{ $t('logRecord.account') }}</th>
                                <th>{{ $t('logRecord.class') }}</th>
                                <th>{{ $t('logRecord.remoteIP') }}</th>
                                <th>{{ $t('logRecord.host') }}</th>
                                <th>{{ $t('logRecord.path') }}</th>
                                <!--<th>{{ $t('logRecord.content') }}</th>-->
                                <th>{{ $t('logRecord.createTime') }}</th>
                                <th>{{ $t('logRecord.operate') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(data, index) in logRecordList" :key="index">
                                <td>{{data.logRecordId}}</td>
                                <td>{{ $t('logRecordSetting.'+ logRecordSetting[data.logId].class) }}</td>
                                <td>{{data.account}}</td>
                                <td>
                                    <span class="badge" :class="classStatus(data.class)">{{ $t('identity.' + data.class )}}</span>
                                </td>
                                <td>{{data.remoteIP}}</td>
                                <td>{{data.host}}</td>
                                <td>{{data.path}}</td>
                                <!--<td>{{data.content}}</td>-->
                                <td>{{data.createTime}}</td>
                                <td>
                                    <a class="btn btn-primary" @click="view(data.logRecordId)"><i
                                            class="fas fa-eye"></i>
                                        {{ $t('common.view') }}</a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div v-if="!isLoading && !logRecordList.length" class="text-notfound text-center">
                        <span class="text-danger">
                            {{ $t('common.notFound') }}
                        </span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <ul class="float-right">
                            <b-pagination
                                    v-model="pagination.page"
                                    :total-rows="pagination.total"
                                    :per-page="pagination.perPage"
                                    aria-controls="my-table"
                                    @input="getLogRecord()"
                            ></b-pagination>
                        </ul>
                    </div>
                </div>
            </b-overlay>
        </div>

        <!-- Modal Data-->
        <b-modal ref="logRecordEdit" cancel-title="取消" ok-title-html="確定" :title="$t('logRecord.' + method)" ok-only>
            <b-tabs content-class="mt-3" justified>
                <b-tab :title="$t('logRecord.detail')" active>
                    <div class="d-block text-center">
                        <form class="form-horizontal">
                            <div class="card-body">
                                <div class="form-group row text-left">
                                    <label for="Account" class="col-sm-3">{{ $t('logRecord.account') }} : </label>
                                    <div class="col-sm-9">
                                        <span class="" id="Account">{{ logRecord.account }}</span>
                                    </div>
                                </div>
                                <div class="form-group row text-left">
                                    <label for="class" class="col-sm-3">{{ $t('logRecord.class') }} : </label>
                                    <div class="col-sm-9">
                                        <span class="" id="class">{{ $t('identity.' + logRecord.class) }}</span>
                                    </div>
                                </div>
                                <div class="form-group row text-left">
                                    <label for="name" class="col-sm-3">{{ $t('logRecord.name') }} : </label>
                                    <div class="col-sm-9">
                                        <span class="" id="name">{{ $t('logRecordSetting.' + logRecord.name) }}</span>
                                    </div>
                                </div>
                                <div class="form-group row text-left">
                                    <label for="remoteIP" class="col-sm-3">{{ $t('logRecord.remoteIP') }} : </label>
                                    <div class="col-sm-9">
                                        <span class="" id="remoteIP">{{ logRecord.remoteIP }}</span>
                                    </div>
                                </div>
                                <!--<div class="form-group row text-left">-->
                                    <!--<label for="content" class="col-sm-3">{{ $t('logRecord.content') }} : </label>-->
                                    <!--<div class="col-sm-9">-->
                                        <!--<span class="" id="content" v-for=" (value,key) in logRecord.content"-->
                                              <!--:key="key">-->
                                            <!--{{ key }} : {{ value }}  <hr style="color:#999;"/>-->
                                        <!--</span>-->
                                    <!--</div>-->
                                <!--</div>-->
                                <div class="form-group row text-left">
                                    <label for="host" class="col-sm-3">{{ $t('logRecord.host') }} : </label>
                                    <div class="col-sm-9">
                                        <span class="" id="host">{{ logRecord.host }}</span>
                                    </div>
                                </div>
                                <div class="form-group row text-left">
                                    <label for="path" class="col-sm-3"> {{ $t('logRecord.path') }} : </label>
                                    <div class="col-sm-9">
                                        <span class="" id="path">{{ logRecord.path }}</span>
                                    </div>
                                </div>
                                <div class="form-group row text-left">
                                    <label for="newDate" class="col-sm-3"> {{ $t('logRecord.newDate') }} : </label>
                                    <div class="col-sm-9">
                                        <span class="" id="newDate">{{ logRecord.newDate }}</span>
                                    </div>
                                </div>
                                <div class="form-group row text-left">
                                    <label for="createTime" class="col-sm-3"> {{ $t('logRecord.createTime') }}
                                        : </label>
                                    <div class="col-sm-9">
                                        <span class="" id="createTime">{{ logRecord.createTime }}</span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </b-tab>
                <b-tab :title="$t('logRecord.serverInfo')">
                    <span v-for=" (value,key) in logRecord.serverInfo" :key="key">
                        {{ key }} : {{ value }} <hr style="color:#999;"/>
                    </span>
                </b-tab>
            </b-tabs>

        </b-modal>
    </section>
</template>

<script>
    export default {
        layout: 'admin',
        data() {
            return {
                //分頁
                pagination: {
                    page: 1,
                    perPage: 10,
                    total: 0,
                    totalPage: 0,
                },
                //操作日誌列表
                logRecordList: {},
                //操作日誌
                logRecord: {
                    content: '',
                    serverInfo: '',
                },
                //請求參數
                sentData: {
                    account: '',
                    class: '',
                    logId: '',
                    startDate: this.$dateTime.nowDate(),
                    endDate: this.$dateTime.tomorrow(),
                },
                //方法
                method: '',
                //是否loading
                isLoading: false,
                //傳送時間格式
                dateConfig: this.$calendar.date(),
                //狀態class
                classStatus: (status) => [
                    {'bg-success': status === 'S'},
                    {'bg-info': status === 'A'},
                    {'bg-warning': status === 'U'}
                ],
                //操作類型初始設定
                logRecordSetting: {
                    1: {'type': '', 'class': ''}
                },
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
                    this.getLogRecord(),
                    this.getLogRecordSetting()
                ])
            },

            /**
             * getLogRecord 取操作日誌
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getLogRecord() {
                this.isLoading = true
                const request = {
                    method: 'get',
                    url: '/api/logRecord',
                    params: {
                        page: this.pagination.page,
                        perPage: this.pagination.perPage,
                    }
                }

                if (this.sentData.account !== '') request.params.account = this.sentData.account
                if (this.sentData.class !== '') request.params.class = this.sentData.class
                if (this.sentData.logId !== '') request.params.logId = this.sentData.logId
                if (this.sentData.startDate !== '') request.params.startDate = this.sentData.startDate
                if (this.sentData.endDate !== '') request.params.endDate = this.sentData.endDate

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.logRecordList = response.data
                    this.pagination.page = response.pagination.page
                    this.pagination.perPage = response.pagination.perPage
                    this.pagination.total = response.pagination.total
                    this.pagination.totalPage = response.pagination.totalPage
                    this.isLoading = false
                }).catch((error) => {
	                this.$root.notify(error)
                })
            },

            /**
             * getLogRecordSetting 取操作記錄代碼
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getLogRecordSetting() {
                const request = {
                    method: 'get',
                    url: '/api/logRecord/getLogRecordSetting',
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.logRecordSetting = response.data;
                }).catch((error) => {
	                this.$root.notify(error)
                })
            },

            /**
             * view 檢視詳細操作日誌
             *
             * @param logRecordId
             * @since 0.0.1
             * @version 0.0.1
             */
            view(logRecordId) {
                const request = {
                    method: 'get',
                    url: '/api/logRecord/' + logRecordId,
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.logRecord = response.data
                    this.method = 'view'
                    this.$refs['logRecordEdit'].show();
                    this.logRecord.name = this.logRecordSetting[this.logRecord.logId]['class'];

                }).catch((error) => {
	                this.$root.notify(error)
                })
            },
        }
    }
</script>

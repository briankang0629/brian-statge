<template>
    <section class="content" id="permission-list">
        <div class="container-fluid">
            <!-- Tool Bar-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="float-sm-right" v-if="controlPermission === 'E'">
                        <router-link :to="{ name : 'permissionCreate' }" class="btn bg-success mr-2 mb-3" @click="create()">
                            <i class="fas fa-pen-nib"></i> {{ $t('common.create') }}
                        </router-link>
                        <a class="btn bg-danger mr-2 mb-3" @click="remove()" v-if="selected.length">
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
                        <b-row class="justify-content-md-left">
                            <!-- 名稱 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('permission.name') }}：</label>
                                <b-form-input class="mr-sm-2" v-model="sentData.name" :placeholder="$t('permission.name')"></b-form-input>
                            </b-col>

                            <!-- 狀態 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('permission.status') }}：</label>
                                <b-form-select v-model="sentData.status">
                                    <option v-for="(item, index) in $t('status')" :value="index" :key="index">
                                        {{item}}
                                    </option>
                                    <template #first>
                                        <b-form-select-option :value="null">-- {{ $t('common.choose') }} --
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-col>

                            <!-- 搜尋 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('permission.operate') }} ：</label>
                                <div class="col-sm-12">
                                    <b-button variant="outline-success" class="my-2 my-sm-0 ml-2" @click="getPermission()"><i class="fas fa-search"></i> {{ $t('message.search') }}</b-button>
                                </div>
                            </b-col>
                        </b-row>
                    </div>

                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped text-nowrap">
                            <thead>
                            <tr>
                                <th v-if="controlPermission === 'E'">{{ $t('common.select') }}</th>
                                <th>{{ $t('permission.permissionId') }}</th>
                                <th>{{ $t('permission.name') }}</th>
                                <th>{{ $t('permission.count') }}</th>
                                <th>{{ $t('permission.createTime') }}</th>
                                <th>{{ $t('permission.status') }}</th>
                                <th>{{ $t('permission.operate') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(data, index) in permissionList" :key="index">
                                <td v-if="controlPermission === 'E'"><input type="checkbox" :value="data.permissionId" v-model="selected" /></td>
                                <td>{{data.permissionId}}</td>
                                <td>{{data.name}}</td>
                                <td>{{data.adminCount}}</td>
                                <td>{{data.createTime}}</td>
                                <td>
                                    <span class="badge " :class="classStatus(data.status)">{{$t('status.' + data.status)}}</span>
                                </td>
                                <td>
                                    <router-link :to="{ name: 'permissionEdit', params: { id: data.permissionId }}" class="btn btn-primary">
                                        <span v-if="controlPermission === 'E'">
                                            <i class="fas fa-pencil-alt"></i> {{ $t('common.edit') }}
                                        </span>
                                        <span v-if="controlPermission === 'V'">
                                            <i class="fas fa-eye"></i> {{ $t('common.view') }}
                                        </span>
                                    </router-link>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div v-if="!isLoading && !permissionList.length" class="text-notfound text-center">
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
                                    @input="getPermission()"
                            ></b-pagination>
                        </ul>
                    </div>
                </div>
            </b-overlay>
        </div>
    </section>
</template>

<script>
    export default {
        layout: 'admin',
        data() {
            return {
                pagination: {
                    page: 1,
                    perPage: 10,
                    total: 0,
                    totalPage: 0,
                },
                permissionList: {},
                permissionConfig: {},
                status: {
                    'E': this.$t('permissionStatus.E'),
                    'V': this.$t('permissionStatus.V'),
                    'N': this.$t('permissionStatus.N')
                },
                method: '',
                classStatus : (status) => [
                    {'bg-success': status === 'Y'},
                    {'bg-danger': status === 'N'},
                    {'bg-warning': status === 'U'}
                ],
                selected: [],
                //請求參數
                sentData:{
                    name:'',
                    status:null,
                },
                isLoading: false,
                controlPermission: ''
            }
        },
        computed: {
        },
        mounted() {
	        //todo 因demo開放google登入 指開放讀得權限 到時關閉第三方後台登入再拿掉
	        this.controlPermission = localStorage.getItem('permission') ?
		        JSON.parse(localStorage.getItem('permission')).admin.permission : 'V'

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
                    this.getPermission(),
                    this.getPermissionConfig()
                ])
            },

            /**
             * getPermissionConfig 取權限預設設定檔
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getPermissionConfig() {
                const request = {
                    method: 'get',
                    url: '/api/permission/config',
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.permissionConfig = response.data;
                }).catch((error) => {
	                this.$root.notify(error)
                })
            },

            /**
             * getPermission 取指定的權限資料
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getPermission() {
                this.isLoading = true
                const request = {
                    method: 'get',
                    url: '/api/permission',
                    params: {
                        page: this.pagination.page,
                        perPage: this.pagination.perPage
                    }
                }

                if(this.sentData.name !== '') request.params.name = this.sentData.name
                if(this.sentData.status !== '') request.params.status = this.sentData.status

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.permissionList = response.data
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
             * remove 刪除指定一筆權限資料
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            remove() {
	            let askSetting = {
		            title: this.$t('message.askDelete'),
		            text: this.$t('message.askDeleteMessage'),
		            confirmText: this.$t('common.confirm'),
		            cancelText: this.$t('common.cancel'),
	            }

	            //詢問刪除
	            this.$Swal.ask(askSetting).then((result) => {
                    if (result.isConfirmed) {
                        this.isLoading = true
                        let permissionId = this.selected.join(',')
                        //請求刪除
                        const request = {
                            method: 'delete',
                            url: '/api/permission/' + permissionId,
                        }

                        //請求刪除前驗證
                        this.$store.dispatch('authRequest', request).then((response) => {
	                        this.$root.notify(response)
                            this.getPermission()
                        }).catch((error) => {
	                        this.$root.notify(error)
                            this.isLoading = false
                        })
                    }
                    //請空選擇
                    this.selected = []
                })
            }
        }
    }
</script>

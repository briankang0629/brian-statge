<template>
    <section class="content">
        <div class="container-fluid">
            <!-- Tool Bar-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="float-sm-right" v-if="controlPermission === 'E'">
                        <router-link :to="{ name : 'adminCreate' }" class="btn bg-success mr-3 mb-3">
                            <i class="fas fa-pen-nib"></i> {{ $t('common.create') }}
                        </router-link>
                        <a class="btn bg-danger mr-2 mb-3" @click="remove()" v-if="selected.length">
                            <i class="fas fa-trash"></i> {{ $t('common.delete') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Table-->
            <b-overlay :show="isLoading" rounded="sm">
                <div class="card card-table">
                    <div class="card-header">
                        <label class="badge bg-success">{{ $t('common.searchBar') }}</label>
                        <b-row class="justify-content-md-left">
                            <!-- 帳號 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('admin.account') }}：</label>
                                <b-form-input class="mr-sm-2" v-model="sentData.account" :placeholder="$t('admin.account')"></b-form-input>
                            </b-col>

                            <!-- 姓名 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('admin.name') }}：</label>
                                <b-form-input class="mr-sm-2" v-model="sentData.name" :placeholder="$t('admin.name')"></b-form-input>
                            </b-col>

                            <!-- email -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('admin.email') }}：</label>
                                <b-form-input class="mr-sm-2" v-model="sentData.email" :placeholder="$t('admin.email')"></b-form-input>
                            </b-col>

                            <!-- 狀態 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('admin.status') }}：</label>
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

                            <!-- 權限 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('admin.permission') }}：</label>
                                <b-form-select v-model="sentData.permissionId">
                                    <option v-for="(item, index) in permission" :value="item.permissionId" :key="index">
                                        {{item.name}}
                                    </option>
                                    <template #first>
                                        <b-form-select-option :value="null">-- {{ $t('common.choose') }} --
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-col>

                            <!-- 搜尋 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('admin.operate') }} ：</label>
                                <div class="col-sm-12">
                                    <b-button variant="outline-success" class="my-2 my-sm-0 ml-2" @click="getAdmin()"> <i class="fas fa-search"></i> {{ $t('message.search') }}</b-button>
                                </div>
                            </b-col>
                        </b-row>

                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped text-nowrap">
                            <thead>
                                <tr>
                                <th v-if="controlPermission === 'E'" >{{ $t('common.select') }}</th>
                                <th>{{ $t('common.image') }}</th>
                                <th>{{ $t('admin.adminId') }}</th>
                                <th>{{ $t('admin.name') }}</th>
                                <th>{{ $t('admin.account') }}</th>
                                <th>{{ $t('admin.permission') }}</th>
                                <th>{{ $t('admin.email') }}</th>
                                <th>{{ $t('admin.createTime') }}</th>
                                <th>{{ $t('admin.isSub') }}</th>
                                <th>{{ $t('admin.status') }}</th>
                                <th>{{ $t('admin.operate') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data, index) in adminList" :key="index">
                                <td v-if="controlPermission === 'E'"><input type="checkbox" :value="data.adminId" v-model="selected"/></td>
                                <td class="admin-image">
                                    <b-img :src="data.picture" alt="admin-image" v-if="data.picture"></b-img>
                                    <b-img :src="notFound" alt="notFound-image" v-else></b-img>
                                </td>
                                <td>{{data.adminId}}</td>
                                <td>{{data.name}}</td>
                                <td>{{data.account}}</td>
                                <td>{{data.permission}}</td>
                                <td>{{data.email}}</td>
                                <td>{{data.createTime}}</td>
                                <td><span class="badge"
                                          :class="classStatus(data.sub)">{{ $t('sub.' + data.sub) }}</span>
                                    </td>
                                <td><span class="badge"
                                          :class="classStatus(data.status)">{{$t('status.' + data.status)}}</span>
                                </td>
                                <td>
                                    <router-link :to="{ name: 'adminEdit', params: { id: data.adminId }}" class="btn btn-primary">
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
                        <div v-if="!isLoading && !adminList.length" class="text-notfound text-center">
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
                                    @input="getAdmin()"
                            ></b-pagination>
                        </ul>
                    </div>
                </div>
            </b-overlay>
        </div>
    </section>
</template>

<script>
	//notFound-image
	import notFound from '../../assets/notFound.png'

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
                adminList: {},
                admin: {
                    status: null,
                    permissionId: null,
                    password: '',
                },
                permission: [],
                method: '',
                classStatus: (status) => [
                    {'bg-success': status === 'Y'},
                    {'bg-danger': status === 'N'},
                    {'bg-warning': status === 'U'}
                ],
                //選擇
                selected: [],
                //notfound-image
	            notFound: notFound,
                //請求參數
                sentData:{
                    account:'',
                    name:'',
                    status:null,
                    permissionId:null,
                    email:''
                },
                //loading
                isLoading: false,
                //操作的權限
                controlPermission: ''
            }
        },
        computed: {},
        mounted() {
        	//todo 因demo開放google登入 指開放讀得權限 到時關閉第三方後台登入再拿掉
            this.controlPermission = localStorage.getItem('permission') ?
	            JSON.parse(localStorage.getItem('permission')).admin.adminList : 'V'

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
                    this.getAdmin(),
                    this.getPermission()
                ])
            },

            /**
             * getAdmin 取管理者
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getAdmin() {
                this.isLoading = true
                const request = {
                    method: 'get',
                    url: '/api/admin',
                    params: {
                        page: this.pagination.page,
                        perPage: this.pagination.perPage
                    }
                }

                if(this.sentData.account !== '') request.params.account = this.sentData.account
                if(this.sentData.name !== '') request.params.name = this.sentData.name
                if(this.sentData.email !== '') request.params.email = this.sentData.email
                if(this.sentData.status !== '') request.params.status = this.sentData.status
                if(this.sentData.permissionId !== '') request.params.permissionId = this.sentData.permissionId

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.adminList = response.data
                    this.pagination.page = response.pagination.page
                    this.pagination.perPage = response.pagination.perPage
                    this.pagination.total = response.pagination.total
                    this.pagination.totalPage = response.pagination.totalPage
                    this.isLoading = false
                }).catch((error) => {
                    console.log(error)
                })
            },

            /**
             * getPermission 取權限
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getPermission() {
                const request = {
                    method: 'get',
                    url: '/api/permission',
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.permission = response.data;
                }).catch((error) => {
                    console.log(error)
                })
            },

            /**
             * remove 刪除
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            remove() {
                //詢問刪除
                this.$Swal.fire({
                    title: this.$t('message.askDelete'),
                    text: this.$t('message.askDeleteMessage'),
                    icon: 'warning',
                    confirmButtonText: this.$t('common.confirm'),
                    cancelButtonText: this.$t('common.cancel'),
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    showCancelButton: true,
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        let adminId = this.selected.join(',')
                        //請求刪除
                        const request = {
                            method: 'delete',
                            url: '/api/admin/' + adminId,
                        }

                        //請求刪除前驗證
                        this.$store.dispatch('authRequest', request).then((response) => {
                            this.$Swal.fire({
                                icon: 'success',
                                title: this.$t('message.success'),
                                text: response.msg
                            })

                            this.getAdmin()
                        }).catch((error) => {
                            this.$Swal.fire({
                                icon: 'error',
                                title: this.$t('message.error'),
                                text: error.msg
                            })
                        })
                    }
                    //請空選擇
                    this.selected = []
                })

            }
        }
    }
</script>
<style>
    th, td {
        text-align: center;
    }

    .admin-image > img {
        width: 50px;
        height: 50px;
    }
</style>
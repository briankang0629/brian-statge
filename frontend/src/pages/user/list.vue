<template>
    <section class="content">
        <div class="container-fluid">
            <!-- Tool Bar-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="float-sm-right" v-if="controlPermission === 'E'">
                        <router-link :to="{ name : 'userCreate' }" class="btn bg-success mr-2 mb-3">
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
                            <!-- 帳號 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('user.account') }}：</label>
                                <b-form-input class="mr-sm-2" v-model="sentData.account"
                                              :placeholder="$t('user.account')"></b-form-input>
                            </b-col>

                            <!-- 姓名 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('user.name') }}：</label>
                                <b-form-input class="mr-sm-2" v-model="sentData.name"
                                              :placeholder="$t('user.name')"></b-form-input>
                            </b-col>

                            <!-- email -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('user.email') }}：</label>
                                <b-form-input class="mr-sm-2" v-model="sentData.email"
                                              :placeholder="$t('user.email')"></b-form-input>
                            </b-col>

                            <!-- 狀態 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('user.status') }}：</label>
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
                                <label class="mr-sm-2 ml-2">{{ $t('user.userGroup') }}：</label>
                                <b-form-select v-model="sentData.userGroupId">
                                    <option v-for="(item, index) in userGroup" :value="item.userGroupId" :key="index">
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
                                <label class="mr-sm-2 ml-2">{{ $t('user.operate') }} ：</label>
                                <div class="col-sm-12">
                                    <b-button variant="outline-success" class="my-2 my-sm-0 ml-2" @click="getUser()"><i class="fas fa-search"></i> {{ $t('message.search') }}
                                    </b-button>
                                </div>
                            </b-col>
                        </b-row>
                    </div>

                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped text-nowrap">
                            <thead>
                            <tr>
                                <th v-if="controlPermission === 'E'">{{ $t('common.select') }}</th>
                                <th>{{ $t('user.userId') }}</th>
                                <th>{{ $t('user.name') }}</th>
                                <th>{{ $t('user.account') }}</th>
                                <th>{{ $t('user.userGroup') }}</th>
                                <th>{{ $t('user.email') }}</th>
                                <th>{{ $t('user.mobile') }}</th>
                                <th>{{ $t('user.createTime') }}</th>
                                <th>{{ $t('user.status') }}</th>
                                <th>{{ $t('user.operate') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(data, index) in userList" :key="index">
                                <td v-if="controlPermission === 'E'"><input type="checkbox" :value="data.userId"
                                                                            v-model="selected"/></td>
                                <td>{{data.userId}}</td>
                                <td>{{data.name}}</td>
                                <td>{{data.account}}</td>
                                <td>{{data.userGroup}}</td>
                                <td>{{data.email}}</td>
                                <td>{{data.mobile}}</td>
                                <td>{{data.createTime}}</td>
                                <td><span class="badge "
                                          :class="classStatus(data.status)">{{$t('status.' + data.status)}}</span></td>
                                <td>
                                    <router-link :to="{ name: 'userEdit', params: { id: data.userId }}" class="btn btn-primary">
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
                        <div v-if="!isLoading && !userList.length" class="text-notfound text-center">
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
                                    @input="getUser()"
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
                userList: {},
                user: {
                    status: null,
                    userGroupId: null,
                    password: '',
                },
                userGroup: [],
                method: '',
                classStatus: (status) => [
                    {'bg-success': status === 'Y'},
                    {'bg-danger': status === 'N'},
                    {'bg-warning': status === 'U'}
                ],
                //請求參數
                sentData:{
                    account:'',
                    name:'',
                    status:null,
                    userGroupId:null,
                    email:''
                },
                selected: [],
                isLoading: false,
                controlPermission: ''
            }
        },
        computed: {},
        mounted() {
	        //todo 因demo開放google登入 指開放讀得權限 到時關閉第三方後台登入再拿掉
	        this.controlPermission = localStorage.getItem('permission') ?
		        JSON.parse(localStorage.getItem('permission')).user.userList : 'V'

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
                    this.getUser(),
                    this.getUserGroup()
                ])
            },

            /**
             * getUser 取使用者
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getUser() {
                this.isLoading = true;
                const request = {
                    method: 'get',
                    url: '/api/user',
                    params: {
                        page: this.pagination.page,
                        perPage: this.pagination.perPage
                    }
                }

                if(this.sentData.account !== '') request.params.account = this.sentData.account
                if(this.sentData.name !== '') request.params.name = this.sentData.name
                if(this.sentData.email !== '') request.params.email = this.sentData.email
                if(this.sentData.status !== '') request.params.status = this.sentData.status
                if(this.sentData.userGroupId !== '') request.params.userGroupId = this.sentData.userGroupId

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.userList = response.data
                    this.pagination.page = response.pagination.page
                    this.pagination.perPage = response.pagination.perPage
                    this.pagination.total = response.pagination.total
                    this.pagination.totalPage = response.pagination.totalPage
                    this.isLoading = false;
                }).catch((error) => {
                    console.log(error)
                })
            },

            /**
             * getUserGroup 取使用者群組
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getUserGroup() {
                const request = {
                    method: 'get',
                    url: '/api/userGroup',
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.userGroup = response.data;
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
                        let userId = this.selected.join(',')
                        //請求刪除
                        const request = {
                            method: 'delete',
                            url: '/api/user/' + userId,
                        }

                        //請求刪除前驗證
                        this.$store.dispatch('authRequest', request).then((response) => {
                            this.$Swal.fire({
                                icon: 'success',
                                title: this.$t('message.success'),
                                text: response.msg
                            })

                            this.getUser()
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
</style>
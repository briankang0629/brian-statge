<template>
    <section class="content">
        <div class="container-fluid">
            <!-- Tool Bar-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="float-sm-right" v-if="controlPermission === 'E'">
                        <router-link :to="{ name: 'adminList' }" class="btn bg-danger mr-2 mb-3">
                            <i class="fas fa-reply"></i> {{ $t('common.back') }}
                        </router-link>
                    </div>
                </div>
            </div>
            <b-overlay :show="isLoading" rounded="sm">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title"> <i class="fas fa-edit"></i>  {{ $t('admin.' + method) }}</h3>
                            </div>
                            <form class="form-horizontal mt-3">
                                <div class="card-body">
                                    <div class="form-group row text-left">
                                        <label for="Account" class="col-sm-2 col-form-label">{{ $t('admin.account') }} : </label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="Account" v-model="admin.account"
                                                   :placeholder="$t('admin.account')" :disabled="method !== 'create'">
                                        </div>
                                    </div>
                                    <div class="form-group row text-left">
                                        <label class="col-sm-2 col-form-label"> {{ $t('admin.permission') }} : </label>
                                        <div class="col-sm-10">
                                            <b-form-select v-model="admin.permissionId" :disabled="controlPermission !== 'E'">
                                                <option v-for="(item, index) in permission" :value="item.permissionId" :key="index">
                                                    {{item.name}}
                                                </option>
                                                <template #first>
                                                    <b-form-select-option :value="null">-- {{ $t('common.choose') }} --
                                                    </b-form-select-option>
                                                </template>
                                            </b-form-select>
                                        </div>
                                    </div>
                                    <div class="form-group row text-left">
                                        <label for="name" class="col-sm-2 col-form-label">{{ $t('admin.name') }} : </label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="name" v-model="admin.name"
                                                   :placeholder="$t('admin.name')" :disabled="controlPermission !== 'E'">
                                        </div>
                                    </div>
                                    <div class="form-group row text-left">
                                        <label for="password" class="col-sm-2 col-form-label">{{ $t('admin.password') }} : </label>
                                        <div class="col-sm-10">
                                            <input type="password" class="form-control" id="password" v-model="admin.password"
                                                   :placeholder="$t('admin.password')" :disabled="controlPermission !== 'E'">
                                        </div>
                                    </div>
                                    <div class="form-group row text-left">
                                        <label for="password" class="col-sm-2 col-form-label">{{ $t('admin.confirm') }} : </label>
                                        <div class="col-sm-10">
                                            <input type="password" class="form-control" id="confirm"
                                                   v-model="admin.confirm" :placeholder="$t('admin.confirm')" :disabled="controlPermission !== 'E'">
                                        </div>
                                    </div>
                                    <div class="form-group row text-left">
                                        <label for="email" class="col-sm-2 col-form-label">{{ $t('admin.email') }} : </label>
                                        <div class="col-sm-10">
                                            <input type="email" class="form-control" id="email" v-model="admin.email"
                                                   :placeholder="$t('admin.email')" :disabled="method !== 'create'">
                                        </div>
                                    </div>
                                    <div class="form-group row text-left">
                                        <label class="col-sm-2 col-form-label"> {{ $t('admin.status') }} : </label>
                                        <div class="col-sm-10">
                                            <b-form-select v-model="admin.status" :options="$t('status')" :disabled="controlPermission !== 'E'">
                                                <template #first>
                                                    <b-form-select-option :value="null" disabled>-- {{ $t('common.choose') }} --
                                                    </b-form-select-option>
                                                </template>
                                            </b-form-select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="text-right">
                                        <router-link :to="{ name: 'adminList'}" class="btn bg-danger mr-3">
                                            <i class="fas fa-reply"></i> {{ $t('common.back') }}
                                        </router-link>
                                        <b-button type="button" class="btn btn-success" @click="save()">
                                            <i class="fas fa-pen-nib"></i>  {{ $t('common.' + method) }}
                                        </b-button>
                                    </div>
                                </div>
                            </form>
                        </div>
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
                    // {'bg-warning': status === 'U'}
                ],

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

            //方法
            this.method = !this.$route.params.id ? 'create' : 'edit'

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
                if(this.method === 'edit') {
                    return Promise.all([
                        this.edit(this.$route.params.id),
                        this.getPermission()
                    ])
                } else {
                    return Promise.all([
                        this.getPermission()
                    ])
                }
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
             * edit 修改管理者
             *
             * @param adminId
             * @since 0.0.1
             * @version 0.0.1
             */
            edit(adminId) {
                this.isLoading = true
                //請求參數
                const request = {
                    method: 'get',
                    url: '/api/admin/' + adminId,
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.admin = response.data
                    this.admin.password = '';

                    this.isLoading = false
                }).catch((error) => {
                    console.log(error)
                    this.isLoading = false
                })
            },

            /**
             * save 儲存資訊
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            save() {
                const request = {
                    method: (this.method == 'create') ? 'post' : 'put',
                    url: '/api/admin/' + (this.method == 'create' ? 'store' : 'update/' + this.$route.params.id),
                    data: this.qs.stringify(this.admin)
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.$Swal.fire({
                        icon: 'success',
                        title: this.$t('message.success'),
                        text: response.msg
                    }).then(() => {
                        this.$router.push({ name : 'adminList'})
                    })

                }).catch((error) => {
                    this.$Swal.fire({
                        icon: 'error',
                        title: this.$t('message.error'),
                        text: error.msg
                    })
                })
            },
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
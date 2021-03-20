<template>
    <section class="content" id="permission-edit">
        <div class="container-fluid">
            <!-- Tool Bar-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="float-sm-right" v-if="controlPermission === 'E'">
                        <router-link :to="{ name: 'permission' }" class="btn bg-danger mr-2 mb-3">
                            <i class="fas fa-reply"></i> {{ $t('common.back') }}
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <b-overlay :show="isLoading" rounded="sm">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title"> <i class="fas fa-edit"></i>{{ $t('permission.' + method) }}</h3>
                            </div>
                            <form class="form-horizontal mt-3">
                                <div class="card-body">
                                    <div class="form-group row text-left">
                                        <label for="name" class="col-sm-3 col-form-label">{{ $t('permission.name') }} : </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="name" v-model="permission.name" :placeholder="$t('permission.name')" :disabled="controlPermission !== 'E'">
                                        </div>
                                    </div>
                                    <!-- 權限細項設定 -->
                                    <div class="form-group row text-left permission-setting">
                                        <label class="col-sm-3 col-form-label">{{ $t('permission.permission') }} : </label>
                                        <div class="col-sm-9">
                                            <div class="accordion" role="tablist">
                                                <!-- 權限 -->
                                                <b-card no-body class="mb-1" v-for="(data, menu) in permissionConfig" :key="menu">
                                                    <b-card-header header-tag="header" class="p-1" role="tab">
                                                        <b-button block v-b-toggle="menu" variant="info">{{ $t('menu.' + menu) }}</b-button>
                                                    </b-card-header>
                                                    <b-collapse :id="menu" visible accordion="my-accordion" role="tabpanel">
                                                        <b-card-body>
                                                            <div v-for="(sub, subMenu) in data" :key="subMenu">
                                                                <b-form-group :label="$t('menu.' + subMenu)">
                                                                    <b-form-radio-group
                                                                            v-model="permission.permission[menu][subMenu]"
                                                                            :options="status"
                                                                            class="mb-3"
                                                                            value-field="item"
                                                                            text-field="name"
                                                                            :disabled="controlPermission !== 'E'"
                                                                    ></b-form-radio-group>
                                                                </b-form-group>
                                                            </div>
                                                        </b-card-body>
                                                    </b-collapse>
                                                </b-card>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row text-left">
                                        <label class="col-sm-3 col-form-label"> {{ $t('permission.status') }} : </label>
                                        <div class="col-sm-9">
                                            <b-form-select v-model="permission.status" :options="$t('status')" :disabled="controlPermission !== 'E'">
                                                <template #first>
                                                    <b-form-select-option :value="null" disabled>-- {{ $t('common.choose') }} --</b-form-select-option>
                                                </template>
                                            </b-form-select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="text-right">
                                        <router-link :to="{ name: 'permission'}" class="btn bg-danger mr-3">
                                            <i class="fas fa-reply"></i> {{ $t('common.back') }}
                                        </router-link>
                                        <b-button type="button" class="btn btn-success" @click="save()" v-if="controlPermission === 'E'">
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
                permission: {
                    name: '',
                    permission: {},
                    status: null
                },
                permissionConfig: {},
                status: {
                    'E': this.$t('permissionStatus.E'),
                    'V': this.$t('permissionStatus.V'),
                    'N': this.$t('permissionStatus.N')
                },
                method: '',
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
                    ])
                } else {
                    return Promise.all([
                        this.getPermissionConfig()
                    ])
                }
            },

            /**
             * getPermissionConfig 取權限預設設定檔
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getPermissionConfig() {
                this.isLoading = true
                //請求參數
                const request = {
                    method: 'get',
                    url: '/api/permission/config',
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.permissionConfig = response.data;
                    this.permission.permission = this.permissionConfig
                    this.isLoading = false
                }).catch((error) => {
	                this.$root.notify(error)
                    this.isLoading = false
                })
            },

            /**
             * edit 修改指定一筆權限資料
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            edit( permissionId ) {
                this.isLoading = true
                //請求參數
                const request = {
                    method: 'get',
                    url: '/api/permission/' + permissionId,
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.permission = response.data
                    this.permissionConfig = response.data.permission
                    this.isLoading = false
                }).catch((error) => {
	                this.$root.notify(error)
                    this.isLoading = false
                })
            },

            /**
             * save 儲存送出
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            save() {
                const request = {
                    method: (this.method === 'create') ? 'post' : 'put',
                    url: '/api/permission/' + (this.method === 'create' ? 'store' : 'update/' + this.permission.permissionId),
                    data: this.qs.stringify(this.permission)
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.$router.push({ name : 'permission'}).then(() => { this.$root.notify(response) })
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
                        })
                    }
                    //請空選擇
                    this.selected = []
                })
            }
        }
    }
</script>

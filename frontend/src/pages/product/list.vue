<template>
    <section class="content">
        <div class="container-fluid">
            <!-- Tool Bar-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="float-sm-right" v-if="controlPermission === 'E'">
                        <router-link :to="{ name : 'productCreate' }" class="btn bg-success mr-3 mb-3">
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
                            <!-- 型號 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('product.model') }}：</label>
                                <b-form-input class="mr-sm-2" v-model="sentData.model" :placeholder="$t('product.model')"></b-form-input>
                            </b-col>

                            <!-- 名稱 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('product.name') }}：</label>
                                <b-form-input class="mr-sm-2" v-model="sentData.name" :placeholder="$t('product.name')"></b-form-input>
                            </b-col>

                            <!-- 語系 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('product.language') }}：</label>
                                <b-form-select v-model="sentData.language">
                                    <option v-for="(item, index) in $t('language')" :value="index" :key="index">
                                        {{item}}
                                    </option>
                                    <template #first>
                                        <b-form-select-option :value="null">-- {{ $t('common.choose') }} --
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-col>

                            <!-- 狀態 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('product.status') }}：</label>
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

                            <!-- 商品分類 -->
                            <b-col lg="2" md="6" sm="12" class="mt-3">
                                <label class="mr-sm-2 ml-2">{{ $t('product.productCategory') }}：</label>
                                <b-form-select v-model="sentData.productCategoryId">
                                    <option v-for="(item, index) in productCategory" :value="item.productCategoryId" :key="index">
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
                                <label class="mr-sm-2 ml-2">{{ $t('product.operate') }} ：</label>
                                <div class="col-sm-12">
                                    <b-button variant="outline-success" class="my-2 my-sm-0 ml-2" @click="getProduct()"><i class="fas fa-search"></i> {{ $t('message.search') }}</b-button>
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
                                <th>{{ $t('product.productId') }}</th>
                                <th>{{ $t('product.model') }}</th>
                                <th>{{ $t('product.name') }}</th>
                                <th>{{ $t('product.costPrice') }}</th>
                                <th>{{ $t('product.price') }}</th>
                                <th>{{ $t('product.productCategory') }}</th>
                                <th>{{ $t('product.createTime') }}</th>
                                <th>{{ $t('product.status') }}</th>
                                <th>{{ $t('product.operate') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data, index) in productList" :key="index">
                                <td v-if="controlPermission === 'E'"><input type="checkbox" :value="data.productId" v-model="selected"/></td>
                                <td class="product-image">
                                    <b-img :src="data.picture" alt="product-image" v-if="data.picture"></b-img>
                                    <b-img :src="notFound" alt="notFound-image" v-else></b-img>
                                </td>
                                <td>{{data.productId}}</td>
                                <td>{{data.model}}</td>
                                <td>{{data.name}}</td>
                                <td>{{data.costPrice}}</td>
                                <td>{{data.price}}</td>
                                <td>{{data.productCategory}}</td>
                                <td>{{data.createTime}}</td>
                                <td><span class="badge"
                                          :class="classStatus(data.status)">{{$t('status.' + data.status)}}</span>
                                </td>
                                <td>
                                    <router-link :to="{ name: 'productEdit', params: { id: data.productId }}" class="btn btn-primary">
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
                        <div v-if="!isLoading && !productList.length" class="text-notfound text-center">
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
                                    @input="getProduct()"
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
                //分頁資料
                pagination: {
                    page: 1,
                    perPage: 10,
                    total: 0,
                    totalPage: 0,
                },
                //商品清單
                productList: {},
                //商品分類
                productCategory: {},
                //狀態列的class
                classStatus: (status) => [
                    {'bg-success': status === 'Y'},
                    {'bg-danger': status === 'N'},
                ],
                //選擇
                selected: [],
                //notfound-image
	            notFound: notFound,
                //請求參數
                sentData: {
                    model:'',
                    name:'',
                    language: null,
                    status: null,
                    productCategoryId: null,
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
	            JSON.parse(localStorage.getItem('permission')).product.productList : 'V'

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
                    this.getProduct(),
                    this.getProductCategory()
                ])
            },

            /**
             * getProduct 取商品
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getProduct() {
                this.isLoading = true
                const request = {
                    method: 'get',
                    url: '/api/product',
                    params: {
                        page: this.pagination.page,
                        perPage: this.pagination.perPage
                    }
                }

                if(this.sentData.model !== '') request.params.model = this.sentData.model
                if(this.sentData.name !== '') request.params.name = this.sentData.name
                if(this.sentData.language !== '') request.params.language = this.sentData.language
                if(this.sentData.status !== '') request.params.status = this.sentData.status
                if(this.sentData.productCategoryId !== '') request.params.productCategoryId = this.sentData.productCategoryId

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.productList = response.data
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
             * getProductCategory 取商品分類
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getProductCategory() {
                const request = {
                    method: 'get',
                    url: '/api/product/category',
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.productCategory = response.data;
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
                        let productId = this.selected.join(',')
                        //請求刪除
                        const request = {
                            method: 'delete',
                            url: '/api/product/' + productId,
                        }

                        //請求刪除前驗證
                        this.$store.dispatch('authRequest', request).then((response) => {
                            this.$Swal.fire({
                                icon: 'success',
                                title: this.$t('message.success'),
                                text: response.msg
                            })

                            this.getProduct()
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

    .product-image > img {
        width: 50px;
        height: 50px;
    }
</style>
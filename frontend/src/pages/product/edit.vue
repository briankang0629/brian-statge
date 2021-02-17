<template>
    <section class="content" id="product-edit">
        <div class="container-fluid">
            <!-- Tool Bar-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="float-sm-right" v-if="controlPermission === 'E'">
                        <router-link :to="{ name: 'productList' }" class="btn bg-danger mr-2 mb-3">
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
                                <span class="card-title"> <i class="fas fa-edit"></i> {{ $t('product.' + method) }}</span>
                            </div>
                            <div class="card-body">
                                <b-tabs content-class="mt-3"  pills card>
                                    <!--基本設定-->
                                    <b-tab :title="$t('product.basic')" active>
                                        <div class="d-block text-center">
                                            <form class="form-horizontal mt-3">
                                                <div class="card-body">
                                                    <div class="form-group row text-left">
                                                        <label for="model" class="col-sm-2 col-form-label">{{ $t('product.model') }} : </label>
                                                        <div class="col-sm-10">
                                                            <input type="text" class="form-control" id="model" v-model="product.model"
                                                                   :placeholder="$t('product.model')" :disabled="controlPermission !== 'E'">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row text-left">
                                                        <label class="col-sm-2 col-form-label"> {{ $t('product.productCategory') }} : </label>
                                                        <div class="col-sm-10">
                                                            <vue-multi-select v-model="product.category"
                                                                              :placeholder="$t('product.selectMsg')"
                                                                              :selectLabel="$t('common.select')"
                                                                              :deselectLabel="$t('common.remove')"
                                                                              :options="productCategory.map(item => item.productCategoryId)"
                                                                              :custom-label="value => productCategory.find(item => item.productCategoryId === value).name"
                                                                              :multiple="true"
                                                                              :taggable="true"
                                                                              :disabled="controlPermission !== 'E'"
                                                            ></vue-multi-select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row text-left">
                                                        <label for="costPrice" class="col-sm-2 col-form-label">{{ $t('product.costPrice') }} : </label>
                                                        <div class="col-sm-10">
                                                            <input type="text" class="form-control" id="costPrice" v-model="product.costPrice"
                                                                   :placeholder="$t('product.costPrice')" :disabled="controlPermission !== 'E'">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row text-left">
                                                        <label for="price" class="col-sm-2 col-form-label">{{ $t('product.price') }} : </label>
                                                        <div class="col-sm-10">
                                                            <input type="text" class="form-control" id="price"
                                                                   v-model="product.price" :placeholder="$t('product.price')" :disabled="controlPermission !== 'E'">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row text-left">
                                                        <label for="sortOrder" class="col-sm-2 col-form-label">{{ $t('product.sortOrder') }} : </label>
                                                        <div class="col-sm-10">
                                                            <input type="number" class="form-control" id="sortOrder" v-model="product.sortOrder"
                                                                   :placeholder="$t('product.sortOrder')" :disabled="controlPermission !== 'E'">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row text-left">
                                                        <label class="col-sm-2 col-form-label"> {{ $t('product.status') }} : （ {{ $t('status.' + product.status) }} ）</label>
                                                        <div class="col-sm-10 pt-3">
                                                            <vue-switch
                                                                v-model="status"
                                                                theme="bootstrap"
                                                                color="success"
                                                                type-bold="true"
                                                            >
                                                            </vue-switch>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </b-tab>
                                    <!--語系設定-->
                                    <b-tab :title="$t('product.description')">
                                        <div class="d-block text-center">
                                            <form class="form-horizontal">
                                                <div class="text-left mb-3">
                                                    <label class="badge bg-success" >{{ $t('product.language') }}</label>
                                                </div>
                                                <b-card no-body>
                                                    <b-tabs pills card>
                                                        <b-tab :title="$t('language.' + data.language)" v-for="(data, index) in product.detail" :key="index">
                                                            <div class="card-body">
                                                                <div class="form-group row text-left">
                                                                    <label for="name" class="col-sm-2 col-form-label">{{ $t('product.name') }} : </label>
                                                                    <div class="col-sm-10">
                                                                        <input type="text" class="form-control" id="name" v-model="product.detail[index].name"
                                                                               :placeholder="$t('product.name')" :disabled="controlPermission !== 'E'">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row text-left">
                                                                    <label class="col-sm-2 col-form-label">{{ $t('product.description') }} : </label>
                                                                    <div class="col-sm-10">
                                                                        <ckeditor type="classic" v-model="product.detail[index].description"></ckeditor>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row text-left">
                                                                    <label for="metaTitle" class="col-sm-2 col-form-label">{{ $t('product.metaTitle') }} : </label>
                                                                    <div class="col-sm-10">
                                                                        <input type="text" class="form-control" id="metaTitle" v-model="product.detail[index].metaTitle"
                                                                               :placeholder="$t('product.metaTitle')" :disabled="controlPermission !== 'E'">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row text-left">
                                                                    <label for="metaKeyword" class="col-sm-2 col-form-label">{{ $t('product.metaKeyword') }} : </label>
                                                                    <div class="col-sm-10">
                                                                        <input type="text" class="form-control" id="metaKeyword" v-model="product.detail[index].metaKeyword"
                                                                               :placeholder="$t('product.metaKeyword')" :disabled="controlPermission !== 'E'">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row text-left">
                                                                    <label for="metaDescription" class="col-sm-2 col-form-label">{{ $t('product.metaDescription') }} : </label>
                                                                    <div class="col-sm-10">
                                                                        <input type="text" class="form-control" id="metaDescription" v-model="product.detail[index].metaDescription"
                                                                               :placeholder="$t('product.metaDescription')" :disabled="controlPermission !== 'E'">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </b-tab>
                                                    </b-tabs>
                                                </b-card>
                                            </form>
                                        </div>
                                    </b-tab>
                                    <!--圖片設定-->
                                    <b-tab :title="$t('product.imageSetting')">
                                        <component :is="imageUploadComponent" :imageConfig="imageConfig" @getImageConfig="imageConfig = $event"></component>
                                        <component :is="imageBatchUploadComponent" :imageBatchConfig="imageBatchConfig" @getImageBatchConfig="imageBatchConfig = $event"></component>
                                    </b-tab>
                                    <!--商品選項-->
                                    <b-tab :title="$t('product.productOption')">
                                        <div class="text-left mb-3">
                                            <label class="badge bg-success">{{ $t('product.productOption') }}</label>
	                                        <div class="text-right">
		                                        <b-button class="btn btn-success" v-b-modal.createOption>
			                                        <i class="fa fa-plus-circle mr-2"></i>
			                                        <span>{{ $t('productOption.manageOption') }}</span>
		                                        </b-button>
	                                        </div>
                                        </div>
                                        <b-card no-body>
                                            <b-tabs pills card>
                                                <b-tab :title="getNameByLanguage(option.detail)" v-for="(option, sort) in product.productOption" :key="sort">
                                                    <!--<div class="mt-2 mb-3">-->
                                                        <!--<div class="col-12">-->
                                                            <!--<div class="custom-control custom-checkbox">-->
                                                                <!--<input v-model="option.required" type="checkbox" autocomplete="off" class="custom-control-input" true-value="Y" false-value="N" id="required">-->
                                                                <!--<label class="custom-control-label" for="required"> {{ $t('productOption.optionRequired') }}</label>-->
                                                            <!--</div>-->
                                                        <!--</div>-->
                                                    <!--</div>-->

                                                    <div class="table-responsive">
                                                        <table class="table table-hover table-striped table-bordered text-nowrap">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{ $t('productOption.optionValueName') }}</th>
                                                                    <th>{{ $t('productOption.quantity') }}</th>
                                                                    <th>{{ $t('productOption.isStock') }}</th>
                                                                    <th>{{ $t('productOption.optionPrice') }}</th>
                                                                    <th>{{ $t('productOption.returnPoint') }}</th>
                                                                    <th>{{ $t('productOption.weight') }}({{ $t('productOption.gram') }})</th>
                                                                    <th>{{ $t('productOption.operate') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr v-for="(value, key) in option.value" :key="key">
                                                                    <td width="25%" style="min-width: 200px;">
                                                                        <b-tabs content-class="" pills vertical>
                                                                            <b-tab :title="$t('language.' + valueDetail.language)" v-for="(valueDetail, index) in value.detail" :key="index">
                                                                                <input class="form-control" type="text" v-model="valueDetail.name" />
                                                                            </b-tab>
                                                                        </b-tabs>
                                                                    </td>
                                                                    <td width="10%">
                                                                        <input class="form-control" type="number" v-model="value.quantity"/>
                                                                    </td>
                                                                    <td width="10%">
                                                                        <b-form-select v-model="value.isStock">
                                                                            <option v-for="(item, index) in $t('enable')" :value="index" :key="index">
                                                                                {{ item }}
                                                                            </option>
                                                                            <template #first>
                                                                                <b-form-select-option :value="null">-- {{ $t('common.choose') }} --
                                                                                </b-form-select-option>
                                                                            </template>
                                                                        </b-form-select>
                                                                    </td>
                                                                    <td width="10%">
                                                                        <input class="form-control" type="number" v-model="value.price" />
                                                                    </td>
                                                                    <td width="10%">
                                                                        <input class="form-control" type="number" v-model="value.point"/>
                                                                    </td>
                                                                    <td width="10%">
                                                                        <input class="form-control" type="number" v-model="value.weight"/>
                                                                    </td>
                                                                    <td width="10%" class="text-center">
                                                                        <b-button class="btn btn-danger" @click="removeOption('optionValue', key, option.value )"><i class="fa fa-trash-alt"></i></b-button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tfoot>
	                                                            <tr>
	                                                                <td colspan="6"></td>
	                                                                <td>
	                                                                    <b-button class="btn btn-success" @click="addOption('optionValue' , option.value)"><i class="fa fa-plus-circle"></i></b-button>
	                                                                </td>
	                                                            </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </b-tab>
                                            </b-tabs>
                                        </b-card>

                                    </b-tab>
                                    <!--商品規格-->
                                    <b-tab :title="$t('product.productSpecification')">
                                    </b-tab>
                                </b-tabs>
                            </div>
                            <div class="card-footer">
                                <div class="text-right">
                                    <router-link :to="{ name: 'productList'}" class="btn bg-danger mr-3">
                                        <i class="fas fa-reply"></i> {{ $t('common.back') }}
                                    </router-link>
                                    <b-button type="button" class="btn btn-success" @click="save()" v-if="controlPermission === 'E'">
                                        <i class="fas fa-pen-nib"></i>  {{ $t('common.' + method) }}
                                    </b-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </b-overlay>
            <!-- 新增選項區 -->
            <b-modal id="createOption" ref="createOption" :cancel-title="$t('common.cancel')" :ok-title-html="$t('common.confirm')" :title="$t('productOption.manageOption')" :hide-footer="controlPermission !== 'E'">
                <div class="d-block text-center">
                    <form class="form-horizontal">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>{{ $t('productOption.optionName') }}</th>
                                        <th>{{ $t('productOption.operate') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(option, index) in product.productOption" :key="index">
                                        <td width="80%" style="min-width: 200px;">
                                            <b-tabs content-class="" pills vertical>
	                                            <b-tab :title="$t('language.' + data.language)" v-for="(data, index) in option.detail" :key="index">
		                                            <input class="form-control" type="text" v-model="data.name" />
	                                            </b-tab>
                                            </b-tabs>
                                        </td>

                                        <td class="text-center">
                                            <b-button class="btn btn-danger" @click="removeOption('option' , index)"><i class="fa fa-trash-alt"></i></b-button>
                                        </td>
                                    </tr>
                                </tbody>
	                            <tfoot>
		                            <tr>
			                            <td colspan="1"></td>
			                            <td>
				                            <b-button class="btn btn-success" @click="addOption('option')"><i class="fa fa-plus-circle"></i></b-button>
			                            </td>
		                            </tr>
	                            </tfoot>
                            </table>
                        </div>
                    </form>
                </div>
            </b-modal>
        </div>
    </section>
</template>

<script>

    export default {
        layout: 'admin',
        data() {
            return {
                //loading
                isLoading: false,
                //當前語系
                language: 'zh-tw',
                //商品資料
                product: {
                    status: 'N',
                    category: [],
                    model: '',
                    name: '',
                    costPrice: '',
                    price: '',
                    sortOrder: '',
                    detail: [],
                    productOption: [],
                    productSpecification: [],
                },
                //狀態
                status: false,
                //商品分類
                productCategory: [],
                //方法
                method: '',
                //class對應狀態
                classStatus: (status) => [
                    {'bg-success': status === 'Y'},
                    {'bg-danger': status === 'N'},
                ],
                //請求參數
                sentData:{
                    account:'',
                    name:'',
                    status:null,
                    productCategoryId:null,
                    email:''
                },
                //操作的權限
                controlPermission: '',
                //圖片component
	            imageUploadComponent: null,
	            //要傳送給component的資料
	            imageConfig: {
		            url: '',
	            },
	            //圖片批量上傳 component
	            imageBatchUploadComponent: null,
	            //要傳送給component的資料
	            imageBatchConfig: [],
            }
        },
        computed: {},
        watch: {
        	status : function (status) {
                this.product.status = status ? 'Y' : 'N'
	        },
        },
        mounted() {
        	//todo 因demo開放google登入 指開放讀得權限 到時關閉第三方後台登入再拿掉
            this.controlPermission = localStorage.getItem('permission') ?
	            JSON.parse(localStorage.getItem('permission')).product.productList : 'V'

            //方法
            this.method = !this.$route.params.id ? 'create' : 'edit'

            //語系
            this.language = localStorage.getItem('language') || 'zh-tw'

            //init
            this.init();
        },
        methods: {

            //=== API 接口 ==========//
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
                        this.getProductCategory()
                    ])
                } else {
                    return Promise.all([
                        this.setProductDetail(),
                        this.getProductCategory()
                    ])
                }
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
	                this.$root.notify(error)
                })
            },

            /**
             * edit 修改管理者
             *
             * @param productId
             * @since 0.0.1
             * @version 0.0.1
             */
            edit(productId) {
                this.isLoading = true
                //請求參數
                const request = {
                    method: 'get',
                    url: '/api/product/' + productId,
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.product = response.data
                    this.status = this.product.status === 'Y';

                    //圖片相關資料整理
                    this.imageConfig.url = response.data.picture
                    this.imageBatchConfig = response.data.relatedImage

                    //API抓取資料後再引入component
                    this.imageUploadComponent = () => import('@/components/media/imageUpload.vue')
                    this.imageBatchUploadComponent = () => import('@/components/media/imageBatchUpload.vue')


                    this.isLoading = false
                }).catch((error) => {
	                this.$root.notify(error)
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
            	this.isLoading = true;

            	//商品的對應圖片ID
	            this.product.uploadId = this.imageConfig.uploadId

                //商品的關聯圖片
	            this.product.relatedImage = this.imageBatchConfig

                //請求參數設定
                const request = {
                    method: (this.method === 'create') ? 'post' : 'put',
                    url: '/api/product/' + (this.method === 'create' ? 'store' : 'update/' + this.$route.params.id),
                    data: this.qs.stringify(this.product)
                }

                //請求
                this.$store.dispatch('authRequest', request).then((response) => {
                    this.$router.push({ name : 'productList'}).then(() => { this.$root.notify(response) })
                }).catch((error) => {
	                this.$root.notify(error);
	                this.isLoading = false;
                })
            },

            /**
             * setProductDetail 依照語系組成Detail
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            setProductDetail() {
                Object.keys(this.$t('language')).forEach((language) =>{
                    this.product.detail.push({'language' : language})
                })

                //API抓取資料後再引入component
                this.imageUploadComponent = () => import('@/components/media/imageUpload.vue')
                this.imageBatchUploadComponent = () => import('@/components/media/imageBatchUpload.vue')
            },

            //=== API 接口 ==========//

            //=== 內部功能區塊 ==========//
            /**
             * getNameByLanguage 依照語系取得名稱
             *
             * @since 0.0.1
             * @version 0.0.1
             * @params data
             */
            getNameByLanguage( data ) {
                return Object.values(data).filter((item) => {
                    return item.language === localStorage.getItem('language')
                })[0].name
            },

            /**
             * addOption 新增選項
             *
             * @since 0.0.1
             * @version 0.0.1
             * @params string type
             */
            addOption( type , data ) {
                switch (type) {
                    case 'option':
                        this.product.productOption.push({
                            multiple: "N",
                            productId: this.product.productId,
                            productOptionId: 0,
                            required: "Y",
                            sortOrder: 0,
                            detail:(Object.keys(this.$t('language')).map((language) => {
                                return {'language' : language}
                            })),
                            value: []
                        })
                        break;
                    case 'optionValue' :
                        data.push({
                            price: 0,
                            quantity: 0,
                            point: 0,
                            weight: 0,
                            isStock: 'N',
                            sortOrder: 0,
                            detail:(Object.keys(this.$t('language')).map((language) => {
                                return {'language' : language}
                            })),
                        })
                        break;
                    default:
                        break;
                }

            },

            /**
             * removeOption 移除選項
             *
             * @since 0.0.1
             * @version 0.0.1
             * @params string type
             * @params int index
             * @params array option
             */
            removeOption( type , index = 0 , option = [] ) {
                let askSetting = {
                    title: this.$t('message.askDelete'),
                    text: this.$t('message.askDeleteMessage'),
                    confirmText: this.$t('common.confirm'),
                    cancelText: this.$t('common.cancel'),
                }

                switch (type) {
                    case 'option':
                        //詢問刪除
                        if(this.product.productOption[index].productOptionId) {
                            this.$Swal.ask(askSetting).then((result) => {
                                if (result.isConfirmed) {
                                    this.isLoading = true
                                    let productOptionId = this.product.productOption[index].productOptionId

                                    //請求刪除
                                    const request = {
                                        method: 'delete',
                                        url: '/api/product/option/' + productOptionId,
                                    }

                                    //請求刪除前驗證
                                    this.$store.dispatch('authRequest', request).then((response) => {
                                        this.$root.notify(response);
                                        this.product.productOption.splice(index , 1)

                                        this.isLoading = false
                                    }).catch((error) => {
                                        this.$root.notify(error);
                                        this.isLoading = false
                                    })
                                }
                            })
                        } else {
                            this.product.productOption.splice(index , 1)
                        }

                        break;
                    case 'optionValue':
                        //詢問刪除
                        if(option[index].productOptionValueId) {
                            this.$Swal.ask(askSetting).then((result) => {
                                if (result.isConfirmed) {
                                    this.isLoading = true
                                    let productOptionValueId = option[index].productOptionValueId

                                    //請求刪除
                                    const request = {
                                        method: 'delete',
                                        url: '/api/product/option/value/' + productOptionValueId,
                                    }

                                    //請求刪除前驗證
                                    this.$store.dispatch('authRequest', request).then((response) => {
                                        this.$root.notify(response);
                                        option.splice(index , 1)

                                        this.isLoading = false
                                    }).catch((error) => {
                                        this.$root.notify(error);
                                        this.isLoading = false
                                    })
                                }
                            })
                        } else {
                            option.splice(index , 1)
                        }

                        break;
                    default:
                        break;
                }
            }

            //=== 內部功能區塊 ==========//
        }
    }
</script>

<style>
    .table thead > tr > td, .table tbody > tr > td {
        vertical-align: middle;
    }

    .table .tab-content {
        padding-top: 19px;
    }

    .required-block {
        display: inline-block;
    }
</style>

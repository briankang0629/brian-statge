<template>
    <section class="content" id="media-image">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <div class="float-sm-right" v-if="controlPermission === 'E' && selected.length">
                        <a class="btn bg-danger mr-2 mb-3" @click="remove()">
                            <i class="fas fa-trash"></i> {{ $t('common.delete') }}
                        </a>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card card-info">
                        <!-- 圖庫區 -->
                        <div class="card-header">
                            <span class="card-title">{{ $t('common.imageUpload') }}</span>
                        </div>
                        <b-overlay :show="isLoading" rounded="sm">
                            <div class="card-body">
                                <h5 class="badge bg-success">{{ $t('media.folder') }}</h5>
                                <b-tabs pills card>
                                    <b-tab :title="item.name" class="folder-tab" v-for="(item, key) in imageFolder"
                                           :key="key" @click="folder = item.code">
                                        <div class="filter-item col-xs-6 col-sm-6 col-md-2 col-lg-1"
                                             v-for="(data, index) in imageList[item.code]"
                                             :key="index"
                                        >
                                            <div class="thumbnail">
                                                <a @click="getImageInfo(data)">
                                                    <b-img left :src="data.url"
                                                           class="img-fluid mb-2 img-thumbnail card-img"
                                                    >
                                                    </b-img>
                                                </a>

                                                <label class="mb-0 select-img" v-if="controlPermission === 'E'">
                                                    <input type="checkbox" v-model="selected"
                                                           :value="data.uploadId"/>
                                                </label>
                                            </div>
                                        </div>
                                        <div v-if="!imageList[item.code]" class="text-center">
                                        <span class="text-danger">
                                            {{ $t('common.notFound') }}
                                        </span>
                                        </div>
                                    </b-tab>
                                </b-tabs>
                            </div>
                        </b-overlay>
                        <hr/>

                        <!-- 上傳圖片區 -->
                        <div class="card-body" style="padding: 5px;" v-if="controlPermission === 'E'">
                            <VueFileAgent
                                    ref="uploadImage"
                                    :multiple="true"
                                    :deletable="true"
                                    :meta="true"
                                    :accept="'image/*'"
                                    :maxSize="'2MB'"
                                    :maxFiles="5"
                                    :helpText="$t('common.imageUpload')"
                                    :errorText="{
                                                  type: $t('media.imageTypeError'),
                                                  size: $t('media.imageSizeExceed')
                                                }"
                                    @select="sumTotal($event)"
                                    @beforedelete="deleted($event)"
                                    v-model="imageRecords"
                            ></VueFileAgent>
                            <!-- 上傳按鈕 -->
                            <div class="mt-3 mb-3 upload-button-group">
                                <button class="btn btn-info ml-2"
                                        :disabled="isLoading"
                                        v-b-modal.createFolder>
                                    {{ $t('media.createFolder') }}
                                </button>
                                <button class="btn btn-primary ml-2"
                                        :disabled="(!imageRecordsForUpload.length) || (disabled)"
                                        @click="uploadImage()">
                                    {{ $t('common.upload') }} {{ imageRecordsForUpload.length }} {{ $t('common.countImages') }}
                                </button>
                            </div>
                        </div>
                        <div class="card-footer" style="min-height: 50px">
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- 新增資料夾區 -->
        <b-modal id="createFolder" ref="createFolder" cancel-title="取消" ok-title-html="確定" :title="$t('media.createFolder')" :hide-footer="controlPermission !== 'E'" @ok.prevent="createFolder()">
            <div class="d-block text-center">
                <form class="form-horizontal">
                    <div class="card-body">
                        <div class="form-group row text-left">
                            <label for="code" class="col-sm-3 col-form-label">{{ $t('media.code') }} : </label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="code" v-model="sentData.code"
                                       :placeholder="$t('media.code')">
                            </div>
                        </div>
                        <div class="form-group row text-left">
                            <label for="name" class="col-sm-3 col-form-label">{{ $t('media.name') }} : </label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="name" v-model="sentData.name"
                                       :placeholder="$t('media.name')">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </b-modal>

        <!-- 圖片詳情 -->
        <b-modal id="imageInfo" ref="imageInfo" cancel-title="取消" ok-title-html="確定" :title="imageInfo.originName" :hide-footer="true">
            <div class="d-block text-center">
                <form class="form-horizontal">
                    <div class="card-body">
                        <div class="form-group row text-left">
                            <label for="name" class="col-sm-3">{{ $t('media.image') }} : </label>
                            <div class="col-sm-9">
                                <b-img :src="imageInfo.url" class="img-fluid mb-2 card-img"></b-img>
                            </div>
                        </div>
                        <div class="form-group row text-left">
                            <label for="name" class="col-sm-3">{{ $t('media.originName') }} : </label>
                            <div class="col-sm-9">
                                {{imageInfo.originName}}
                            </div>
                        </div>
                        <div class="form-group row text-left">
                            <label for="name" class="col-sm-3">{{ $t('media.height') }} : </label>
                            <div class="col-sm-9">
                                {{imageInfo.height}}
                            </div>
                        </div>
                        <div class="form-group row text-left">
                            <label for="name" class="col-sm-3">{{ $t('media.width') }} : </label>
                            <div class="col-sm-9">
                                {{imageInfo.width}}
                            </div>
                        </div>
                        <div class="form-group row text-left">
                            <label for="name" class="col-sm-3">{{ $t('media.size') }} : </label>
                            <div class="col-sm-9">
                                {{imageInfo.size}}
                            </div>
                        </div>
                        <div class="form-group row text-left">
                            <label for="name" class="col-sm-3">{{ $t('media.extension') }} : </label>
                            <div class="col-sm-9">
                                {{imageInfo.extension}}
                            </div>
                        </div>
                        <div class="form-group row text-left">
                            <label for="name" class="col-sm-3">{{ $t('media.createTime') }} : </label>
                            <div class="col-sm-9">
                                {{imageInfo.createTime}}
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </b-modal>
    </section>
</template>

<script>
    export default {
        layout: 'admin',
        data() {
            return {
                //按鈕可否點選
                disabled: false,
                //isLoading
                isLoading: false,
                //選擇的圖片
                selected: [],
                //單張圖片詳細資訊
                imageInfo:{
                    url: '',
                    originName: '',
                    height: '',
                    width: '',
                    size: '',
                    extension: '',
                    createTime: ''
                },
                //圖片列表
                imageList: {},
                //圖片資料夾設定
                imageFolder: {},
                //上傳圖片model
                imageRecords: [],
                //上傳圖片總數
                imageRecordsForUpload: [],
                //上傳圖片API的Url
                uploadUrl: '',
                //要創建的資料夾
                folder: '',
	            //操作的權限
	            controlPermission: '',
                //Header
                uploadHeaders: {
                    'Authorization': JSON.parse(localStorage.getItem('auth')).token,
                },
                //sentData
                sentData: {
                    code: '',
                    name: ''
                },

            };
        },
        mounted() {
            //判定當前環境決定api 要請求的位置
            this.uploadUrl = (process.env.NODE_ENV === 'production') ?
                ((process.env.VUE_APP_API_DEV) ? process.env.VUE_APP_API_DEV + 'api/media/image/store' : 'https://admin.kang-web.tk/backend/api/media/image/store') :
                '/api/media/image/store'

	        //權限 todo 因demo開放google登入 指開放讀得權限 到時關閉第三方後台登入再拿掉
	        this.controlPermission = localStorage.getItem('permission') ?
		        JSON.parse(localStorage.getItem('permission')).media.image : 'V'

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
                    this.getImage(),
                    this.getSetting(),
                ])
            },

            /**
             * getImage 取圖片列表
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getImage: function () {
                this.isLoading = true
                const request = {
                    method: 'get',
                    url: '/api/media/image',
                    params: {
                        type: 'image'
                    }
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.imageList = response.data
                    this.isLoading = false
                }).catch((error) => {
	                this.$root.notify(error)
                })
            },

            /**
             * getSetting 取圖片資料夾設定
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            getSetting: function () {
                this.isLoading = true
                const request = {
                    method: 'get',
                    url: '/api/media/image/setting',
                }

                this.$store.dispatch('authRequest', request).then((response) => {
                    this.imageFolder = response.data

                    //預設第一個資料夾
                    this.folder = response.data[0].code
                    this.isLoading = false
                }).catch((error) => {
	                this.$root.notify(error)
                })
            },

            /**
             * uploadImage 上傳圖片
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            uploadImage: function () {
                //暫時禁用按鈕
                this.disabled = true
                this.isLoading = true

                //套件內的fn 無法吃到內部folder 改為header 傳送
                this.uploadHeaders.folder = this.folder

                //開始上傳
                this.$refs.uploadImage.upload(this.uploadUrl, this.uploadHeaders, this.imageRecordsForUpload).then((response) => {
	                this.$root.notify(response)

                    //清除圖片
                    this.imageRecords = []
                    this.imageRecordsForUpload = [];

                    //開放按鈕
                    this.disabled = false
                    this.isLoading = false

                    this.getImage()
                }).catch((error) => {
	                this.$root.notify(error)

                    //清除圖片
                    this.imageRecords = []
                    this.imageRecordsForUpload = [];

                    //開放按鈕
                     this.disabled = false
                     this.isLoading = false
                })
            },

            /**
             * sumTotal 將帶上傳區的圖片做加總
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            sumTotal: function (imageRecordsNewlySelected) {
                let validFileRecords = imageRecordsNewlySelected.filter((imageRecord) => !imageRecord.error);
                this.imageRecordsForUpload = this.imageRecordsForUpload.concat(validFileRecords);
            },

            /**
             * deleted 刪除圖片
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            deleted: function (imageRecord) {
                this.$refs.uploadImage.deleteFileRecord(imageRecord)
            },

            /**
             * createFolder 創建資料夾
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            createFolder: function () {
                //請求參數
                const request = {
                    method: 'post',
                    url: '/api/media/image/create/folder',
                    data: this.qs.stringify(this.sentData)
                }

                this.$store.dispatch('authRequest', request).then((response) => {
	                this.$root.notify(response)
                    this.$refs['createFolder'].hide()
                    this.getImage()
                }).catch((error) => {
	                this.$root.notify(error)

                    this.isLoading = false
                })
            },

            /**
             * getImageInfo 取圖片詳細資訊
             *
             * @since 0.0.1
             * @version 0.0.1
             * @param array data
             */
            getImageInfo: function ( data ) {
                this.imageInfo = {
                    url: data.url,
                    originName: data.originName,
                    height: data.height,
                    width: data.width,
                    size: data.size,
                    extension: data.extension,
                    createTime: data.createTime,
                }
                this.$refs['imageInfo'].show()
            },

            /**
             * remove 刪除
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            remove: function () {
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
                        let imageId = this.selected.join(',')

                        //請求參數
                        const request = {
                            method: 'delete',
                            url: '/api/media/image/' + imageId,
                        }

                        this.$store.dispatch('authRequest', request).then((response) => {
                            this.$notify({
                                type:'success',
                                title: this.$t('message.success'),
                                text: response.msg,
                            })

                            this.isLoading = false
                            this.selected = []
                            this.getImage()
                        }).catch((error) => {
                            this.$notify({
                                type:'error',
                                title: this.$t('message.error'),
                                text: error.msg,
                            })

                            this.isLoading = false
                        })
                    }
                });
            }
        },
    }
</script>
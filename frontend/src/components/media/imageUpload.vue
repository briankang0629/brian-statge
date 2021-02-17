<template>
    <b-overlay :show="isLoading" rounded="sm">
        <div class="tab-pane  d-block text-center">
            <div class="text-left mb-3">
                <label class="badge bg-success" >{{ $t('media.representImage') }}</label>
            </div>
            <div class="text-left">
                <b-card no-body>
                    <b-tabs pills card>
                        <!-- 圖庫選圖 -->
                        <b-tab :title="$t('media.selectImageFromFolder')" active class="page-inside">
                            <div class="profile-pic bg-light pt-3">
                                <div class="default-image pl-3">
                                    <img :src="notFound" v-if="!imageInfo.url">
                                    <img :src="imageInfo.url" v-else>
                                    <span class="btn btn-link btn-sm btn-block mb-4" v-b-modal.gallery>{{ $t('media.pleaseSelectImage') }}</span>
                                </div>
                                <div class="default-image select-image ml-3">
                                    <p>
                                        {{ $t('media.selectImageFromFolder') }}
                                        <span class="text-sm text-danger">({{ $t('media.selectImageFromFolderNote') }})</span>
                                    </p>
                                    <button class="btn btn-primary" v-b-modal.gallery>
                                        {{ $t('media.gallery') }}
                                    </button>
                                </div>
                            </div>
                        </b-tab>

                        <!--上傳新圖片-->
                        <b-tab :title="$t('media.uploadNewImage')">
                            <div class="profile-pic bg-light pt-3">
                                <VueFileAgent
                                        class="profile-pic-upload-block"
                                        ref="uploadImage"
                                        :multiple="false"
                                        :deletable="false"
                                        :meta="false"
                                        :compact="true"
                                        :accept="'image/*'"
                                        :maxSize="'2MB'"
                                        :maxFiles="1"
                                        :helpText="$t('common.imageUpload')"
                                        :errorText="{
                                                    type: $t('media.imageTypeError'),
                                                    size: $t('media.imageSizeExceed')
                                                    }"
                                        v-model="imageRecords"
                                        @select="sumTotal($event)"
                                        @beforedelete="deleted($event)"
                                >
                                    <template v-slot:after-inner>
                                        <span title="after-inner" class="btn btn-link btn-sm btn-block">{{ $t('media.pleaseSelectImage') }}</span>
                                    </template>
                                    <template v-slot:after-outer class="aad">
                                        <div title="after-outer">
                                            <p>
                                                {{ $t('media.pleaseUploadImage') }}
                                                <span class="text-sm text-danger">({{ $t('media.pleaseUploadImageNote') }})</span>
                                            </p>
                                            <button class="btn btn-primary"
                                                    :disabled="(!imageRecordsForUpload.length) || (disabled)"
                                                    @click="uploadImage()">
                                                {{ $t('common.upload') }}
                                            </button>
                                            <button type="button" class="ml-2 btn btn-danger"
                                                    v-if="imageRecordsForUpload.length"
                                                    @click="removeImage()">{{ $t('common.remove') }}
                                            </button>
                                            <div class="clearfix"></div>
                                        </div>
                                    </template>
                                </VueFileAgent>
                            </div>
                        </b-tab>
                    </b-tabs>
                </b-card>
            </div>

            <!-- 圖庫區 -->
            <b-modal id="gallery" ref="gallery" cancel-title="取消" ok-title-html="確定" :title="$t('media.selectImageFromFolder')" :hide-footer="true" @ok.prevent="selectProfile()">
                <div class="d-block text-center">
                    <form class="form-horizontal">
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
                                    </div>
                                </div>
                                <div v-if="!imageList[item.code]" class="text-center">
                                    <span class="text-danger">
                                        {{ $t('common.notFound') }}
                                    </span>
                                </div>
                            </b-tab>
                        </b-tabs>
                    </form>
                </div>
            </b-modal>
        </div>
    </b-overlay>
</template>

<script>
    //notFound-image
    import notFound from '../../assets/notFound.png'

    export default {
    	props:['imageConfig'],
        data() {
            return {
                //按鈕可否點選
                disabled: false,
                //isLoading
                isLoading: false,
                //選擇的圖片
                selected: [],
                //無圖片預設圖片
                notFound: notFound,
                //單張圖片詳細資訊
                imageInfo: {
                    url: this.imageConfig.url,
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
                //上傳圖片model (本身圖片
                imageRecords: [],
                //上傳圖片的物件 (上傳用的
                imageRecordsForUpload: [],
                //上傳圖片API的Url
                uploadUrl: '',
                //操作的權限
                controlPermission: '',
                //Header
                uploadHeaders: {
                    'Authorization': JSON.parse(localStorage.getItem('auth')).token,
                },
            };
        },
        mounted() {
            //判定當前環境決定api 要請求的位置
            this.uploadUrl = (process.env.NODE_ENV === 'production') ?
                ((process.env.VUE_APP_API_DEV) ? process.env.VUE_APP_API_DEV + 'api/media/image/store' : 'https://admin.kang-web.tk/backend/api/media/image/store') :
                '/api/media/image/store'

            //父層的圖片資料存進component
	        this.imageInfo = this.imageConfig

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
                    console.log(error)
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
                    console.log(error)
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

                //開始上傳
                this.$refs.uploadImage.upload(this.uploadUrl, this.uploadHeaders, this.imageRecordsForUpload).then((response) => {
                    this.$notify({
                        type:'success',
                        title: this.$t('message.success'),
                        text: this.$t('message.uploadSuccess'),
                    })

                    //清除圖片
                    this.imageRecordsForUpload = [];

                    //開放按鈕
                    this.disabled = false
                    this.isLoading = false

	                //將子層的資料傳送給父層
	                this.imageInfo.uploadId = response[0].data.uploadId
	                this.$emit('getImageConfig', this.imageInfo);

                    this.getImage()
                }).catch((error) => {
                    this.$notify({
                        type:'error',
                        title: this.$t('message.error'),
                        text: this.$t('message.uploadFail'),
                    })

                    //清除圖片
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
             * getImageInfo 取圖片詳細資訊
             *
             * @since 0.0.1
             * @version 0.0.1
             * @param data
             */
            getImageInfo: function (data) {
                this.imageInfo = {
                    uploadId: data.uploadId,
                    url: data.url,
                    originName: data.originName,
                    height: data.height,
                    width: data.width,
                    size: data.size,
                    extension: data.extension,
                    createTime: data.createTime,
                }

                //將子層的資料傳送給父層
                this.$emit('getImageConfig', this.imageInfo);
                this.$refs['gallery'].hide()
            },

            /**
             * remove 刪除
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            remove: function () {
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
            },

	        /**
	         * removeImage 移除當前圖片
	         *
	         * @since 0.0.1
	         * @version 0.0.1
	         */
	        removeImage: function () {
                this.imageRecords = null;
		        this.imageRecordsForUpload = [];
            },
        },
    }
</script>
<style lang="scss">
    ol li {
        font-size: 14px;
    }

    .profile-pic {
        .profile-pic-upload-block {
            border: 2px dashed transparent;
            padding: 20px;
            padding-top: 0;
        }
    }

    .profile-pic {
        .vue-file-agent {
            width: 180px;
            float: left;
            margin: 0 15px 5px 0;
            border: 0;
            box-shadow: none;
        }
    }

    /**
     * 圖庫設定
     */
    .default-image {
        display: inline-block;
    }

    .default-image {
        img {
            width: 180px;
        }
    }

    @media (min-width: 574px) {
        .default-image.select-image {
            position: absolute;
        }
    }

    /** 圖庫CSS 設定 */
    /** 圖片排列方式 */
    .filter-item {
        display: inline-block;
        max-width: 125px;
    }

    /** 選擇圖片按鈕 */
    .select-img {
        position: absolute;
        top: 0;
        left: 5px;
    }

    .select-img input[type=checkbox] {
        height: 20px;
        width: 20px;
    }

    /** 圖片縮圖 */
    .img-thumbnail {
        padding: .25rem;
        background-color: #ebedef;
        border: 1px solid #c4c9d0;
        border-radius: .25rem;
        height: 100px;
        -o-object-fit: cover;
        object-fit: cover;
    }

    .img-thumbnail:hover {
        cursor: pointer;
    }

    .folder-tab {
        box-shadow: none;
    }

    #tab-image {
        width: 100%;
    }

</style>

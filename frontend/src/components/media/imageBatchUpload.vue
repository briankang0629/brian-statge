<template>
	<b-overlay :show="isLoading" rounded="sm">

        <!--批量上傳圖片-->
        <div class="tab-pane  d-block text-center" id="component-imageBatchUpload">
            <div class="text-left mb-3">
                <label class="badge bg-success">{{ $t('media.additionImage') }}</label>
            </div>
            <div class="text-left">
                <div class="tab-pane active" id="tab-image">
                    <div class="table-responsive">
                        <table id="images" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <td class="text-left">{{ $t('media.additionImage') }}</td>
                                    <td class="text-left">{{ $t('product.sortOrder')}}</td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(data, index) in imageBatchInfo" :key="index">
                                    <td class="text-left">
                                        <div class="thumbnail">
                                            <b-img left
                                                   :src="data.picture"
                                                   alt=""
                                                   title=""
                                                   class="img-fluid mb-2 img-thumbnail card-img"
                                            >
                                            </b-img>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <input type="text" v-model="data.sortOrder" :placeholder="$t('product.sortOrder')" class="form-control">
                                    </td>
                                    <td class="text-left">
                                        <b-button type="button" class="btn btn-danger" @click="remove(data.uploadId)">
                                            <i class="fa fa-trash"></i>
                                        </b-button>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot>
                            <tr>
                                <td colspan="3">
                                    <div class="card-body" style="padding: 5px;">
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
                                                    v-b-modal.galleryBatch>
                                                {{ $t('media.selectImageFromFolder') }}
                                            </button>
                                            <button class="btn btn-primary ml-2"
                                                    :disabled="(!imageRecordsForUpload.length) || (disabled)"
                                                    @click="uploadImage()">
                                                {{ $t('common.upload') }} {{ imageRecordsForUpload.length }} {{ $t('common.countImages') }}
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 圖庫區 -->
            <b-modal id="galleryBatch" ref="galleryBatch" cancel-title="取消" ok-title-html="確定" :title="$t('media.selectImageFromFolder')" @ok="selectBatchImage()">
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
                                        <a>
                                            <b-img left :src="data.url"
                                                   class="img-fluid mb-2 img-thumbnail card-img"
                                            >
                                            </b-img>
                                        </a>

                                        <label class="mb-0 select-img">
                                            <input type="checkbox" v-model="selected"
                                                   :value="data"/>
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
		props: ['imageBatchConfig'],
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
				//多張圖片詳細資訊
				imageBatchInfo: [],
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
			this.imageBatchInfo = this.imageBatchConfig

            //init
            this.init()
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

					//將上傳後的圖片陣列
					response.forEach((media) => {
						this.imageBatchInfo.push({'picture': media.data.picture, 'uploadId': media.data.uploadId, 'sortOrder': 0})
					})

					//將子層的資料傳送給父層
					this.$emit('getImageBatchConfig', this.imageBatchInfo);

					//清除圖片
					this.imageRecords = []
					this.imageRecordsForUpload = [];

					//開放按鈕
					this.disabled = false
					this.isLoading = false

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
             * selectBatchImage 選擇多張圖片
             *
             * @since 0.0.1
             * @version 0.0.1
             */
            selectBatchImage: function() {
                this.selected.forEach((data) => {
                    this.imageBatchInfo.push({'picture': data.url, 'uploadId': data.uploadId, 'sortOrder': 0})
                })

                this.$refs['galleryBatch'].hide()
                this.selected = []
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
                    this.$notify({
                        type:'error',
                        title: this.$t('message.error'),
                        text: error.errorCode + error.msg,
                    })
                })
            },

            /**
             * remove 移除指定圖片
             *
             * @since 0.0.1
             * @version 0.0.1
             */
			remove(uploadId) {
				this.imageBatchInfo.forEach((data, key) => {
					if (data.uploadId === uploadId) {
						this.imageBatchInfo.splice(key , 1)
					}
				})
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
	#component-imageBatchUpload {
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

		/** 圖片縮圖 */
		.img-thumbnail {
			padding: .25rem;
			background-color: #EBEDEF;
			border: 1px solid #C4C9D0;
			border-radius: .25rem;
			max-height: 100px;
			max-width: 100px;
			-o-object-fit: cover;
			object-fit: cover;
		}
		
		@media (max-width: 414px) {
			.img-thumbnail {
				height: 100%;
			}
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

        /** 上傳前預覽圖 */
        .file-preview.image-preview {
            z-index: 10!important;
        }
	}
</style>

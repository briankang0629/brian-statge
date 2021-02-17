<?php
/**
 * ProductController 商品控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/12/17
 */

use Libraries\PublicFunction as publicFunction;
use Libraries\Validator as validator;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】商品控制器
 */
class ProductController extends Controller {

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

	/**
	 * lists 商品清單
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	public function lists() {
		//驗證權限
		$this->permission('product/productList' , 'V' , 'A');

		//宣告
        $data = [];
		$mediaModel = new MediaModel();
		$productModel = new ProductModel();

		//商品列表
		$productLists = $productModel->lists(request::$get);

		//處理商品圖片
		foreach($productLists as $product) {
			//有上傳圖片
			$product['picture'] = $mediaModel->getMedia($product['uploadId']);

            //取商品所屬分類
            foreach ($productModel->getProductCategoryByProductId($product['productId']) as $item) {
                $product['category'][] = (int)$item['productCategoryId'];
            }

			//商品列表
			$data[] = $product;
		}

		//取商品列表
		publicFunction::json([
			'data' => $data ,
			'pagination' => $productModel->getPagination()
		] , 'success');
	}

	/**
	 * info 商品資訊
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $id
	 * @return mixed
	 */
	public function info( $id ) {
		//驗證權限
		$this->permission('product/productList' , 'V' , 'A');

		//宣告
		$productModel = new ProductModel();
		$productOptionModel = new ProductOptionModel();
        $mediaModel = new MediaModel();

		//商品資料
		if(!$product = $productModel->info($id)) {
			return publicFunction::emptyOutput();
		}

		//取商品詳細資料
		$product['detail'] = $productModel->getProductDetail($id);

        //取商品所屬分類
        foreach ($productModel->getProductCategoryByProductId($id) as $item) {
            $product['category'][] = (int)$item['productCategoryId'];
        }

		//取商品圖片
		$product['picture'] = $mediaModel->getMedia($product['uploadId']);

        //取商品附加圖片
		$product['relatedImage'] = $mediaModel->getMediaRelated($id, 'product');

        //取商品選項
        if($product['productOption'] = $productOptionModel->lists(['productId' => $id])) {
            foreach ($product['productOption'] as $key => $option) {
                //商品選項語系
                $option['detail'] = $productOptionModel->getProductOptionDetail($option['productOptionId']);

                //商品選項值
                $option['value'] = $productOptionModel->getProductOptionValue($option['productOptionId']);

                //商品選項值語系
                foreach ($option['value'] as $index => $value) {
                    $value['detail'] = $productOptionModel->getProductOptionValueDetail($value['productOptionValueId']);
                    $option['value'][$index] = $value;
                }

                //存成新的陣列
                $product['productOption'][$key] = $option;
            }
        }

		//取商品列表
		publicFunction::json([
			'data' => $product
		] , 'success');
	}

	/**
	 * store 新增商品
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return mixed
	 * @throws \Exception
	 */
	public function store() {
		//驗證權限
		$this->permission('product/productList' , 'E' , 'A');

		//宣告
		$logRecordData = [];
		$productModel = new ProductModel();
		$mediaModel = new MediaModel();

		//內部驗證
		$this->validatorProduct(request::$post);

        //事務操作存資料
		try {
			//宣告事務語法開始
			$productModel->db->beginTransaction();

			//商品資料
			$product = publicFunction::fillArray(request::$post, [
				'model', 'costPrice', 'price', 'sortOrder', 'status', 'uploadId'
			]);
			$product['createTime'] = date('Y-m-d H:i:s');

			//寫入商品資訊
			if(!$productModel->store($product)) {
				throw new Exception(language::getFile()['common']['create']['failed'], '0401');
			}

			//商品資料存進log
			$logRecordData['product'] = $product;

			//取得新增商品ID
			$productId = $productModel->db->getLastId();

			//儲存商品資訊
			foreach(request::$post['detail'] as $detail) {
			    //html_entity_decode
                $detail['description'] = html_entity_decode($detail['description']);

				$productDetail = publicFunction::fillArray($detail , [
					'name' , 'description' , 'metaTitle' , 'metaKeyword' , 'metaDescription' , 'tag'
				]);
				$productDetail['productId'] = $productId;
				$productDetail['language'] = $detail['language'];

				//寫入商品詳細資訊
				if(!$productModel->storeProductDetail($productDetail)) {
					throw new Exception(language::getFile()['common']['create']['failed'], '0402');
				}

                //寫入商品分類
                if(!$productModel->updateProductToCategory($productId, request::$post['category'])) {
                    throw new Exception(language::getFile()['common']['update']['failed'], '0407');
                }

				//附加圖片判斷
                $relatedImage = !empty(request::$post['relatedImage']) ? request::$post['relatedImage'] : [];

				//執行更新商品附加圖片
				if(!$mediaModel->updateMediaRelated($productId , 'product', $relatedImage)) {
					throw new Exception(language::getFile()['common']['update']['failed'], '0410');
				}

				//存進log
				$logRecordData['productDetail'][] = $productDetail;
			}

            //儲存商品選項
            if(isset(request::$post['productOption'])) {
                $this->updateProductOption(request::$post['productOption'] , $productId);
            }

			//操作記錄 @todo
			$this->writeLog(17 , [], $productModel->db->getSql());

			//Commit
			$productModel->db->commit();
		} catch(Exception $exception) {
			//Rollback
			$productModel->db->rollback();

			//回傳錯誤訊息
			publicFunction::error($exception->getCode(), $exception->getMessage());
		}

		//回傳
		publicFunction::json([
			'status' => 'success' ,
			'msg' => language::getFile()['common']['create']['success'] ,
		]);
	}

	/**
	 * update 修改商品
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $id 商品id
	 * @return mixed
	 * @throws \Exception
	 */
	public function update( $id ) {
		//驗證權限
		$this->permission('product/productList' , 'E' , 'A');

		//宣告
		$logRecordData = [];
		$productModel = new ProductModel();
		$mediaModel = new MediaModel();

		//內部驗證
		$this->validatorProduct(request::$put);

		//事務操作存資料
		try {
			//宣告事務語法開始
			$productModel->db->beginTransaction();

			//傳送參數
			$product = publicFunction::fillArray(request::$put, [
				'model', 'costPrice', 'price', 'sortOrder', 'status', 'uploadId'
			]);

			//執行更新
			if(!$productModel->update($id , $product)) {
				throw new Exception(language::getFile()['common']['update']['failed'], '0404');
			}

			//執行更新商品分類
			if(!$productModel->updateProductToCategory($id , request::$put['category'])) {
				throw new Exception(language::getFile()['common']['update']['failed'], '0406');
			}

			//附加圖片判斷
			$relatedImage = !empty(request::$put['relatedImage']) ? request::$put['relatedImage'] : [];

			//執行更新商品附加圖片
            if(!$mediaModel->updateMediaRelated($id , 'product', $relatedImage)) {
				throw new Exception(language::getFile()['common']['update']['failed'], '0408');
			}

			//商品資料存進log
			$logRecordData['product'] = $product;

			//更新商品詳細資料
			foreach(request::$put['detail'] as $detail) {
                //html_entity_decode
                $detail['description'] = html_entity_decode($detail['description']);

				$productDetail = publicFunction::fillArray($detail , [
					'name' , 'description' , 'metaTitle' , 'metaKeyword' , 'metaDescription' , 'tag'
				]);

				$where = [
					['productId' , '=' , $id] ,
					['language' , '=' , $detail['language']] ,
				];

				if(!$productModel->updateProductDetail($productDetail , $where)) {
					throw new Exception(language::getFile()['common']['update']['failed'], '0405');
				}

				//商品資料存進log
				$logRecordData['productDetail'][] = $productDetail;
			}

			//更新商品選項
            if(isset(request::$put['productOption'])) {
                $this->updateProductOption(request::$put['productOption'] , $id);
            }

			//操作記錄 @todo
			$this->writeLog(18 , [] , $productModel->db->getSql());

			//Commit
			$productModel->db->commit();
		} catch(Exception $exception) {
			//Rollback
			$productModel->db->rollback();

			//回傳錯誤訊息
			publicFunction::error($exception->getCode(), $exception->getMessage());
		}

		//回傳
		publicFunction::json([
			'status' => 'success' ,
			'msg' => language::getFile()['common']['update']['success'] ,
		]);
	}

	/**
	 * delete 刪除商品
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param $id
	 * @return string
	 */
	public function delete( $id ) {
		//驗證權限
		$this->permission('product/productList' , 'E' , 'A');

		//宣告
		$productModel = new ProductModel();
		$productOptionModel = new ProductOptionModel();
		$mediaModel = new MediaModel();

		//事務操作存資料
		try {
			//宣告事務語法開始
			$productModel->db->beginTransaction();

			//單筆或多筆刪除
			if(!$productModel->delete(['productId', 'IN', '( ' . $id . ' )'])) {
				throw new Exception(language::getFile()['common']['delete']['failed'], '0415');
			}

			//刪除關聯商品分類
			if(!$productModel->deleteProductToCategory(['productId', 'IN', '( ' . $id . ' )'])) {
				throw new Exception(language::getFile()['common']['delete']['failed'], '0416');
			}

			//刪除關聯商品的附加圖片
			if(!$mediaModel->deleteRelatedUpload(['productId', 'IN', '( ' . $id . ' )'])) {
				throw new Exception(language::getFile()['common']['delete']['failed'], '0417');
			}

			//判斷有無需要刪除關聯的商品選項
			if($productOptionArray = $productOptionModel->lists(['productId' => $id])) {

				//組成要刪除的productOptionId
				foreach($productOptionArray as $productOption) {
					$productOptionId[] = $productOption['productOptionId'];
				}
				$productOptionId = implode(',' , $productOptionId);

				//刪除關聯商品選項
				if(!$productOptionModel->delete(['productOptionId', 'IN', '( ' . $productOptionId . ' )'])) {
					throw new Exception(language::getFile()['common']['delete']['failed'], '0418');
				}
			}

			//刪除關聯商品規格

			//操作記錄
			$this->writeLog(6 , [] , []);

			//Commit
			$productModel->db->commit();
		} catch(Exception $exception) {
			//Rollback
			$productModel->db->rollback();

			//回傳錯誤訊息
			publicFunction::error($exception->getCode(), $exception->getMessage());
		}


		//回傳
        publicFunction::json([
			'status' => 'success' ,
			'msg' => language::getFile()['common']['delete']['success'] ,
		]);
	}
	//----------------------------------------------------------------
	//EndRegion API
	//----------------------------------------------------------------

    //----------------------------------------------------------------
    // 附屬函示 API Start
    //----------------------------------------------------------------

    /**
     * updateProductOption 更新商品選項
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param array $productOptionData
     * @param int $productId
     * @throws \Exception
     */
    private function updateProductOption( $productOptionData , $productId ) {
        //宣告
        $productOptionModel = new ProductOptionModel();

        //更新商品選項
        foreach ($productOptionData as $key => $option) {
            //傳送參數
            $productOption = publicFunction::fillArray($option, [
                'multiple', 'required', 'sortOrder'
            ]);
            $productOption['productId'] = $productId;

            //更新選項基本資料
            if(!empty($option['productOptionId']) ?
                !$productOptionModel->update($option['productOptionId'] , $productOption) :
                !$productOptionModel->store($productOption)
            ) {
	            throw new Exception(language::getFile()['common']['update']['failed'], '0411');
            }

            //商品選項ID
            $productOptionId = !empty($option['productOptionId']) ? $option['productOptionId'] : $productOptionModel->db->getLastId();

            //更新商品選項語系
            foreach ($option['detail'] as $index => $detail) {
                $productOptionDetail = publicFunction::fillArray($detail , [
                    'name' , 'language'
                ]);
                $productOptionDetail['productOptionId'] = $productOptionId;

                $where = [
                    ['productOptionId' , '=' , $productOptionId] ,
                    ['language' , '=' , $detail['language']] ,
                ];

                if(!$productOptionModel->updateProductOptionDetail($productOptionDetail , $where)) {
	                throw new Exception(language::getFile()['common']['update']['failed'], '0412');
                }
            }

            //更新選項值
            if(isset($option['value'])) {
                foreach ($option['value'] as $index => $value) {
                    //傳送參數
                    $productOptionValue = publicFunction::fillArray($value, [
                        'price', 'quantity', 'sortOrder' , 'point' , 'weight' , 'isStock'
                    ]);
                    $productOptionValue['productOptionId'] = $productOptionId;

                    //更新商品選項值
                    if(!empty($value['productOptionValueId']) ?
                        !$productOptionModel->updateProductOptionValue($value['productOptionValueId'] , $productOptionValue) :
                        !$productOptionModel->storeProductOptionValue($productOptionValue)
                    ) {
	                    throw new Exception(language::getFile()['common']['update']['failed'], '0413');
                    }

                    //商品選項值ID
                    $productOptionValueId = isset($value['productOptionValueId']) ? $value['productOptionValueId'] : $productOptionModel->db->getLastId();

                    //更新商品選項值語系
                    foreach ($value['detail'] as $valueDetail) {
                        $productOptionValueDetail = publicFunction::fillArray($valueDetail , [
                            'name' , 'language'
                        ]);
                        $productOptionValueDetail['productOptionValueId'] = $productOptionValueId;

                        $where = [
                            ['productOptionValueId' , '=' , $productOptionValueId] ,
                            ['language' , '=' , $valueDetail['language']] ,
                        ];

                        if(!$productOptionModel->updateProductOptionValueDetail($productOptionValueDetail , $where)) {
	                        throw new Exception(language::getFile()['common']['update']['failed'], '0414');
                        }
                    }
                }
            }
        }
    }

	/**
	 * validatorProduct 內部驗證
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $validator
	 * @throws \Exception
	 */
	private function validatorProduct( $validator ) {
		//規則
		$require = [
			'category' => 'required|array' ,
			'detail' => 'required|array' ,
			'model' => 'required|string|lenMax:64|lenMin:3' ,
			'costPrice' => 'required|number' ,
			'price' => 'required|number' ,
			'sortOrder' => 'required|int|lenMax:10' ,
			'status' => 'required|in:Y&N' ,
		];

		//驗證
		validator::make($validator , $require);

		//檢查商品詳細資料格式
		foreach($validator['detail'] as $detail) {
			//判斷傳送語系是否合法
			if(!in_array($detail['language'] , publicFunction::getSystemCode()['language'])) {
				publicFunction::error('0400');
			}

			//規則
			$require = [
				'name' => 'required|string|lenMax:64' ,
				'description' => 'required|string|lenMin:3' ,
				'metaTitle' => 'string|lenMax:64' ,
				'metaKeyword' => 'string|lenMax:64' ,
				'metaDescription' => 'string|lenMax:64' ,
				'tag' => 'string|lenMax:64' ,
			];

			//驗證
			validator::make($detail , $require);
		}

		//驗證商品選項填寫內容
		if(isset($validator['productOption'])) {
			foreach ($validator['productOption'] as $key => $option) {
				//驗證
				validator::make($option , [
					'productOptionId' => 'int' ,
					'multiple' => 'in:Y&N' ,
					'sortOrder' => 'int' ,
					'required' => 'in:Y&N' ,
					'detail' => 'array' ,
					'value' => 'array' ,
				]);

				//驗證語系
				foreach ($option['detail'] as $index => $detail) {
					validator::make($detail , [
						'name' => 'required|string' ,
						'language' => 'required|string' ,
					]);
				}

				//驗證選項值
				if(isset($option['value'])) {
					foreach ($option['value'] as $index => $value) {
						validator::make($value , [
							'productOptionValueId' => 'int' ,
							'price' => 'required|number' ,
							'quantity' => 'required|number' ,
							'point' => 'required|number' ,
							'weight' => 'required|number' ,
							'isStock' => 'in:Y&N' ,
							'detail' => 'array'
						]);

						foreach ($value['detail'] as $valueDetail) {
							validator::make($valueDetail , [
								'name' => 'required|string' ,
								'language' => 'required|string' ,
							]);
						}
					}
				}
			}
		}
	}
    //----------------------------------------------------------------
    // 附屬函示 API End
    //----------------------------------------------------------------
}
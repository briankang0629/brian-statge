<?php
/**
 * ProductSpecificationController 商品規格控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2021/01/21
 */

use Libraries\PublicFunction as publicFunction;
use Libraries\Validator as validator;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】商品規格控制器
 */
class ProductSpecificationController extends Controller {

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

	/**
	 * lists 商品規格清單
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	public function lists() {
		//驗證權限
		$this->permission(['A'] , 'product/productList' , 'V');

		//宣告
        $data = [];
		$mediaModel = new MediaModel();
		$productModel = new ProductModel();

		//商品規格列表
		$productLists = $productModel->lists(request::$get);

		//處理商品規格圖片
		foreach($productLists as $product) {
			//有上傳圖片
			$product['picture'] = $mediaModel->getMedia($product['uploadId']);

            //取商品規格所屬分類
            foreach ($productModel->getProductCategoryByProductId($product['productId']) as $item) {
                $product['category'][] = (int)$item['productCategoryId'];
            }

			//商品規格列表
			$data[] = $product;
		}

		//取商品規格列表
		return publicFunction::json([
			'data' => $data ,
			'pagination' => $productModel->getPagination()
		] , 'success');
	}

	/**
	 * info 商品規格資訊
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	public function info( $id ) {
		//驗證權限
		$this->permission(['A'] ,'product/productList' ,  'V');

		//宣告
		$productModel = new ProductModel();
        $mediaModel = new MediaModel();

		//商品規格資料
		if(!$product = $productModel->info($id)) {
			return publicFunction::emptyOutput();
		}

		//取商品規格詳細資料
		$product['detail'] = $productModel->getProductDetail($id);

        //取商品規格所屬分類
        foreach ($productModel->getProductCategoryByProductId($id) as $item) {
            $product['category'][] = (int)$item['productCategoryId'];
        }

		//取商品規格圖片
		$product['picture'] = $mediaModel->getMedia($product['uploadId']);

        //取商品規格附加圖片
		$product['relatedImage'] = $mediaModel->getMediaRelated($id, 'product');

		//取商品規格列表
		return publicFunction::json([
			'data' => $product
		] , 'success');
	}

	/**
	 * store 新增商品規格
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return boolean
	 */
	public function store() {
		//驗證權限
		$this->permission(['A'] , 'product/productList' , 'E');

		//宣告
		$logRecordData = [];
		$productDetail = [];
		$productModel = new ProductModel();
		$mediaModel = new MediaModel();

		//規則
		$require = [
			'category' => 'required|array' ,
			'detail' => 'required|array' ,
			'model' => 'required|string|lenMax:64|lenMin:3' ,
			'costPrice' => 'required|number' ,
			'price' => 'required|number' ,
			'sortOrder' => 'required|int|lenMax:10' ,
			'status' => 'required|in["Y" , "N"]' ,
		];

		//驗證
		validator::make(request::$post , $require);

		//檢查商品規格詳細資料格式
		foreach(request::$post['detail'] as $detail) {
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

		//事務操作存資料
		try {
			//宣告事務語法開始
			$productModel->db->beginTransaction();

			//商品規格資料
			$product = publicFunction::fillArray(request::$post, [
				'model', 'costPrice', 'price', 'sortOrder', 'status', 'uploadId'
			]);
			$product['createTime'] = date('Y-m-d H:i:s');

			//寫入商品規格資訊
			if(!$productModel->store($product)) {
				throw new Exception(language::getFile()['common']['create']['failed'], '0401');
			}

			//商品規格資料存進log
			$logRecordData['product'] = $product;

			//取得新增商品規格ID
			$productId = $productModel->db->getLastId();

			//儲存商品規格資訊
			foreach(request::$post['detail'] as $detail) {
			    //html_entity_decode
                $detail['description'] = html_entity_decode($detail['description']);

				$productDetail = publicFunction::fillArray($detail , [
					'name' , 'description' , 'metaTitle' , 'metaKeyword' , 'metaDescription' , 'tag'
				]);
				$productDetail['productId'] = $productId;
				$productDetail['language'] = $detail['language'];

				//寫入商品規格詳細資訊
				if(!$productModel->storeProductDetail($productDetail)) {
					throw new Exception(language::getFile()['common']['create']['failed'], '0402');
				}

                //寫入商品規格分類
                if(!$productModel->updateProductToCategory($productId, request::$post['category'])) {
                    throw new Exception(language::getFile()['common']['update']['failed'], '0407');
                }

				//執行更新商品規格附加圖片
				if(!$mediaModel->updateMediaRelated($productId , 'product', request::$post['relatedImage'])) {
					throw new Exception(language::getFile()['common']['update']['failed'], '0409');
				}

				//存進log
				$logRecordData['productDetail'][] = $productDetail;
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
	 * update 修改商品規格
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $productID 商品規格id
	 * @return string|array
	 */
	public function update( $id ) {
		//驗證權限
		$this->permission(['A'] , 'product/productList' , 'E');

		//宣告
		$logRecordData = [];
		$productModel = new ProductModel();
		$mediaModel = new MediaModel();

		//規則
		$require = [
			'category' => 'array' ,
			'detail' => 'array' ,
			'model' => 'string|lenMax:64|lenMin:3' ,
			'costPrice' => 'number' ,
			'price' => 'number' ,
			'sortOrder' => 'int|lenMax:10' ,
			'status' => 'in["Y" , "N"]' ,
		];

		//驗證
		validator::make(request::$put , $require);

		//檢查商品規格詳細資料格式
		foreach(request::$put['detail'] as $detail) {
			//判斷傳送語系是否合法
			if(!in_array($detail['language'] , publicFunction::getSystemCode()['language'])) {
				publicFunction::error('0403');
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

			//執行更新商品規格分類
			if(!$productModel->updateProductToCategory($id , request::$put['category'])) {
				throw new Exception(language::getFile()['common']['update']['failed'], '0406');
			}

			//執行更新商品規格附加圖片
			if(!$mediaModel->updateMediaRelated($id , 'product', request::$put['relatedImage'])) {
				throw new Exception(language::getFile()['common']['update']['failed'], '0408');
			}

			//商品規格資料存進log
			$logRecordData['product'] = $product;

			//更新商品規格詳細資料
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

				//商品規格資料存進log
				$logRecordData['productDetail'][] = $productDetail;
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
	 * delete 刪除商品規格
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param $id
	 * @return string
	 */
	public function delete( $id ) {
		//驗證權限
		$this->permission(['A'] , 'product/productList' , 'E');

		//宣告
		$productModel = new ProductModel();

		//單筆或多筆刪除
		$productModel->delete(['productId', 'IN', '( ' . $id . ' )']);

		//操作記錄
		$this->writeLog(6 , [] , $productModel->db->getSql());

		//回傳
		return publicFunction::json([
			'status' => 'success' ,
			'msg' => language::getFile()['common']['delete']['success'] ,
		]);
	}
	//----------------------------------------------------------------
	//EndRegion API
	//----------------------------------------------------------------
}

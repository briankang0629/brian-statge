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
        $language = isset(request::$get['language']) ? request::$get['language'] : publicFunction::getSystemCode()['defaultLanguage'];

		//商品列表
		$productLists = $productModel->lists(request::$get);

		//處理商品圖片
		foreach($productLists as $product) {
			//有上傳圖片
			$product['picture'] = $mediaModel->getMedia($product['uploadId']);

			//取商品的所屬分類
			$product['productCategory'] = $productModel->item('productCategoryDetail' , 'name' , [
			    ['productCategoryId', '=' , $product['productCategoryId']],
			    ['language', '=' , $language],
            ])['name'];

			//商品列表
			$data[] = $product;
		}

		//取商品列表
		return publicFunction::json([
			'data' => $data ,
			'pagination' => $productModel->getPagination()
		] , 'success');
	}

	/**
	 * info 商品資訊
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	public function info( $id ) {
		//驗證權限
		$this->permission('product/productList' , 'V' , 'A');

		//宣告
		$productModel = new ProductModel();
        $mediaModel = new MediaModel();

		//商品資料
		if(!$product = $productModel->info($id)) {
			return publicFunction::emptyOutput();
		}

		//取商品詳細資料
		$product['detail'] = $productModel->getProductDetail($id);

		//取商品圖片
		$product['picture'] = $mediaModel->getMedia($product['uploadId']);

		//取商品列表
		return publicFunction::json([
			'data' => $product
		] , 'success');
	}

	/**
	 * store 新增商品
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return boolean
	 */
	public function store() {
		//驗證權限
		$this->permission('product/productList' , 'E' , 'A');

		//宣告
		$logRecordData = [];
		$productDetail = [];
		$productModel = new ProductModel();

		//規則
		$require = [
			'productCategoryId' => 'required|integer|lenMax:10' ,
			'detail' => 'required|array' ,
			'model' => 'required|string|lenMax:64|lenMin:3' ,
			'costPrice' => 'required|number' ,
			'price' => 'required|number' ,
			'sortOrder' => 'required|int|lenMax:10' ,
			'status' => 'required|in:Y&N' ,
		];

		//驗證
		validator::make(request::$post , $require);

		//檢查商品詳細資料格式
		foreach(request::$post['detail'] as $language => $detail) {
			//判斷傳送語系是否合法
			if(!in_array($language , publicFunction::getSystemCode()['language'])) {
				publicFunction::error('0400');
			}

			//規則
			$require = [
				'name' => 'required|string|lenMax:64' ,
				'description' => 'required|string|lenMax:255|lenMin:3' ,
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

			//商品資料
			$product = publicFunction::fillArray(request::$post, [
				'productCategoryId', 'model', 'costPrice', 'price', 'sortOrder', 'status'
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
			foreach(request::$post['detail'] as $language => $detail) {
				$productDetail = publicFunction::fillArray($detail , [
					'name' , 'description' , 'metaTitle' , 'metaKeyword' , 'metaDescription' , 'tag'
				]);
				$productDetail['productId'] = $productId;
				$productDetail['language'] = $language;

				//寫入商品詳細資訊
				if(!$productModel->storeProductDetail($productDetail)) {
					throw new Exception(language::getFile()['common']['create']['failed'], '0402');
				}

				//存進log
				$logRecordData['productDetail'][] = $productDetail;
			}

			//操作記錄 @todo
			$this->writeLog(17 , $logRecordData, $productModel->db->getSql());

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
	 * @param int $productID 商品id
	 * @return string|array
	 */
	public function update( $id ) {
		//驗證權限
		$this->permission('product/productList' , 'E' , 'A');

		//宣告
		$productDetail = [];
		$logRecordData = [];
		$productModel = new ProductModel();

		//規則
		$require = [
			'productCategoryId' => 'integer|lenMax:10' ,
			'detail' => 'array' ,
			'model' => 'string|lenMax:64|lenMin:3' ,
			'costPrice' => 'number' ,
			'price' => 'number' ,
			'sortOrder' => 'int|lenMax:10' ,
			'status' => 'in:Y&N' ,
		];

		//驗證
		validator::make(request::$put , $require);

		//檢查商品詳細資料格式
		foreach(request::$put['detail'] as $language => $detail) {
			//判斷傳送語系是否合法
			if(!in_array($language , publicFunction::getSystemCode()['language'])) {
				publicFunction::error('0403');
			}

			//規則
			$require = [
				'name' => 'required|string|lenMax:64' ,
				'description' => 'required|string|lenMax:255|lenMin:3' ,
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
				'productCategoryId', 'model', 'costPrice', 'price', 'sortOrder', 'status'
			]);

			//執行更新
			if(!$productModel->update($id , $product)) {
				throw new Exception(language::getFile()['common']['update']['failed'], '0404');
			}

			//商品資料存進log
			$logRecordData['product'] = $product;

			//更新商品詳細資料
			foreach(request::$put['detail'] as $language => $detail) {
				$productDetail = publicFunction::fillArray($detail , [
					'name' , 'description' , 'metaTitle' , 'metaKeyword' , 'metaDescription' , 'tag'
				]);

				$where = [
					['productId' , '=' , $id] ,
					['language' , '=' , $language] ,
				];

				if(!$productModel->updateProductDetail($productDetail , $where)) {
					throw new Exception(language::getFile()['common']['update']['failed'], '0405');
				}

				//商品資料存進log
				$logRecordData['productDetail'][] = $productDetail;
			}

			//操作記錄
			$this->writeLog(18 , $logRecordData , $productModel->db->getSql());

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
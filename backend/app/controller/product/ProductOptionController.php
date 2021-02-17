<?php
/**
 * ProductOptionController 商品選項控制器
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
 * 【Controller】商品選項控制器
 */
class ProductOptionController extends Controller {

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

	/**
	 * lists 商品選項清單
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	public function lists() {}

	/**
	 * info 商品選項資訊
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	public function info( $id ) {}

	/**
	 * store 新增商品選項
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return boolean
	 */
	public function store() {}

	/**
	 * update 修改商品選項
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $productID 商品選項id
	 * @return string|array
	 */
	public function update( $id ) {}

	/**
	 * delete 刪除商品選項
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
		$productOptionModel = new ProductOptionModel();

		//事務操作存資料
		try {
			//宣告事務語法開始
			$productOptionModel->db->beginTransaction();

			//刪除商品選項
			if(!$productOptionModel->delete(['productOptionId', '=' , $id])) {
				throw new Exception(language::getFile()['common']['delete']['failed'], '0419');
			}

			//操作記錄
			$this->writeLog(20 , [] , $productOptionModel->db->getSql());

			//Commit
			$productOptionModel->db->commit();

		} catch(Exception $exception) {
			//Rollback
			$productOptionModel->db->rollback();

			//回傳錯誤訊息
			publicFunction::error($exception->getCode(), $exception->getMessage());
		}

        //回傳
        publicFunction::json([
            'status' => 'success' ,
            'msg' => language::getFile()['common']['delete']['success'] ,
        ]);
	}

	/**
	 * deleteProductOptionValue 刪除商品選項值
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param $id
	 * @return string
	 */
	public function deleteProductOptionValue( $id ) {
		//驗證權限
		$this->permission('product/productList' , 'E' , 'A');

		//宣告
		$productOptionModel = new ProductOptionModel();

		//事務操作存資料
		try {
			//宣告事務語法開始
			$productOptionModel->db->beginTransaction();

			//刪除商品選項
			if(!$productOptionModel->deleteProductOptionValue(['productOptionValueId', '=' , $id])) {
				throw new Exception(language::getFile()['common']['delete']['failed'], '0420');
			}

			//操作記錄
			$this->writeLog(21 , [] , $productOptionModel->db->getSql());

			//Commit
			$productOptionModel->db->commit();

		} catch(Exception $exception) {
			//Rollback
			$productOptionModel->db->rollback();

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
}
<?php
/**
 * ProductCategoryController 商品分類控制器
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
 * 【Controller】商品分類控制器
 */
class ProductCategoryController extends Controller
{

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

    /**
     * lists 權限清單
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function lists() {
        //驗證權限
        $this->permission('product/productCategory','V','A');

        //宣告
        $data = [];
        $productCategoryModel = new ProductCategoryModel();
        $productModel = new ProductModel();

        //取權限列表
        $productCategories = $productCategoryModel->lists(request::$get);

        //計算該權限下有多少管理者
        foreach ($productCategories as $key => $productCategory) {
            $productCategory['productCount'] = $productModel->getProductByProductCategoryId($productCategory['productCategoryId'])->count;
            $data[] = $productCategory;
        }

        //回傳
        return publicFunction::json([
        	'data' => $data,
	        'pagination' => $productCategoryModel->getPagination()
        ] , 'success');
    }

    /**
     * info 權限資訊
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function info($id) {
        //驗證權限
        $this->permission('product/productCategory','V','A');

        //宣告
        $productCategoryModel = new ProductCategoryModel();

        //商品分類資料
        $productCategory = $productCategoryModel->info($id);

        //取商品詳細資料
        $productCategory['detail'] = $productCategoryModel->getProductCategoryDetail($id);

        //取權限資訊
        return publicFunction::json([
            'data' => $productCategory
        ] , 'success');
    }

	/**
	 * store 新增權限
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function store() {
        //驗證權限
        $this->permission('product/productCategory','E','A');

        //宣告
        $productCategoryModel = new ProductCategoryModel();

        //規則
		$require = [
			'name' => 'required|string',
			'productCategory' => 'required|string',
			'status' => 'required|in:Y&N',
		];

		//驗證
        validator::make(request::$post , $require);

		//檢查名稱有無重覆
		if(!$productCategoryModel->checkExist('name', request::$post['name'])) {
			publicFunction::error('0104');
		}

		//傳送參數
        $sentData = [
            'name' => request::$post['name'],
            'productCategory' => json_encode(request::$post['productCategory']),
            'status' => request::$post['status'],
            'createTime' => date('Y-m-d H:i:s'),
        ];

		//執行更新
        $productCategoryModel->store($sentData);

		//操作記錄
		$this->writeLog(7 , $sentData , $productCategoryModel->db->getSql());

		//回傳
        publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['create']['success'],
        ]);

	}

    /**
     * update 修改權限
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param int $productCategoryID 權限id
     * @return string|array
     */
    public function update($id) {
        //驗證權限
        $this->permission('product/productCategory','E','A');

        //宣告
        $productCategoryModel = new ProductCategoryModel();

        //規則
        $require = [
            'name' => 'required|string',
            'productCategory' => 'required|string',
            'status' => 'required|in:Y&N',
        ];

        //驗證
        validator::make(request::$put , $require);

        //傳送參數
        $sentData = [
            'productCategory' => json_encode(request::$put['productCategory']),
            'name' => request::$put['name'],
            'status' => request::$put['status'],
        ];

        //執行更新
        $productCategoryModel->update($id, $sentData);

	    //操作記錄
	    $this->writeLog(8 , $sentData , $productCategoryModel->db->getSql());

	    //回傳
        publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['update']['success'],
        ]);
    }

    /**
     * delete 刪除權限
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $id
     * @return string
     */
    public function delete ($id) {
        //驗證權限
        $this->permission('product/productCategory','E','A');

        //宣告
        $productCategoryModel = new ProductCategoryModel();

        //單筆或多筆刪除
        $productCategoryModel->delete(['productCategoryId', 'IN', '( ' . $id . ' )']);

	    //操作記錄
	    $this->writeLog(9 , [] , $productCategoryModel->db->getSql());

	    //回傳
        return publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['delete']['success'],
        ]);
    }

    /**
     * getProductCategoryConfig 依系統預設權限
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return mixed
     */
    public function getProductCategoryConfig() {
        return publicFunction::json([
            'status' => 'success',
            'data' => publicFunction::getSystemCode()['productCategory'],
        ]);
    }

    //----------------------------------------------------------------
    //EndRegion API
    //----------------------------------------------------------------
}
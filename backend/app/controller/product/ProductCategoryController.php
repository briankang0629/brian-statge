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

    /** @var string $tempName 暫存名稱 */
    private $tempName = '';

    /**
     * lists 權限清單
     *
     * @since 0.0.1
     * @version 0.0.1
     */
    public function lists () {
        //驗證權限
        $this->permission(['A'], 'product/productCategory', 'V');

        //宣告
        $data = [];
        $productCategoryModel = new ProductCategoryModel();

        //取商品分類列表
        $productCategories = $productCategoryModel->lists(request::$get);

        //計算該商品分類下有多少管理者
        foreach ($productCategories as $key => $productCategory) {
            //計算商品歸類數
            $productCategory['productCount'] = $productCategoryModel->getProductByProductCategoryId($productCategory['productCategoryId'])->count;

            //製作分類名稱
            if ($productCategory['parentId']) {
                $productCategory['name'] = $this->makeProductCategoryName($productCategory, $productCategories);
                $productCategories[$key] = $productCategory;
            }

            $data[] = $productCategory;
        }

        //回傳
        publicFunction::json([
            'data'       => $data,
            'pagination' => $productCategoryModel->getPagination()
        ], 'success');
    }

    /**
     * info 商品分類資訊
     *
     * @since 0.0.1
     * @version 0.0.1
     */
    public function info ($id) {
        //驗證權限
        $this->permission(['A'], 'product/productCategory', 'V');

        //宣告
        $productCategoryModel = new ProductCategoryModel();

        //商品分類資料
        $productCategory = $productCategoryModel->info($id);

        //取商品詳細資料
        $productCategory['detail'] = $productCategoryModel->getProductCategoryDetail($id);

        //取權限資訊
        publicFunction::json([
            'data' => $productCategory
        ], 'success');
    }

    /**
     * store 新增權限
     *
     * @since 0.1.0
     * @return boolean
     */
    public function store () {
        //驗證權限
        $this->permission(['A'], 'product/productCategory', 'E');

        //宣告
        $productCategoryModel = new ProductCategoryModel();

        //規則
        $require = [
            'name'            => 'required|string',
            'productCategory' => 'required|string',
            'status'          => 'required|in["Y" , "N"]',
        ];

        //驗證
        validator::make(request::$post, $require);

        //檢查名稱有無重覆
        if ( !$productCategoryModel->checkExist('name', request::$post['name'])) {
            publicFunction::error('0104');
        }

        //傳送參數
        $sentData = [
            'name'            => request::$post['name'],
            'productCategory' => json_encode(request::$post['productCategory']),
            'status'          => request::$post['status'],
            'createTime'      => date('Y-m-d H:i:s'),
        ];

        //執行更新
        $productCategoryModel->store($sentData);

        //操作記錄
        $this->writeLog(7, $sentData, $productCategoryModel->db->getSql());

        //回傳
        publicFunction::json([
            'status' => 'success',
            'msg'    => language::getFile()['common']['create']['success'],
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
    public function update ($id) {
        //驗證權限
        $this->permission(['A'], 'product/productCategory', 'E');

        //宣告
        $productCategoryModel = new ProductCategoryModel();

        //規則
        $require = [
            'name'            => 'required|string',
            'productCategory' => 'required|string',
            'status'          => 'required|in["Y" , "N"]',
        ];

        //驗證
        validator::make(request::$put, $require);

        //傳送參數
        $sentData = [
            'productCategory' => json_encode(request::$put['productCategory']),
            'name'            => request::$put['name'],
            'status'          => request::$put['status'],
        ];

        //執行更新
        $productCategoryModel->update($id, $sentData);

        //操作記錄
        $this->writeLog(8, $sentData, $productCategoryModel->db->getSql());

        //回傳
        publicFunction::json([
            'status' => 'success',
            'msg'    => language::getFile()['common']['update']['success'],
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
        $this->permission(['A'], 'product/productCategory', 'E');

        //宣告
        $productCategoryModel = new ProductCategoryModel();

        //單筆或多筆刪除
        $productCategoryModel->delete([
            'productCategoryId',
            'IN',
            '( ' . $id . ' )'
        ]);

        //操作記錄
        $this->writeLog(9, [], $productCategoryModel->db->getSql());

        //回傳
        publicFunction::json([
            'status' => 'success',
            'msg'    => language::getFile()['common']['delete']['success'],
        ]);
    }

    //----------------------------------------------------------------
    //EndRegion API
    //----------------------------------------------------------------

	//----------------------------------------------------------------
	// 客製化功能 API Start
	//----------------------------------------------------------------

	/**
	 * getProductCategories 取商品分類列表
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return mixed
	 */
	public function getProductCategories () {
		//驗證權限
		$this->permission(['U']);

		//宣告
		$data = [];
		$productCategoryModel = new ProductCategoryModel();

		//取商品分類列表
		$productCategories = $productCategoryModel->lists(request::$get);
		foreach($productCategories as $key => $item) {
			//第一層母選單
			if($item['parentId'] === 0) {
				$parentProductCategories[] = $item;
			} else {
				$subProductCategories[] = $item;
			}
		}

		//製作分類樹狀結構
		foreach($parentProductCategories as $key => $category) {
			//製作子選單
			$category['sub'] = $this->makeProductCategoryTree($subProductCategories , $category['productCategoryId']);

			//存進資料內
			$data[] = $category;
		}

		//回傳
		publicFunction::json([
			'status' => 'success',
			'data'   => $data,
		]);
	}

    /**
     * getProductCategory 取指定商品分類
     *
     * @param int $productCategoryId
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getProductCategory( $productCategoryId ) {
        //驗證權限
        $this->permission(['U']);

        //驗證參數
        validator::make(request::$get, [
            'language' => 'required|in' . json_encode(publicFunction::getSystemCode()['language'])
        ]);

        //宣告
        $productCategoryModel = new ProductCategoryModel();

        //商品分類資料
        $data = $productCategoryModel->info($productCategoryId);

        //取商品詳細資料
        $data['detail'] = $productCategoryModel->getProductCategoryDetailByLanguage($productCategoryId , request::$get['language']);

        //取商品列表
        publicFunction::json([
            'data' => $data ,
        ] , 'success');
    }

    /**
     * getProductCategoryTree 取商品分類的家族樹
     *
     * @param int $productCategoryId
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getProductCategoryTree( $productCategoryId ) {
        //驗證權限
        $this->permission(['U']);

        //驗證參數
        validator::make(request::$get, [
            'language' => 'required|in' . json_encode(publicFunction::getSystemCode()['language'])
        ]);

        //宣告
        $data = [];
        $productCategoryModel = new ProductCategoryModel();

        //S ==================== 商品分類處理 ==================== //
        //製作分類麵包穴導覽
        foreach (json_decode($productCategoryModel->info($productCategoryId)['family'] , true) as $key => $categoryId) {
            //取商品詳細資料
            $productCategory = $productCategoryModel->info($categoryId);
            $productCategory['detail'] = $productCategoryModel->getProductCategoryDetailByLanguage($productCategory['productCategoryId'] , request::$get['language']);

            //存進家族樹內
            $data[] = $productCategory;
        }

        //E ==================== 商品分類處理 ==================== //

        //取商品列表
        publicFunction::json([
            'data' => $data ,
        ] , 'success');
    }

	//----------------------------------------------------------------
	// 客製化功能 API End
	//----------------------------------------------------------------

    //----------------------------------------------------------------
    //Start 附屬函示
    //----------------------------------------------------------------

    /**
     * makeProductCategoryName 依據商品分類製作層級的名稱
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param array $category
     * @param array $categories
     * @return mixed
     */
    private function makeProductCategoryName ($category , $categories ) {
        $this->tempName = $category['name'];

        foreach ($categories as $key => $item) {
            if($category['parentId'] === $item['productCategoryId']) {
                $category['name'] = $item['name']  . ' > ' . $this->tempName;
            } else {
                unset($categories[$key]);
                continue;
            }
        }

        return $category['name'];
    }

	/**
	 * makeProductCategoryTree 依據商品分類製作家族數
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $categories
	 * @param int $parentId
	 * @return mixed
	 */
	private function makeProductCategoryTree ($categories , $parentId = 0) {
		$tempArray = [];
		foreach ($categories as $key => $category) {
			if($category['parentId'] == $parentId) {
				unset($categories[$key]);
				$category['sub'] = $this->makeProductCategoryTree($categories , $category['productCategoryId']);
				$tempArray[] = $category;
			}
		}

		return $tempArray;
	}

    //----------------------------------------------------------------
    //End 附屬函示
    //----------------------------------------------------------------
}

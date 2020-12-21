<?php
/**
 * ProductModel 商品模型
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/12/17
 */


/**---------------------------------------------------------
 *                      程式固定變數
 *----------------------------------------------------------
 *  | 1.$table   |  此Model必要的資料庫table
 *----------------------------------------------------------
 *                       程式寫作規則
 *----------------------------------------------------------
 *  | 1.lists    |  取列表
 *  | 2.info     |  取指定ID的資料
 *  | 3.store    |  存取資料
 *  | 4.update   |  更新指定Id的資料
 *  | 5.delete   |  刪除指定資料
 *----------------------------------------------------------
 *                        客製化內容
 *----------------------------------------------------------
 */

use Libraries\PublicFunction as publicFunction;

/**
 * 【Model】商品模型
 */
class ProductModel extends Model {
	/** @var string table名稱 */
	public $table = 'product';

	/** @var array 預設欄位 */
	protected $filed = [
		'productId' , 'productCategoryId' , 'model' , 'costPrice' , 'price' , 'sortOrder' , 'view' , 'status' , 'uploadId' , 'createTime' , 'updateTime'
	];

	/** @var array 關連欄位 */
	public $relateWith = [
		'productDetail' => [
			'filed' => [
				'productDetail.name AS name' ,
				'productDetail.language AS language'
			] ,
			'join' => [
				'table' => 'productDetail' ,
				'joinKey' => 'productId'
			] ,
		] ,
	];

	/** @var string primary 主鍵 */
	private $primaryKey = 'productId';

	/**
	 * Lists 取商品列表
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @return mixed
	 */
	public function lists( $data = array() ) {
		$where = [];

		//沒有指定語系選擇預設語系
		if(!isset($data['language'])) {
			$where[] = ['language' , '=' , publicFunction::getSystemCode()['defaultLanguage']];
		} else {
			$where[] = ['language' , '=' , $data['language']];
		}

		//有商品型號過濾
		if(isset($data['model'])) {
			$where[] = ['model' , 'LIKE' , '%' . $data['model'] . '%'];
		}

		//有商品分類過濾
		if(isset($data['productCategoryId'])) {
			$where[] = ['productCategoryId' , '=' , $data['productCategoryId']];
		}

		//狀態 過濾 @todo pdo
		if(isset($data['status'])) {
			$where[] = ['status' , '=' , $data['status']];
		}

		//組關聯要的欄位
		$this->makeMerge('productDetail');

		//宣告頁碼class
		$this->makePagination($this->primaryKey , $data , $where , $this->relateWith['productDetail']['join']);

		//回傳
		return $this->db->table($this->table)->select($this->filed)->join('productDetail' , $this->primaryKey)->where($where , true)->orderBy('sortOrder' , 'ASC' , true)->limit($this->pagination->start , $this->pagination->perPage)->rows;
	}

	/**
	 * info 取商品資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $productId
	 * @return mixed
	 */
	public function info( $productId ) {
		//回傳
		return $this->db->table($this->table)->select($this->filed)->where(['productId' , '=' , $productId])->row;
	}

	/**
	 * store 新增商品資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @return mixed
	 */
	public function store( $data ) {
		return $this->db->table($this->table)->insert($data);
	}

	/**
	 * update 更新商品資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $productId
	 * @param array $data
	 * @return mixed
	 */
	public function update( $productId , $data ) {
		return $this->db->table($this->table)->update($data)->where(['productId' , '=' , $productId]);
	}

	/**
	 * delete 刪除商品資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param array $where
     * @return mixed
     */
    public function delete( $where ) {
        return $this->db->table($this->table)->delete()->where($where);
    }

	//S == 客製化區塊 ========================================//

	/**
	 * getProductDetail 依ID取商品詳細資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $productId
	 * @return mixed
	 */
	public function getProductDetail( $productId ) {
		return $this->db->table('productDetail')->select(['name' , 'language' , 'metaTitle' , 'metaKeyword', 'metaDescription'])->where(['productId' , ' = ' , $productId])->rows;
    }

	/**
	 * storeProductDetail 新增商品詳細資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @return mixed
	 */
	public function storeProductDetail( $data ) {
		return $this->db->table('productDetail')->insert($data);
	}

	/**
	 * updateProductDetail 更新商品詳細資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @param array $where
	 * @return mixed
	 */
	public function updateProductDetail( $data , $where ) {
		return $this->db->table('productDetail')->update($data)->where($where);
	}

    /**
     * getProductByProductCategoryId 依商品分類ID取商品數量
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param int $productCategoryId
     * @return mixed
     */
    public function getProductByProductCategoryId( $productCategoryId ) {
        return $this->db->table($this->table)->select($this->primaryKey)->where(['productCategoryId', '=' , $productCategoryId]);
    }
    //E == 客製化區塊 ========================================//
}
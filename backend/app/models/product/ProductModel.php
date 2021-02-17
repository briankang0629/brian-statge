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
		'productId' , 'model' , 'costPrice' , 'price' , 'sortOrder' , 'view' , 'status' , 'uploadId' , 'createTime' , 'updateTime'
	];

	/** @var string primary 主鍵 */
	private $primaryKey = 'productId';

    /** @var array $cast 主鍵 */
    protected $casts = [
        'costPrice' => 'double',
        'price' => 'double',
    ];

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

		//關聯欄位設定
		$this->filed = $this->makeMerge([
			'pd.name' ,
			'pd.language' ,
			'p2c.productCategoryId'
		]);

		//宣告頁碼class
		$this->makePagination($this->primaryKey , $data , $where , [
			['table' => 'productDetail', 'joinKey' => 'productId'],
			['table' => 'productToCategory', 'joinKey' => 'productId']
		], $this->primaryKey);

		//回傳
		return $this->makeCast($this->db->table($this->table)->select($this->filed)
			->join('productDetail pd', $this->primaryKey)
			->join('productToCategory p2c', $this->primaryKey)
			->where($where , true)
			->groupBy($this->primaryKey , true)
			->orderBy('sortOrder, productId' , 'ASC' , true)
			->limit($this->pagination->start , $this->pagination->perPage)->rows);
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
		return $this->makeCast($this->db->table($this->table)->select($this->filed)->where(['productId' , '=' , $productId])->row);
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
    	return (
    		$this->db->table($this->table)->delete()->where($where) &&
		    $this->db->table('productDetail')->delete()->where($where)
	    );
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
		return $this->db->table('productDetail')->select(['name' , 'description' , 'language' , 'metaTitle' , 'metaKeyword', 'metaDescription'])->where(['productId' , ' = ' , $productId])->rows;
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
     * getProductCategoryByProductId 依管理者ID取商品分類ID
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param int $productId
     * @return mixed
     */
    public function getProductCategoryByProductId( $productId ) {
        return $this->db->table('productToCategory')->select('productCategoryId')->where(['productId', '=' , $productId])->rows;
    }

	/**
	 * updateProductToCategory 更新商品所屬分類
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $id
	 * @param array $categories
	 * @return mixed
	 */
	public function updateProductToCategory( $id , $categories ) {
		if(!$this->db->table('productToCategory')->delete()->where([$this->primaryKey, '=' ,$id])) {
			return false;
		}

		if(count($categories) < 1) {
		    return true;
        }

		foreach($categories as $productCategoryId) {
			if(!$this->db->table('productToCategory')->insert([
				$this->primaryKey => $id ,
				'productCategoryId' => $productCategoryId
			])) {
				return false;
			}
		}

		return true;
	}

	/**
	 * deleteProductToCategory 刪除商品所屬分類
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $where
	 * @return boolean
	 */
	public function deleteProductToCategory($where) {
		return $this->db->table('productToCategory')->delete()->where($where);
	}

    //E == 客製化區塊 ========================================//
}
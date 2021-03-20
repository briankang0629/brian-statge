<?php
/**
 * ProductCategoryModel 商品分類模型
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/12/17
 * @since 0.1.0 2020/12/17
 */

use Libraries\PublicFunction as publicFunction;

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

/**
 * 【Model】商品分類模型
 */
class ProductCategoryModel extends Model {
	/** @var string table名稱 */
	public $table = 'productCategory';

	/** @var array 預設欄位 */
	protected $filed = [
		'productCategoryId' , 'parentId', 'status', 'sortOrder', 'createTime' , 'updateTime', 'family' , 'level'
	];

	/** @var string primary 主鍵 */
	private $primaryKey = 'productCategoryId';

    /** @var array $cast 主鍵 */
    protected $casts = [
        'productCategoryId' => 'integer',
        'parentId' => 'integer',
        'sortOrder' => 'integer',
    ];

	/**
	 * Lists 取會員列表
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @return mixed
	 */
	public function lists( $data = array() ) {
	    //宣告
		$where = [];

        //沒有指定語系選擇預設語系
        if(!isset($data['language'])) {
            $where[] = ['language' , '=' , publicFunction::getSystemCode()['defaultLanguage']];
        } else {
            $where[] = ['language' , '=' , $data['language']];
        }

		//name 過濾
		if(isset($data['name'])) {
			$where[] = ['name' , 'LIKE' , '%' . $data['name'] . '%'];
		}

		//狀態 過濾
		if(isset($data['status'])) {
			$where[] = ['status' , '=' , $data['status']];
		}

		//欄位設定
		$this->filed = $this->makeMerge([
	        'pcd.name' ,
			'pcd.language'
        ]);

        //宣告頁碼class
        $this->makePagination($this->primaryKey , $data , $where , [
	        ['table' => 'productCategoryDetail', 'joinKey' => 'productCategoryId'],
        ]);

		//回傳
		return $this->makeCast($this->db->table($this->table)->select($this->filed)
			->join('productCategoryDetail pcd' , $this->primaryKey)
			->where($where , true)
			->orderBy('level' , 'ASC' , true)
			->limit($this->pagination->start , $this->pagination->perPage)->rows
		);
	}

	/**
	 * info 取會員資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $productCategoryId
	 * @return mixed
	 */
	public function info( $productCategoryId ) {
		return $this->db->table($this->table)->select($this->filed)->where(['productCategoryId' , '=' , $productCategoryId])->row;
	}

	/**
	 * store 新增會員資料
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
	 * update 更新會員資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $productCategoryId
	 * @param array $data
	 * @return mixed
	 */
	public function update( $productCategoryId , $data ) {
		return $this->db->table($this->table)->update($data)->where(['productCategoryId' , '=' , $productCategoryId]);
	}

	/**
	 * delete 刪除會員資料
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
     * getProductCategoryDetail 依ID取商品分類詳細資料
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param int $productCategoryId
     * @return mixed
     */
    public function getProductCategoryDetail( $productCategoryId ) {
        return $this->db->table('productCategoryDetail')->select(['name' , 'language' , 'metaTitle' , 'metaKeyword', 'metaDescription'])->where(['productCategoryId', '=' , $productCategoryId])->rows;
    }

    /**
     * getProductCategoryDetailByLanguage 依ID與語系取商品分類詳細資料
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param int $productCategoryId
     * @param string $language
     * @return mixed
     */
    public function getProductCategoryDetailByLanguage( $productCategoryId , $language = 'zh-tw') {
        return $this->db->table('productCategoryDetail')->select(['name' , 'language' , 'description' , 'metaTitle' , 'metaKeyword', 'metaDescription'])->where([
            [ 'productCategoryId', '=' , $productCategoryId ],
            [ 'language', '=' , $language ],
        ])->row;
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
        return $this->db->table('productToCategory')->select($this->primaryKey)->where(['productCategoryId', '=' , $productCategoryId]);
    }

    //E == 客製化區塊 ========================================//
}
<?php
/**
 * ProductOptionModel 商品選項模型
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2021/01/30
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
 * 【Model】商品選項模型
 */
class ProductOptionModel extends Model {
	/** @var string table名稱 */
	public $table = 'productOption';

	/** @var array 預設欄位 */
	protected $filed = [
		'productOptionId', 'productId' , 'multiple' , 'sortOrder' , 'required'
	];

	/** @var string primary 主鍵 */
	private $primaryKey = 'productOptionId';

    /** @var array $cast 主鍵 */
    protected $casts = [
        'point' => 'double',
        'price' => 'double',
        'weight' => 'double',
    ];

	/**
	 * Lists 取商品選項列表
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @return mixed
	 */
	public function lists( $data = array() ) {
		$where = [];

		//有商品ID過濾
		if(isset($data['productId'])) {
			$where[] = ['productId' , '=' , $data['productId']];
		}

		//回傳
		return $this->makeCast($this->db->table($this->table)->select($this->filed)
			->where($where , true)
			->orderBy('sortOrder, productOptionId' , 'ASC')->rows);
	}

	/**
	 * info 取商品選項資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $productOptionId
	 * @return mixed
	 */
	public function info( $productOptionId ) {
		return $this->makeCast($this->db->table($this->table)->select($this->filed)->where(['productOptionId' , '=' , $productOptionId])->row);
	}

	/**
	 * store 新增商品選項資料
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
	 * update 更新商品選項資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $productOptionId
	 * @param array $data
	 * @return mixed
	 */
	public function update( $productOptionId , $data ) {
		return $this->db->table($this->table)->update($data)->where(['productOptionId' , '=' , $productOptionId]);
	}

	/**
	 * delete 刪除商品選項資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param array $where
     * @return mixed
     */
    public function delete( $where ) {
    	//判斷是否刪除商品選項值
    	if(!$productOptionValueArray = $this->db->table('productOptionValue')->select('productOptionValueId')->where($where)->rows) {
		    return (
			    $this->db->table($this->table)->delete()->where($where) &&
			    $this->db->table('productOptionDetail')->delete()->where($where)
		    );
	    } else {
		    //組成要刪除的productOptionValueId
		    foreach($productOptionValueArray as $productOptionValue) {
			    $productOptionValueId[] = $productOptionValue['productOptionValueId'];
		    }
		    $productOptionValueId = implode(',' , $productOptionValueId);

		    return (
			    $this->db->table($this->table)->delete()->where($where) &&
			    $this->db->table('productOptionDetail')->delete()->where($where) &&
			    $this->deleteProductOptionValue(['productOptionValueId', 'IN', '( ' . $productOptionValueId . ' )'])
		    );
	    }
    }

	//S == 客製化區塊 ========================================//

	/**
	 * getProductOptionDetail 依ID取商品選項詳細資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $productOptionId
	 * @return mixed
	 */
	public function getProductOptionDetail( $productOptionId ) {
		return $this->db->table('productOptionDetail')->select(['name' , 'language'])->where(['productOptionId' , ' = ' , $productOptionId])->rows;
    }

    /**
     * getProductOptionValue 依ID取商品選項值詳細資料
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param int $productOptionId
     * @return mixed
     */
    public function getProductOptionValue( $productOptionId ) {
        return $this->makeCast($this->db->table('productOptionValue')->select(['productOptionValueId' , 'price' , 'sortOrder' , 'quantity' , 'point' , 'weight' , 'isStock'])->where(['productOptionId' , ' = ' , $productOptionId])->rows);
    }

    /**
     * getProductOptionValueDetail 依商品選項值ID取詳細資料
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param int $productOptionValueId
     * @return mixed
     */
    public function getProductOptionValueDetail( $productOptionValueId ) {
        return $this->db->table('productOptionValueDetail')->select(['language' , 'name'])->where(['productOptionValueId' , ' = ' , $productOptionValueId])->rows;
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
	 * updateProductOptionDetail 更新商品選項詳細資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @param array $where
	 * @return mixed
	 */
	public function updateProductOptionDetail( $data , $where ) {
        if(!$this->db->table('productOptionDetail')->delete()->where($where)) {
            return false;
        }
        return $this->db->table('productOptionDetail')->insert($data);
	}

    /**
     * storeProductOptionValue 新增一筆商品選值
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param array $data
     * @return mixed
     */
    public function storeProductOptionValue( $data ) {
        return $this->db->table('productOptionValue')->insert($data);
    }

    /**
     * updateProductOptionValue 更新商品選值
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param int $productOptionValueId
     * @param array $data
     * @return mixed
     */
    public function updateProductOptionValue( $productOptionValueId , $data ) {
        return $this->db->table('productOptionValue')->update($data)->where(['productOptionValueId' , '=' , $productOptionValueId]);
    }

    /**
     * updateProductOptionValueDetail 更新商品選值詳細資料
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param array $data
     * @param array $where
     * @return mixed
     */
    public function updateProductOptionValueDetail( $data , $where ) {
        if(!$this->db->table('productOptionValueDetail')->delete()->where($where)) {
            return false;
        }
        return $this->db->table('productOptionValueDetail')->insert($data);
    }

	/**
	 * deleteProductOptionValue 刪除商品選項值資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $where
	 * @return mixed
	 */
	public function deleteProductOptionValue( $where ) {
		return(
			$this->db->table('productOptionValue')->delete()->where($where) &&
			$this->db->table('productOptionValueDetail')->delete()->where($where)
		);
	}
    //E == 客製化區塊 ========================================//
}
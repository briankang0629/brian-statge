<?php
/**
 * OrderModel 訂單管理模型
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2021/02/21
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
 * 【Model】訂單管理模型
 */
class OrderModel extends Model {
	/** @var string table名稱 */
	public $table = '`order`';

	/** @var array 預設欄位 */
	protected $filed = [
		'orderId' , 'permissionId' , 'name' , 'account' , 'password' , 'email' , 'createTime' , 'updateTime' , 'status', 'sub', 'uploadId', 'picture'
	];

	/** @var string primary 主鍵 */
	private $primaryKey = 'orderId';

    /** @var array $cast 主鍵 */
    protected $casts = [
        'costPrice' => 'numberFormat',
        'price' => 'numberFormat',
    ];


	/**
	 * Lists 取訂單管理列表
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @return mixed
	 */
	public function lists( $data = array() ) {}

	/**
	 * info 取訂單管理資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $orderId
	 * @return mixed
	 */
	public function info( $orderId ) {}

	/**
	 * store 新增訂單管理資料
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
	 * update 更新訂單管理資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $orderId
	 * @param array $data
	 * @return mixed
	 */
	public function update( $orderId , $data ) {
		return $this->db->table($this->table)->update($data)->where(['orderId' , '=' , $orderId]);
	}

	/**
	 * delete 刪除訂單管理資料
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
     * getOrderGroupByMonth 取月份訂單 @todo此功能屬於首頁數據模組
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param array $data
     * @return object
     */
    public function getOrderGroupByMonth() {
        return  $this->db->query("SELECT COUNT(orderId) AS total, createTime FROM `order` WHERE YEAR(createTime) = YEAR(NOW()) GROUP BY MONTH(createTime)")->rows;
    }

    /**
     * getOrderTotal 取訂單總數
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return object
     */
    public function getOrderTotal() {
        return $this->db->table($this->table)->select($this->primaryKey)->where()->count;
    }

    /**
     * getOrderSale 取訂單銷售總額
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return object
     */
    public function getOrderSale() {
        return $this->db->table('orderTotal')->select('SUM(value) AS total')->where()->row['total'];
    }

    /**
     * getSaleHotProduct 取商品熱銷排行
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return object
     */
    public function getSaleHotProduct($data) {
        //沒有指定語系選擇預設語系
        if(!isset($data['language'])) {
            $where[] = ['language' , '=' , publicFunction::getSystemCode()['defaultLanguage']];
        } else {
            $where[] = ['language' , '=' , $data['language']];
        }

        return $this->makeCast($this->db->table('orderProduct')->select(['productId', 'pd.name', 'pd.description', 'p.costPrice', 'COUNT(productId) AS total', 'p.price' , 'p.model', 'p.costPrice', 'p.uploadId'])
            ->join('product p', 'productId')
            ->join('productDetail pd', 'productId')
            ->where($where, true)
            ->groupBy('productId' , true)
            ->orderBy('total' , 'DESC' , true)
            ->limit($data['limit']))->rows;
    }

    /**
     * getOrderByDateRange 取指定時間內訂單資料
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return object
     */
    public function getOrderByDateRange( $data = array() ) {
        //篩選條件
        $where = [
            ['createTime' , '>' , $data['startDate']],
            ['createTime' , '<=' , $data['endDate']],
        ];

        //有篩選狀態
        if(isset($data['orderStatus'])) {
            $where[] = ['orderStatus', '=' , $data['orderStatus']];
        }

        return $this->db->table($this->table)->select(['orderId', 'orderNumber', 'total', 'createTime', 'paymentCode', 'commission'])
            ->where($where, true)
            ->orderBy('createTime' , 'desc')
            ->rows;
    }

    //E == 客製化區塊 ========================================//
}

<?php
/**
 * LogRecordModel 操作記錄模型
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/11/28
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

use Libraries\Pagination;

/**
 * 【Model】操作記錄模型
 */
class LogRecordModel extends Model {
	/** @var string table名稱 */
	public $table = 'logRecord';

	/** @var array 預設欄位 */
	protected $filed = [
		'logRecordId' , 'logId' , 'adminId' , 'userId' , 'account', 'class',
		'remoteIP', 'class', 'serverAddr', 'serverInfo', 'host', 'content', 'path',
		'sqlString', 'newDate', 'createTime', 'updateTime'
	];

	/** @var string primary 主鍵 */
	private $primaryKey = 'logRecordId';

	/** @var array $cast 主鍵 */
	protected $casts = [
		'serverInfo' => 'array',
		'content' => 'array',
        'logId' => 'int'
	];

	/**
	 * Lists 取操作記錄列表
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @return mixed
	 */
	public function lists( $data = array() ) {
		$where = [];

		//有身分過濾
		if(isset($data['class'])) {
			$where[] = ['class' , '=' , $data['class']];
		}

		//有帳號過濾
		if(isset($data['account'])) {
			$where[] = ['account' , '=' , $data['account']];
		}

		//操作ID 過濾
		if(isset($data['logId'])) {
			$where[] = ['logId' , '=' , $data['logId']];
		}

		//新增日期 過濾
		if(isset($data['startDate']) && isset($data['endDate'])) {
			$where[] = ['CreateTime' , '>' , $data['startDate']];
			$where[] = ['CreateTime' , '<=' , $data['endDate']];
		}

		//client端IP 過濾
		if(isset($data['remoteIP'])) {
			$where[] = ['remoteIP' , '=' , $data['remoteIP']];
		}

		//宣告頁碼class
		$this->makePagination($this->primaryKey , $data , $where);

		//回傳
		return $this->makeCast($this->db->table($this->table)->select($this->filed)
			->where($where, true)
			->limit($this->pagination->start , $this->pagination->perPage)->rows);
	}

	/**
	 * info 取操作記錄資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $logRecordId 操作紀錄ID
	 * @return mixed
	 */
	public function info( $logRecordId ) {
		return $this->makeCast($this->db->table($this->table)->select($this->filed)->where(['logRecordId' , '=' , $logRecordId])->row);
	}

	/**
	 * store 新增操作記錄資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data 儲存資料
	 * @return mixed
	 */
	public function store( $data ) {
		return $this->db->table($this->table)->insert($data);
	}

	/**
	 * update 更新操作記錄資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $logRecordId 操作紀錄ID
	 * @param array $data 更新資料
	 * @return mixed
	 */
	public function update( $logRecordId , $data ) {
		return $this->db->table($this->table)->update($data)->where(['logRecordId' , '=' , $logRecordId]);
	}

	/**
	 * delete 刪除操作記錄資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $where WHERE 條件
	 * @return mixed
	 */
	public function delete( $where ) {
		return $this->db->table($this->table)->delete()->where($where);
	}

    //S == 客製化區塊 ========================================//

    //E == 客製化區塊 ========================================//
}
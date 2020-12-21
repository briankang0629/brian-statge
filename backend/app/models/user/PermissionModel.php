<?php
/**
 * PermissionModel 權限模型
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/11/08
 * @since 0.1.0 2020/11/08
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

/**
 * 【Model】權限模型
 */
class PermissionModel extends Model {
	/** @var string table名稱 */
	public $table = 'permission';

	/** @var array 預設欄位 */
	protected $filed = [
		'permissionId'  , 'name' , 'permission', 'createTime' , 'updateTime' , 'status'
	];

	/** @var array 關連欄位 */
	public $relateWith = [];

	/** @var string primary 主鍵 */
	private $primaryKey = 'permissionId';

    /** @var array $cast 主鍵 */
    protected $casts = [
        'permission' => 'array',
        'name' => 'string'
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

		//name 過濾
		if(isset($data['name'])) {
			$where[] = ['name' , 'LIKE' , '%' . $data['name'] . '%'];
		}

		//狀態 過濾
		if(isset($data['status'])) {
			$where[] = ['status' , '=' , $data['status']];
		}

        //權限列表不包含自身權限
        $where[] = ['permissionId', '!=' , $this->getPermissionIdByAdminId(AdminID)];

		//宣告頁碼class
		$this->makePagination($this->primaryKey , $data , $where);

		//回傳
		return $this->makeCast($this->db->table($this->table)->select($this->filed)->where($where , true)->orderBy('permissionId' , 'ASC' , true)->limit($this->pagination->start , $this->pagination->perPage)->rows);
	}

	/**
	 * info 取會員資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $permissionId
	 * @return mixed
	 */
	public function info( $permissionId ) {
		return $this->makeCast($this->db->table($this->table)->select($this->filed)->where(['permissionId' , '=' , $permissionId])->row);
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
	 * @param int $permissionId
	 * @param array $data
	 * @return mixed
	 */
	public function update( $permissionId , $data ) {
		return $this->db->table($this->table)->update($data)->where(['permissionId' , '=' , $permissionId]);
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
     * getPermissionByAdminId 依管理者ID取權限
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param int $adminId
     * @return mixed
     */
    public function getPermissionByAdminId( $adminId ) {
        return $this->makeCast($this->db->table($this->table)->select('permission')->join('admin', 'permissionId')->where(['adminId', '=' , $adminId])->row);
    }

    /**
     * getPermissionIdByAdminId 依管理者ID取權限ID
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param int $adminId
     * @return mixed
     */
    public function getPermissionIdByAdminId( $adminId ) {
        return $this->db->table('admin')->select('permissionId')->where(['adminId', '=' , $adminId])->row['permissionId'];
    }
    //E == 客製化區塊 ========================================//
}
<?php
/**
 * AdminModel 管理者模型
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/11/10
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
 * 【Model】管理者模型
 */
class AdminModel extends Model {
	/** @var string table名稱 */
	public $table = 'admin';

	/** @var array 預設欄位 */
	protected $filed = [
		'adminId' , 'permissionId' , 'name' , 'account' , 'password' , 'email' , 'createTime' , 'updateTime' , 'status', 'sub', 'uploadId', 'picture'
	];

	/** @var array 關連欄位 */
	public $relateWith = [
		'permission' => [
			'filed' => ['permission.name AS permission' , 'permission.name as permission'] ,
			'join' => ['table' => 'permission' , 'joinKey' => 'permissionId'] ,
		] ,
	];

	/** @var string primary 主鍵 */
	private $primaryKey = 'adminId';

	/**
	 * Lists 取管理者列表
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @return mixed
	 */
	public function lists( $data = array() ) {
		$where = [];
		//有管理者帳號過濾
		if(isset($data['account'])) {
			$where[] = ['account' , 'LIKE' , '%' . $data['account'] . '%'];
		}

		//email 過濾
		if(isset($data['email'])) {
			$where[] = ['email' , 'LIKE' , '%' . $data['email'] . '%'];
		}

        //權限 過濾
        if(isset($data['permissionId'])) {
            $where[] = ['permissionId' , '=' , $data['permissionId']];
        }

        //狀態 過濾 @todo pdo
		if(isset($data['status'])) {
			$where[] = [$this->table . '.status' , '=' , $data['status']];
		}

        //狀態 過濾 @todo pdo
        if(isset($data['name'])) {
            $where[] = [$this->table . '.name' , 'LIKE' , '%' . $data['name'] . '%'];
        }

		//管理者列表不包含自身
        $where[] = ['adminId', '!=' , AdminID];

		//組關聯要的欄位
		$this->makeMerge('permission');

		//宣告頁碼class
		$this->makePagination($this->primaryKey , $data , $where);

		return $this->db->table($this->table)->select($this->filed)->join('permission' , 'permissionId')->where($where , true)->orderBy('adminId' , 'ASC' , true)->limit($this->pagination->start , $this->pagination->perPage)->rows;
	}

	/**
	 * info 取管理者資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $adminId
	 * @return mixed
	 */
	public function info( $adminId ) {
		//回傳
		return $this->db->table($this->table)->select($this->filed)->where(['adminId' , '=' , $adminId])->row;
	}

	/**
	 * store 新增管理者資料
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
	 * update 更新管理者資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $adminId
	 * @param array $data
	 * @return mixed
	 */
	public function update( $adminId , $data ) {
		return $this->db->table($this->table)->update($data)->where(['adminId' , '=' , $adminId]);
	}

	/**
	 * delete 刪除管理者資料
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
     * getAdminCountByPermissionId 依權限ID取管理者
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param int $permissionId
     * @return mixed
     */
    public function getAdminCountByPermissionId( $permissionId ) {
        return $this->db->table($this->table)->select($this->filed)->where(['permissionId' , '=' , $permissionId])->count;
    }
    //E == 客製化區塊 ========================================//
}
<?php
/**
 * UserModel 使用者模型
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2019/07/22
 * @since 0.1.0 2019/07/22
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
 * 【Model】使用者模型
 */
class UserModel extends Model {
	/** @var string table名稱 */
	public $table = 'user';

	/** @var array 預設欄位 */
	protected $filed = [
		'userId' , 'userGroupId' , 'name' , 'account' , 'email' , 'mobile', 'createTime' , 'updateTime' , 'status'
	];

	/** @var array 關連欄位 */
	public $relateWith = [
		'userGroup' => [
			'filed' => ['userGroup.name AS userGroup' , 'userGroup.description'] ,
			'join' => ['table' => 'userGroup' , 'joinKey' => 'userGroupId'] ,
		] ,
	];

	/** @var string primary 主鍵 */
	private $primaryKey = 'userId';

	/**
	 * Lists 取會員列表
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @return mixed
	 */
	public function lists( $data = array() ) {
		$where = [];
		//有會員帳號過濾
		if(isset($data['account'])) {
			$where[] = ['account' , 'LIKE' , '%' . $data['account'] . '%'];
		}

		//email 過濾
		if(isset($data['email'])) {
			$where[] = ['email' , 'LIKE' , '%' . $data['email'] . '%'];
		}

		//狀態 過濾
		if(isset($data['status'])) {
			$where[] = ['status' , '=' , $data['status']];
		}

		//合併關聯欄位
		$this->makeMerge('userGroup');

		//宣告頁碼class
		$this->makePagination($this->primaryKey , $data , $where);

		//回傳
		return $this->db->table($this->table)->select($this->filed)->join('userGroup' , 'userGroupId')->where($where , true)->orderBy('userId' , 'ASC' , true)->limit($this->pagination->start , $this->pagination->perPage)->rows;
	}

	/**
	 * info 取會員資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $userId
	 * @return mixed
	 */
	public function info( $userId ) {
		//合併關聯欄位
		$this->makeMerge('userGroup');

		//回傳
		return $this->db->table($this->table)->select($this->filed)->join('userGroup' , 'userGroupId')->where(['userId' , '=' , $userId])->row;
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
	 * @param int $userId
	 * @param array $data
	 * @return mixed
	 */
	public function update( $userId , $data ) {
		return $this->db->table($this->table)->update($data)->where(['userId' , '=' , $userId]);
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
}
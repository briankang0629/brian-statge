<?php
/**
 * UserGroupModel 使用者群組模型
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/10/24
 * @since 0.1.0 2020/10/24
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
 * 【Model】使用者群組模型
 */
class UserGroupModel extends Model {
	/** @var string table名稱 */
	public $table = 'userGroup';

	/** @var array 預設欄位 */
	protected $filed = [
	    'userGroupId' , 'name' , 'description' , 'createTime' , 'updateTime'
	];

	/** @var string primary 主鍵 */
	private $primaryKey = 'userGroupId';

	/**
	 * Lists 取會員群組列表
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @return mixed
	 */
	public function lists( $data = array() ) {
		$where = [];
		//會員群組名稱過濾
		if(isset($data['name'])) {
			$where[] = ['name' , 'LIKE' , '%' . $data['name'] . '%'];
		}

		//宣告頁碼class
		$this->makePagination($this->primaryKey , $data , $where);

		//回傳
		return $this->db->table($this->table)->select($this->filed)
			->where($where , true)
			->orderBy('userGroupId' , 'ASC' , true)
			->limit($this->pagination->start , $this->pagination->perPage)->rows;
	}

	/**
	 * info 取會員群組資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $userGroupId
	 * @return mixed
	 */
	public function info( $userGroupId ) {
		return $this->db->table($this->table)->select($this->filed)->where(['userGroupId' , '=' , $userGroupId])->row;
	}

	/**
	 * store 新增會員群組資料
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
	 * update 更新會員群組資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $userGroupId
	 * @param array $data
	 * @return mixed
	 */
	public function update( $userGroupId , $data ) {
		return $this->db->table($this->table)->update($data)->where(['userGroupId' , '=' , $userGroupId]);
	}

	/**
	 * delete 刪除會員群組資料
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
<?php
/**
 * SystemMenuModel 後後台選單模型
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/11/22
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
 * 【Model】後後台選單模型
 */
class SystemMenuModel extends Model {
	/** @var string table名稱 */
	public $table = 'systemMenu';

	/** @var array 預設欄位 */
	protected $filed = [
		'systemMenuId' , 'parentId' , 'code' , 'route', 'icon', 'status'
	];

	/** @var string primary 主鍵 */
	private $primaryKey = 'systemMenuId';

	/**
	 * Lists 取後台選單列表
	 *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param array $data
	 * @return mixed
	 */
	public function lists( $data = array() ) {
        $where = [];

        //有狀態過濾
        if(isset($data['status'])) {
            $where[] = ['status' , '=' , $data['status']];
        }

		return $this->db->table($this->table)->select($this->filed)
			->where($where, true)
			->orderBy($this->primaryKey , 'ASC')->rows;
	}

	/**
	 * info 取後台選單資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $systemMenuId 系統選單ID
	 * @return mixed
	 */
	public function info( $systemMenuId ) {
		return $this->db->table($this->table)->select($this->filed)->where(['systemMenuId' , '=' , $systemMenuId])->row;
	}

	/**
	 * store 新增後台選單資料
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
	 * update 更新後台選單資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $systemMenuId 系統選單ID
	 * @param array $data 更新資料
	 * @return mixed
	 */
	public function update( $systemMenuId , $data ) {
		return $this->db->table($this->table)->update($data)->where(['systemMenuId' , '=' , $systemMenuId]);
	}

	/**
	 * delete 刪除後台選單資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $where WHERE 語句
	 * @return mixed
	 */
	public function delete( $where ) {
		return $this->db->table($this->table)->delete()->where($where);
	}

    //S == 客製化區塊 ========================================//
    /**
     * getSystemMenuByCode 依 Code 取指定選單
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param string $code
     * @return mixed
     */
    public function getSystemMenuByCode( $code ) {
        return $this->db->table($this->table)->select($this->filed)->where([
            ['code' , '=' , $code],
            ['status' , '=' , 'Y']
        ])->row;
    }

    /**
     * getSubMenuByParentId 依上層ID取所屬的子選單
     *
     * @param int $parentId
     * @since 0.0.1
     * @version 0.0.1
     * @return mixed
     */
    public function getSubMenuByParentId( $parentId ) {
        return $this->db->table($this->table)->select($this->filed)->where([
            ['parentId' , '=' , $parentId],
            ['status' , '=' , 'Y']
        ])->rows;
    }

    //E == 客製化區塊 ========================================//
}
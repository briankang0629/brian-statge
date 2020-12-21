<?php
/**
 * MediaModel 多媒體模型(上傳功能)
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/12/09
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
 * 【Model】多媒體模型
 */
class MediaModel extends Model {
	/** @var string table名稱 */
	public $table = 'upload';

	/** @var array 預設欄位 */
	protected $filed = [
		'uploadId' , 'fileName' , 'originName' , 'type' , 'extension' , 'size' , 'height' , 'width' , 'folder' , 'createTime'
	];

	/** @var array 關連欄位 */
	public $relateWith = [];

	/** @var string primary 主鍵 */
	private $primaryKey = 'uploadId';

	/**
	 * Lists 取多媒體列表
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 * @return mixed
	 */
	public function lists( $data = array() ) {
		$where = [];

		//有檔案類型
		if(isset($data['type'])) {
			$where[] = [$this->table . '.type' , '=' , $data['type']];
		}

		//有指定資料夾
		if(isset($data['folder'])) {
			$where[] = [$this->table . '.folder' , '=' , $data['folder']];
		}

		return $this->db->table($this->table)->select($this->filed)->where($where)->rows;
	}

	/**
	 * info 取多媒體資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param int $uploadId 上傳ID
	 * @return mixed
	 */
	public function info( $uploadId ) {
		return $this->db->table($this->table)->select($this->filed)->where(['uploadId' , '=' , $uploadId])->row;
	}

	/**
	 * store 新增多媒體資料
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
	 * update 更新多媒體資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $uploadId 上傳ID
	 * @param array $data 更新資料
	 * @return mixed
	 */
	public function update( $uploadId , $data ) {
		return $this->db->table($this->table)->update($data)->where(['uploadId' , '=' , $uploadId]);
	}

	/**
	 * delete 刪除多媒體資料
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
	 * getMedia 依Id取多媒體資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $uploadId
	 * @return mixed
	 */
	public function getMedia( $uploadId ) {
		//取多媒體資料
		if(!$media = $this->info($uploadId)) {
			return '';
		}

		//依據類型回傳自定義結果
		switch($media['type']) {
			case 'image':
				return IMAGE_URL . $media['folder'] . '/' . $media['extension'] .  '/' . $media['fileName'] . '.' . $media['extension'];
				break;
		}
	}

	/**
	 * getMediaFolder 取資料夾
	 *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param string $type
	 * @return mixed
	 */
	public function getMediaFolder( $type ) {
		return $this->db->table('uploadFolder')->select(['name' , 'code'])->where(['type' , '=' , $type])->rows;
	}

	/**
	 * getMediaFolderInfo 取指定資料夾
	 *
	 * @param string $type
	 * @param string $code
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return mixed
	 */
	public function getMediaFolderInfo( $type , $code ) {
		//指定條件
		$where = [
			['type' , '=' , $type],
			['code' , '=' , $code]
		];

		//回傳
		return $this->db->table('uploadFolder')->select(['name' , 'code'])->where($where)->row;
	}

    /**
     * storeFolder 新增資料夾
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param array $data
     * @return mixed
     */
    public function storeFolder( $data ) {
        return $this->db->table('uploadFolder')->insert($data);
    }
	//E == 客製化區塊 ========================================//
}
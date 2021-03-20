<?php
/**
 * Model
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2019/06/22
 * @since 0.1.0 2019/06/22
 */

use Libraries\DB as DB;
use Libraries\Pagination;

abstract class Model {
	/** @var DB $db 連線變數 */
	public $db;

	/** @var string table 資料表 */
	protected $table;

    /** @var array $casts 要轉換格式的欄位 */
    protected $casts;

	/** @var array pagination 分頁資料 */
	protected $pagination;

	/**
	 * model constructor. 建構子
     *
     * @since 0.0.1
     * @version 0.0.1
	 */
	public function __construct() {
		//啟用DB連線
		$this->db = DB::getInitialize();
	}

    /**
     * lists 取列表
     * @since 0.0.1
     * @version 0.0.1
     * @param $data
     * @return mixed
     */
	abstract protected function lists( $data );

    /**
     * info 取指定ID的資料
     * @since 0.0.1
     * @version 0.0.1
     * @param $id
     * @return mixed
     */
	abstract protected function info( $id );

    /**
     * store 存取資料
     * @since 0.0.1
     * @version 0.0.1
     * @param $data
     * @return mixed
     */
	abstract protected function store( $data );

    /**
     * update 更新指定Id的資料
     * @since 0.0.1
     * @version 0.0.1
     * @param $id
     * @param $data
     * @return mixed
     */
	abstract protected function update( $id , $data );

    /**
     * delete 刪除指定資料
     * @since 0.0.1
     * @version 0.0.1
     * @param $where
     * @return mixed
     */
	abstract protected function delete( $where );

	/**
	 * item 取單筆資料
	 *
     * @since 0.0.1
     * @version 0.0.1
	 * @param string $table 資料表
	 * @param string $field 欄位
	 * @param array $where WHERE 條件
	 * @return bool
	 */
	public function item( $table, $field , $where ) {
		return $this->db->table($table)->select($field)->where($where)->row;
	}

	/**
	 * item 取多筆資料
	 *
     * @since 0.0.1
     * @version 0.0.1
	 * @param string $table 資料表
	 * @param string $field 欄位
	 * @param array $where WHERE 條件
	 * @param int $count 要取的筆數
	 * @return bool
	 */
	public function items( $table, $field , $where , $count = 0 ) {
		if($count) {
            return $this->db->table($table)->select($field)->where($where , true)->limit($count)->rows;
        } else {
            return $this->db->table($table)->select($field)->where($where)->rows ;
        }
	}

	/**
	 * count 統計指定table的條件總數
	 *
     * @since 0.0.1
     * @version 0.0.1
	 * @param string $table 資料表
	 * @param string $field 欄位
	 * @param array $where WHERE 條件
     * @param boolean|string $groupBy
	 * @return bool
	 */
	public function count( $table , $field , $where , $groupBy = false) {
		return $groupBy ?
            (int)$this->db->table($table)->select($field)->where($where , true)->groupBy($groupBy)->count :
            (int)$this->db->table($table)->select("COUNT(" . $field . ") AS total")->where($where)->row['total'];
	}

	/**
	 * countWithJoin 統計多表JOIN後得條件總數
	 *
     * @since 0.0.1
     * @version 0.0.1
	 * @param string $field 欄位
	 * @param array $where WHERE 條件
	 * @param array $join JOIN資料
     * @param boolean|string $groupBy
	 * @return integer
	 */
	public function countWithJoin( $field , $where , $join = [] , $groupBy = false) {
		if(!isset($join['table'])) {
			return $groupBy ?
                (int)$this->db->table($this->table)->select($field)->joinWithMany($join)->where($where , true)->groupBy($groupBy)->count :
                (int)$this->db->table($this->table)->select("COUNT(" . $field . ") AS total")->joinWithMany($join)->where($where)->row['total'];
		} else {
			return $groupBy ?
                (int)$this->db->table($this->table)->select($field)->join($join['table'] , $join['joinKey'])->where($where , true)->groupBy($groupBy)->count :
                (int)$this->db->table($this->table)->select("COUNT(" . $field . ") AS total")->join($join['table'] , $join['joinKey'])->where($where)->row['total'];
		}
	}

	/**
	 * checkExist 檢查重覆
	 *
     * @since 0.0.1
     * @version 0.0.1
	 * @param $field
	 * @param $data
	 * @return bool
	 */
	public function checkExist( $field , $data ) {
		$result = $this->db->table($this->table)->select($field)->where([$field , '=' , $data]);
		if($result->count > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * makeMerge 將要JOIN得table組合
	 *
     * @since 0.0.1
     * @version 0.0.1
	 * @param array $filed
	 * @return array
	 */
	protected function makeMerge( $filed = array() ) {
		if(is_array($filed)) {
			return array_merge($this->filed , $filed);
		}

		return $this->filed;
	}

    /**
     * makeCast 將資料轉換成指定格式
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param array $data
     * @return array
     */
    protected function makeCast( $data = [] ) {
        //必須是陣列格式傳入
        if(!is_array($data)) {
            return $data;
        }

        //預設不為一維陣列
        $isOneArray = false;

        //一維陣列轉為二維陣列
        if(!isset($data[0]) || !is_array($data[0])) {
            $data = array($data);
            $isOneArray = true;
        }

        //開始配對
        foreach ($data as $key => $value) {
            //取model內設定的欄位與資料配對
            $matchs = array_intersect_key($this->casts, $value);

            //轉換指定格式
            foreach ($matchs as $field => $match) {
                switch ($match) {
                    case 'int':
                    case 'integer':
                        $data[$key][$field] = (int)$data[$key][$field];
                        break;
                    case 'string':
                        $data[$key][$field] = (string)$data[$key][$field];
                        break;
                    case 'double':
                        $data[$key][$field] = (double)$data[$key][$field];
                        break;
                    case 'array':
                    case 'json':
                        $data[$key][$field] = json_decode($data[$key][$field], true);
                        break;
                    case 'numberFormat':
                        $data[$key][$field] = number_format($data[$key][$field]);
                        break;
                    default:
                        break;
                }
            }
        }

        //回傳轉換好資料
        return ($isOneArray) ? $data[0] : $data;
    }

	/**
	 * makePagination 製做列表用的頁碼
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $primaryKey 資料表主鍵
	 * @param array $data 傳送參數
	 * @param array $where WHERE 條件
	 * @param array $join JOIN 條件
	 * @param boolean|string $groupBy groupBy 條件
	 */
	protected function makePagination( $primaryKey, $data , $where , $join = array() , $groupBy = false) {
		//宣告頁碼
		$this->pagination = new Pagination($data);

		//計算資料總數 先判定是否有JOIN
        if($join) {
            $this->pagination->total = $this->countWithJoin($primaryKey , $where , $join , $groupBy);
        } else {
            $this->pagination->total = $this->count($this->table , $primaryKey , $where ,$groupBy);
        }
	}

	/**
	 * getPagination 回傳頁碼資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return array
	 */
	public function getPagination() {
		return $this->pagination->output();
	}
}
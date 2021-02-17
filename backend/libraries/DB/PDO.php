<?php
/**
 * PDO
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2019/06/21
 * @since 0.1.0 2019/06/21
 */
namespace DB;

use Libraries\PublicFunction;

final class PDO {
	/** @var object 連線變數 */
	private $connection = null;

	/** @var object 連線變數 */
	private $statement = null;

	/** @var string 連線的table */
	private $table;

	/** @var string sql的類型 */
	private $type;

	/** @var string sql語法 */
	private $sql;

	/** @var string errorCode */
	public $errorCode;

	/** @var string errorMsg */
	public $errorMsg;

    /** @var array 傳送參數 */
    public $params = [];


	/**
	 * PDO constructor.
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param $hostname
	 * @param $username
	 * @param $password
	 * @param $database
	 * @param string $port
	 * @throws \Exception
	 */
	public function __construct( $hostname , $username , $password , $database , $port = '3306' ) {
		try {
			$this->connection = new \PDO("mysql:host=" . $hostname . ";port=" . $port . ";dbname=" . $database , $username , $password , array(\PDO::ATTR_PERSISTENT => true));
		} catch(\PDOException $e) {
			throw new \Exception('Failed to connect to database. Reason: \'' . $e->getMessage() . '\'');
		}

		$this->connection->exec("SET NAMES 'utf8'");
		$this->connection->exec("SET CHARACTER SET utf8");
		$this->connection->exec("SET CHARACTER_SET_CONNECTION=utf8");
		$this->connection->exec("SET SQL_MODE = ''");
	}

	/**
	 * prepare
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param $sql
	 */
	public function prepare( $sql ) {
		$this->statement = $this->connection->prepare($sql);
	}

	/**
	 * bindParam
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param $parameter
	 * @param $variable
	 * @param int $data_type
	 * @param int $length
	 */
	public function bindParam( $parameter , $variable , $data_type = \PDO::PARAM_STR , $length = 0 ) {
		if($length) {
			$this->statement->bindParam($parameter , $variable , $data_type , $length);
		} else {
			$this->statement->bindParam($parameter , $variable , $data_type);
		}
	}

	/**
	 * execute
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @throws \Exception
	 */
	public function execute() {
		try {
			if($this->statement && $this->statement->execute()) {
				$data = array();

				while($row = $this->statement->fetch(\PDO::FETCH_ASSOC)) {
					$data[] = $row;
				}

				$result = new \stdClass();
				$result->row = (isset($data[0])) ? $data[0] : array();
				$result->rows = $data;
				$result->count = $this->statement->rowCount();
			}
		} catch(\PDOException $e) {
			throw new \Exception('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode());
		}
	}

	/**
	 * @param $sql 連線語句
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $params
	 * @return bool|\stdClass
	 * @throws \Exception
	 */
	public function query( $sql , $params = array() ) {
		$this->statement = $this->connection->prepare($sql);

		$result = false;

		try {
			if($this->statement && $this->statement->execute($params)) {
				$data = array();

				while($row = $this->statement->fetch(\PDO::FETCH_ASSOC)) {
					$data[] = $row;
				}

				$result = new \stdClass();
				$result->row = (isset($data[0]) ? $data[0] : array());
				$result->rows = $data;
				$result->count = $this->statement->rowCount();
			} else {
			    //導向錯誤
			    PublicFunction::error('0100', "SQL Error : " . $this->statement->errorInfo()[2]);
			}
		} catch(\PDOException $e) {
			throw new \Exception('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode() . ' <br />' . $sql);
		}

		//還原預設
		$this->clear();

		if($result) {
			return $result;
		} else {
			$result = new \stdClass();
			$result->row = array();
			$result->rows = array();
			$result->count = 0;
			return $result;
		}
	}

	/**
	 * escape 過律特殊字
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param $value
	 * @return mixed
	 */
	public function escape( $value ) {
		return str_replace(array("\\" , "\0" , "\n" , "\r" , "\x1a" , "'" , '"') , array("\\\\" , "\\0" , "\\n" , "\\r" , "\Z" , "\'" , '\"') , $value);
	}

	/**
	 * countAffected
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return int
	 */
	public function countAffected() {
		if($this->statement) {
			return $this->statement->rowCount();
		} else {
			return 0;
		}
	}

	/**
	 * getLastId 取新增厚的ID
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return string
	 */
	public function getLastId() {
		return $this->connection->lastInsertId();
	}

	/**
	 * isConnected 是否連線
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return bool
	 */
	public function isConnected() {
		if($this->connection) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * table 指定的Table
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return $this
	 */
	public function table( $table ) {
		$this->table = $table;
		return $this;
	}

	//Region SQL 實作邏輯 ============================================================/
	/**
	 * select 組成 SELECT 得語法
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return $this
	 */
	public function select( $field ) {
		//判斷欄圍是陣列還是字串格式
		if(is_array($field)) {
			//組成要搜尋的欄位
			foreach($field as $key => $item) {
				//若有指定前面的table 則不加上 EX:user.name
				if((substr_count($item , '.')) || (substr_count($item , 'COUNT '))) {
					continue;
				}

				//存進欄位陣列
				$field[$key] = $this->table . '.' . $item . ' ';
			}

			//每個欄位用逗號分隔
			$field = implode(',' , $field);
		} else {
			//計算COUNT在字符串中出現的次數
			if(!substr_count($field , 'COUNT')) {
				$field = $this->table . '.' . $field . ' ';
			}
		}

		//組成SQL
		$this->type = 'select';
		$this->sql = "SELECT " . $field . " FROM " . $this->table . "";

		//回傳
		return $this;
	}

	/**
	 * insert 組成 INSERT 得寫法
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return boolean
	 */
	public function insert( $data ) {
		$this->type = 'insert';
		$this->sql = "INSERT $this->table (%s) VALUES (%s)";

		$field = array();
		if(count($data) == 0 || !is_array($data)) {
			return false;
		}

		foreach($data as $key => $value) {
			$field[] = $key;
			$sentData[$key] = $value;
		}

		$this->sql = sprintf($this->sql , join(',' , $field) , ':' . join(',:' , $field));

		return $this->query($this->sql ,$sentData);
	}

	/**
	 * update 組成 UPDATE 得寫法
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return $this
	 */
	public function update( $data ) {
		$this->type = 'update';
		$sql = "UPDATE $this->table SET %s ";
		$temp = [];
		if(count($data) == 0 || !is_array($data)) {
			return false;
		}

		foreach($data as $key => $value) {
			$temp[] = $key . " = :" . $key;
            $this->params[$key] = $value;
		}

		$this->sql = sprintf($sql , join(', ' , $temp));
		return $this;
	}

	/**
	 * upsert 組成 INSERT UPDATE 得寫法 資料存在就更新，反之新增一筆
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data 要新增的資料
	 * @param array $update 要更新的資料
	 * @return $this
	 */
	public function upsert( $data , $update ) {
		$this->type = 'insert';
		$sql = "INSERT INTO $this->table (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s";
		$temp = [];
		$updateTemp = [];
		$sentData = [];

		if(count($data) == 0 || !is_array($data)) {
			return false;
		}

		//insert
		foreach($data as $key => $value) {
			$temp[] = $key;
			$sentData[$key] = $value;
		}

		//update
		foreach($update as $key => $value) {
			$updateTemp[] = $key . " = '" . $value . "'";
		}

		$this->sql = sprintf($sql , join(',' , $temp) , '"' . join('","' , $sentData) . '"' , join(',' , $updateTemp));
		return $this->query($this->sql);
	}

	/**
	 * delete 組成 DELETE 得寫法
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return $this
	 */
	public function delete() {
		$this->type = 'delete';
		$this->sql = "DELETE FROM $this->table";
		return $this;
	}

	/**
	 * where 組成 WHERE 得寫法
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return $this
	 */
	public function where( $where = [] , $next = false ) {
		//判定傳入格式
		if(!empty($where)) {
			if(is_array($where[0])) {
				//宣告WHERE SQL
				$sql = " WHERE ";

				//組成sql字串
				foreach($where as $key => $param) {
				    //IN 不需要加刮號 @todo 之後要根據 判斷式做調整
				    if(in_array( strtoupper($param[1]), ['IN', 'NOT IN'] )) {
                        $sql .= $param[0] . " " . $param[1] . " "  . $this->escape($param[2]);
                    } else {
                        $sql .= $param[0] . " " . $param[1] . " " . "'" . $this->escape($param[2]) . "'";
                    }

					//不是最後一筆要持續加上AND
					if($key !== array_key_last($where)) {
						$sql .= " AND ";
					}
				}
			} else {
                //IN 不需要加刮號
                if(in_array( strtoupper($where[1]) , ['IN', 'NOT IN'] )) {
                    $sql = " WHERE " . $where[0] . " " . $where[1] . " " . $this->escape($where[2]);
                } else {
                    $sql = " WHERE " . $where[0] . " " . $where[1] . " " . "'" . $this->escape($where[2]) . "'";
                }
			}
		} else {
			$sql = '';
		}

		//判訂SQL 得方法
		switch($this->type) {
			//SELECT
			case 'select':
				$this->sql .= $sql;
				if($next) {
					return $this;
				}//要進行orderBy or Limit
				break;

			//UPDATE,DELETE
			case 'update':
			case 'delete':
				$this->sql .= $sql;
				break;

			//INSERT
			default:
			case 'insert':
				return false;
				break;
		}

		//回傳結果
		return $this->query($this->sql , $this->params);
	}

	/**
	 * orderBy 組成 ORDER BY 得寫法
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return $this
	 */
	public function orderBy( $field = '' , $type = 'ASC' , $next = false ) {
		$this->sql .= " ORDER BY " . $this->escape($field) . " " . $type;

		if($next) {
			return $this;
		} else {
			//回傳結果
			return $this->query($this->sql);
		}
	}

    /**
     * groupBy 組成 GROUP BY 得寫法
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param string|array $field
     * @return $this
     */
    public function groupBy( $field = '' , $next = false ) {
        $this->sql .= " GROUP BY " . $this->escape($field);

        if($next) {
            return $this;
        } else {
            //回傳結果
            return $this->query($this->sql);
        }
    }

	/**
	 * limit 組成 LIMIT 得寫法
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return $this
	 */
	public function limit( $start , $end = 0 ) {
		$this->sql .= " LIMIT " . $start;
		if($end > 0) {
			$this->sql .= ", " . $end;
		}
		return $this->query($this->sql);
	}

	/**
	 * join 組成JOIN得寫法
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return $this
	 */
	public function join( $table , $joinKey ) {
		$this->sql .= " JOIN " . $table . " USING (" . $joinKey . ")";
		return $this;
	}

	/**
	 * joinWithMany 多表JOIN
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return $this
	 */
	public function joinWithMany( $joins ) {
		foreach($joins as $key => $join) {
			$this->sql .= " JOIN " . $join['table'] . " USING (" . $join['joinKey'] . ")";
		}
		return $this;
	}

    /**
     * left join 組成LEFT JOIN得寫法
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return $this
     */
    public function leftJoin( $table , $joinKey , $relateData ) {
        $this->sql .= " LEFT JOIN " . $relateData['table'] . " ON ($table.$joinKey = " . $relateData['table'] . "." . $relateData['joinKey'] . ")";

        return $this;
    }

	/**
	 * right join 組成RIGHT JOIN得寫法
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return $this
	 */
	public function rightJoin( $table , $joinKey , $relateData ) {
		$this->sql .= " RIGHT JOIN " . $relateData['table'] . " ON ($table.$joinKey = " . $relateData['table'] . "." . $relateData['joinKey'] . ")";

		return $this;
	}

	//EndRegion SQL 實作邏輯 ============================================================/

    /**
     * transaction 交易語法
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $sqls
     * @return bool
     */
	public function transaction ( $sqls ) {
        try {
            $this->connection->beginTransaction();

            foreach ($sqls as $key => $sql) {
                if(!$this->connection->exec($sql)) {
                    throw new \PDOException('SQL Error : ' . $sql);
                }
            }

            $this->connection->commit();
        } catch ( \PDOException $e ) {
            $this->connection->rollBack();
            PublicFunction::error('0101', 'transaction SQL Error: ' . $e->getMessage());
            return false;
        }

        return true;
    }

	/**
	 * beginTransaction 宣告交易語法啟用
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return bool
	 */
    public function beginTransaction() {
		return $this->connection->beginTransaction();
    }

	/**
	 * commit
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return bool
	 */
	public function commit() {
		return $this->connection->commit();
	}

	/**
	 * rollBack
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return bool
	 */
	public function rollBack() {
		return $this->connection->rollBack();
	}

	/**
	 * getSql 取sql
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return $this
	 */
	public function getSql() {
		return $this->sql;
	}

	/**
	 * __destruct 解構子
	 * @since 0.0.1
	 * @version 0.0.1
	 * __destruct
	 */
	public function __destruct() {
		$this->connection = null;
	}

	/**
	 * clear 清除資料還原預設
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	private function clear() {
		$this->params = [];
	}
}

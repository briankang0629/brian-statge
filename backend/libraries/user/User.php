<?php
/**
 * Libraries User
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/10/16
 * @since 0.1.0 2020/10/16
 */

namespace Libraries;

use Libraries\PublicFunction as publicFunction;
use Libraries\DB as DB;
use Libraries\Request as request;

class User {
    public $identity = 'user';
	private $userId;
	private $name;
	private $account;
	private $level;
	private $email;
	private $lastLoginTime;
	private $uuid;
	private $createTime;
	private $updateTime;

	/**
	 * Controller constructor.
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	public function __construct($register) {
		$this->db = $register['db'];
		$this->request = $register['request'];
	}

	/**
	 * login 登入功能
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param string $account 帳號
	 * @param string $password 密碼
	 * @param string $token token
	 * @return boolean
	 */
	public function login($account, $password, $token = '') {
		//檢查有無此帳號
		$sql = "SELECT * FROM user WHERE account = '" . $this->db->escape($account) . "'";
		$result = $this->db->query($sql);
		//無此帳號
		if (!isset($result->row['account'])) {
			$this->errorCode = '0000';
			return false;
		}
		//檢查帳號狀態
		if ($result->row['status'] !== 'Y') {
			$this->errorCode = '0001';
			return false;
		}

        //密碼解密
        $decodePassword = PublicFunction::decode($result->row['password'] , $result->row['secretKey']);

        if($password != $decodePassword) {
            PublicFunction::error('0002');
        }

        $loginTime = date('Y-m-d H:i:s');//@todo
        $this->userId = $result->row['userId'];
        $this->name = $result->row['name'];
        $this->account = $result->row['account'];
        $this->email = $result->row['email'];
        $this->level = $result->row['level'];
        $this->ip = $result->row['ip'];
        $this->createTime = $result->row['createTime'];
        $this->updateTime = $result->row['updateTime'];
        $this->lastLoginTime = $loginTime;

        //更新會員登入紀錄
        $this->db->query("UPDATE user SET lastLoginTime = '" . $loginTime . "', ip = '" . $this->db->escape(request::$server['REMOTE_ADDR']) . "' WHERE userId = '" . (int)$this->userId . "'");

        //token 登入記錄一筆
        $this->db->query("INSERT INTO userToken SET userId = " . (int)$this->userId . ", token = '" . $this->db->escape($token) . "', ip = '" . $this->db->escape(request::$server['REMOTE_ADDR']) . "', createTime = '" . $loginTime . "', status = 'Y'");
        return true;

	}

	/**
	 * auth 驗證登入功能
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param string $token token
	 * @return boolean
	 */
	public function auth($token) {
		$token = $this->db->escape($token);
		//清除超過三個月的登入紀錄@todo
		if ((isset($token)) && !empty($token)) {
			$user = $this->db->query("SELECT utk.updateTime AS updateTime, u.account , u.name FROM userToken utk LEFT JOIN user u ON (utk.userId = u.userId) WHERE utk.token = '" . $token . "' AND utk.status = 'Y'");

			//判定有無資料
			if ($user->count) {
				//驗證1小時閒置 逾時登出
				$now_time = date('Y-m-d H:i:s');
				if (strtotime($now_time) - strtotime($user->row['updateTime']) < 3600) {
					//更新現在時間
					$this->db->query("UPDATE userToken SET updateTime = '" . $now_time . "' WHERE token = '" . $token . "'");
					return $user->row;
				} else {
					//將該token狀態設為N
					$this->db->query("UPDATE userToken SET status = 'N' WHERE token = '" . $token . "'");
					$this->errorCode = '0004';
					return false;
				}
			} else {
				//執行登出
				$this->logout();
				//帶入錯誤碼
				$this->errorCode = '0003';
				return false;
			}
		}
	}

	//登出
	public function logout() {
		//清除cookies uid
		setcookie('uid', '');
	}

	public function isLogged() {
		return $this->userId;
	}

	public function getId() {
		return $this->userId;
	}

	public function getGroupId() {
		return $this->userGroupId;
	}

	public function getEmail() {
		return $this->email;
	}
}

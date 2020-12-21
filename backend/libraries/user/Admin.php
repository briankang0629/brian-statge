<?php
/**
 * Libraries Admin
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2019/12/08
 * @since 0.1.0 2019/12/08
 */

namespace Libraries;

use Libraries\PublicFunction as publicFunction;
use Libraries\DB as DB;
use Libraries\Request as request;

class Admin {
	/** @var string $identity 身分 */
    public $identity = 'admin';

	/** @var object $db 連線變數 */
	public $db;

	/** @var int $adminId 管理者ID */
    private $adminId;

	/** @var int $adminId 管理者ID */
    private $name;

	/** @var string $account 管理者帳號 */
    private $account;

	/** @var int $level 管理者等級 */
    private $level;

	/** @var string $email 管理者email */
    private $email;

	/** @var array $permission 管理者權限 */
    private $permission;

	/** @var string $sub 管理者email */
    private $sub;

    /**
     * Controller constructor.
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function __construct() {
	    //啟用DB連線
	    $this->db = DB::getInitialize();
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
        $sql = "SELECT * FROM admin WHERE account = '" . $this->db->escape($account) . "'";
        $result = $this->db->query($sql);

        //無此帳號
        if (!isset($result->row['account'])) {
            PublicFunction::error('0000');
        }
        //檢查帳號狀態
        if ($result->row['status'] !== 'Y') {
            PublicFunction::error('0001');
        }

        //密碼解密
        $decodePassword = PublicFunction::decode($result->row['password'] , $result->row['secretKey']);

        if($password != $decodePassword) {
            PublicFunction::error('0002');
        }

        $loginTime = date('Y-m-d H:i:s');//@todo
        $this->adminId = $result->row['adminId'];

        //更新會員登入紀錄
        $this->db->query("UPDATE admin SET lastLoginTime = '" . $loginTime . "', ip = '" . $this->db->escape(request::$server['REMOTE_ADDR']) . "' WHERE adminId = '" . (int)$this->adminId . "'");

        //token 登入記錄一筆
        $this->db->query("INSERT INTO adminToken SET adminId = " . (int)$this->adminId . ", token = '" . $this->db->escape($token) . "', ip = '" . $this->db->escape(request::$server['REMOTE_ADDR']) . "', createTime = '" . $loginTime . "', status = 'Y'");
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
	        $admin = $this->db->query("SELECT atk.updateTime AS updateTime, a.account, a.name, a.adminId, a.email, a.level, a.picture, a.permissionId, a.sub FROM adminToken atk LEFT JOIN admin a ON (atk.adminId = a.adminId) WHERE atk.token = '" . $token . "' AND atk.status = 'Y'");

            //判定有無資料
            if ($admin->count) {
                //驗證3小時閒置 逾時登出
                $now_time = date('Y-m-d H:i:s');
                if (strtotime($now_time) - strtotime($admin->row['updateTime']) < 86400) {
                    //更新現在時間
                    $this->db->query("UPDATE adminToken SET updateTime = '" . $now_time . "' WHERE token = '" . $token . "'");

                    //存放變數
                    $this->adminId = $admin->row['adminId'];
                    $this->name = $admin->row['name'];
                    $this->account = $admin->row['account'];
                    $this->email = $admin->row['email'];
                    $this->level = $admin->row['level'];
                    $this->sub = $admin->row['sub'];

                    //取的管理者權限
                    if($admin->row['permissionId']) {
                        $permission = $this->db->query("SELECT permission FROM permission WHERE permissionId = '" . $admin->row['permissionId'] . "'");
                        $this->permission = json_decode($permission->row['permission'], true);
                    } else {
                        //若為主帳號 所有權限開放
                        $this->permission = [];
                        foreach (publicFunction::getSystemCode()['permission'] as $menu => $data) {
                            if($data) {
                                foreach ($data as $subMenu => $item) {
                                    $this->permission[$menu][$subMenu] = 'E';
                                }
                            }
                        }
                    }

                    //回傳
	                return $admin->row;
                } else {
                    //將該token狀態設為N
                    $this->db->query("UPDATE adminToken SET status = 'N' WHERE token = '" . $token . "'");
                    PublicFunction::error('0004');
                    return false;
                }
            } else {
                //帶入錯誤碼
                PublicFunction::error('0003');
                return false;
            }
        }

    }

    /**
     * getName 取得名稱
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * getId 取得admin ID
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @return integer
     */
    public function getId() {
        return $this->adminId;
    }

    /**
     * getEmail 取得email
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * getAccount 取得帳號
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @return string
     */
    public function getAccount() {
        return $this->account;
    }

	/**
	 * getSub 取得是否為員工帳號
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return string
	 */
	public function getSub() {
		return $this->sub;
	}

	/**
	 * getPermission 取得權限
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return array
	 */
	public function getPermission() {
		return $this->permission;
	}
}

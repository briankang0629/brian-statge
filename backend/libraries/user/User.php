<?php
/**
 * Libraries User
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2021/03/07
 * @since 0.1.0 2021/03/07
 */

namespace Libraries;

use Libraries\PublicFunction as publicFunction;
use Libraries\DB as DB;
use Libraries\Request as request;

class User {
    /** @var string $identity 身分 */
    public $identity = 'user';

    /** @var object $db 連線變數 */
    public $db;

    /** @var int $userId 管理者ID */
    private $userId;

    /** @var int $userId 管理者ID */
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
        $sql = "SELECT * FROM user WHERE account = '" . $this->db->escape($account) . "'";
        $result = $this->db->query($sql);

        //無此帳號
        if (!isset($result->row['account'])) {
            PublicFunction::error('0050');
        }
        //檢查帳號狀態
        if ($result->row['status'] !== 'Y') {
            PublicFunction::error('0051');
        }

        //密碼解密
        $decodePassword = PublicFunction::decode($result->row['password'] , $result->row['secretKey']);

        if($password != $decodePassword) {
            PublicFunction::error('0052');
        }

        $loginTime = date('Y-m-d H:i:s');//@todo
        $this->userId = $result->row['userId'];

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
            $user = $this->db->query("SELECT utk.updateTime AS updateTime, u.account, u.name, u.userId, u.email, u.picture FROM userToken utk JOIN user u USING(userId) WHERE utk.token = '" . $token . "' AND utk.status = 'Y'");

            //判定有無資料
            if ($user->count) {
                //驗證3小時閒置 逾時登出
                $now_time = date('Y-m-d H:i:s');
                if (strtotime($now_time) - strtotime($user->row['updateTime']) < 86400) {
                    //更新現在時間
                    $this->db->query("UPDATE userToken SET updateTime = '" . $now_time . "' WHERE token = '" . $token . "'");

                    //存放變數
                    $this->userId = $user->row['userId'];
                    $this->name = $user->row['name'];
                    $this->account = $user->row['account'];
                    $this->email = $user->row['email'];

                    //回傳
                    return $user->row;
                } else {
                    //將該token狀態設為N
                    $this->db->query("UPDATE userToken SET status = 'N' WHERE token = '" . $token . "'");
                    PublicFunction::error('0054');
                    return false;
                }
            } else {
                //帶入錯誤碼
                PublicFunction::error('0053');
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
     * getId 取得user ID
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return integer
     */
    public function getId() {
        return $this->userId;
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

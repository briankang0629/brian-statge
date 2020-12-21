<?php
/**
 * Libraries Google 服務
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/11/02
 * @since 0.1.0 2019/11/02
 */

namespace Libraries;

use Google_Service_Oauth2;
use Libraries\Request as request;
use Libraries\DB as DB;

class Google {

	/** @var object $client 連線變數 */
	private $client;

	/** @var object $db 資料庫連線變數 */
	private $db;

	/** @var object $request Request請求變數 */
	private $request;

	/** @var boolean $active google模組功能是否有開放*/
	public $active = false;

	/** @var string $errorCode 錯誤馬*/
	public $errorCode;

    /**
     * Controller constructor.
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function __construct() {
        $this->db = DB::getInitialize();
        $this->client = new \Google_Client();
        $this->active = true;//之後需結合模組開關@todo
    }

    /**
     * login 登入功能
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param string $token token
     * @return boolean
     */
    public function login() {
    	//讀取設定擋
	    $this->client->setAuthConfig(BASE_PATH . '/storage/google/credentials.json');
	    // offline access
	    $this->client->setAccessType('offline');
	    // incremental auth
	    $this->client->setIncludeGrantedScopes(true);
	    //需要授權取得的資源
	    $this->client->addScope([Google_Service_Oauth2::USERINFO_EMAIL, Google_Service_Oauth2::USERINFO_PROFILE]);
	    // 寫憑證設定：「已授權的重新導向 URI 」的網址 @todo
//	    $this->client->setRedirectUri('http://localhost:8080/auth/google');

	    return $this->client->createAuthUrl();
    }

	/**
	 * auth oauth 取Google資料驗證身分
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param string $token token
	 * @return array|boolean
	 */
	public function auth( $token ) {
		if(isset(request::$get['code'])) {
			//S ====== Google 取資料實作

			try {
				//讀取設定擋
				$this->client->setAuthConfig(BASE_PATH . '/storage/google/credentials.json');
				//驗證使用者登入後 redirect 過來附帶的 code
				$this->client->authenticate(request::$get['code']);
				// 使用 Service 去取得使用者資訊以及 email
				$service = new Google_Service_Oauth2($this->client);
				$googleUser = $service->userinfo->get();

				//google 取回的資料
				$user['account'] = (string)$googleUser->id;
				$user['email'] = $googleUser->email;
				$user['name'] = $googleUser->name;
				$user['picture'] = $googleUser->picture;

			} catch(\Exception $e) {
				$this->errorCode = '0304';
				return false;
			}

            //E ====== Google 取資料實作

            //S ====== 內部流程

			//取有無使用者
			$userData = $this->getUser($user);

            $loginTime = date('Y-m-d H:i:s');
			if(isset($userData['account'])) {
				//未啟用
				if($userData['status'] != 'Y') {
					$this->errorCode = '0302';
					return false;
				}

				//更新會員登入紀錄
				$this->db->query("UPDATE admin SET lastLoginTime = '" . $loginTime . "', ip = '" . $this->db->escape(request::$server['REMOTE_ADDR']) . "' WHERE adminId = '" . (int)$userData['adminId'] . "'");

			} else {
				if(!$userData['adminId'] = $this->register($user)) {
					$this->errorCode = '0303';
					return false;
				}
			}

            //token 登入記錄一筆
            $insert = [
                'adminId' => (int)$userData['adminId'],
                'token' => $this->db->escape($token),
                'ip' => $this->db->escape(request::$server['REMOTE_ADDR']),
                'createTime' => $loginTime,
                'status' => 'Y',
            ];

            $this->db->table('adminToken')->insert($insert);

            //E ====== 內部流程

            //回傳
			return [
                'Authorization' => $token,
				'account' => $user['account'],
				'name' => $user['name']
			];

		} else {
			$this->errorCode = '0301';
			return false;
		}
	}

	/**
	 * getUser 取用戶資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param $user
	 * @return mixed
	 */
	private function getUser( $user ) {
		return $this->db->table(Identity)->select(['adminId', 'account', 'status'])->where(['account' , '=', $user['account']])->row;
	}

	/**
	 * register 用google資料註冊用戶資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param $user
	 * @return mixed
	 */
	private function register( $user ) {
		$data = [
			'name' => $user['name'],
			'account' => $user['account'],
			'password' => '',
			'email' => $user['email'],
			'picture' => $user['picture'],
			'type' => 'google',
			'ip' => $this->db->escape(request::$server['REMOTE_ADDR']),
			'createTime' => date('Y-m-d H:i:s'),
			'status' => 'Y',
		];

        $this->db->table(Identity)->insert($data);

		return $this->db->getLastId();
	}
}

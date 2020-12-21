<?php
/**
 * LoginController 登入控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2019/11/23
 */

use Libraries\PublicFunction as publicFunction;
use Libraries\Validator as validator;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】登入控制器
 */
class LoginController extends Controller
{

	//----------------------------------------------------------------
    //Region API
    //----------------------------------------------------------------

    /**
     * login 登入功能
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return string json
     */
    public function login() {
        if ((isset(request::$post['account'])) && (isset(request::$post['password']))) {
            $response = [];
            //帳號密碼
            $account = request::$post['account'];
            $password = request::$post['password'];
            $token = publicFunction::token();

            //執行登入
            $this->{Identity}->login($account, $password, $token);

            $response['status'] = 'success';
            $response['msg'] = language::getFile()['common']['login']['loginSuccess'];
            $response['token'] = $token;

            //驗證
	        $this->{Identity}->auth($token);

	        //操作記錄
	        if(Identity == 'admin') {
		        define('AdminID', $this->admin->getId());
		        define('Account', $this->admin->getAccount());
		        $this->writeLog(10 , request::$post , $this->admin->db->getSql());

		        //回傳權限資料
                $response['permission'] = $this->admin->getPermission();
	        } else {
		        define('UserID', $this->user->getId());
		        define('Account', $this->user->getAccount());
		        $this->writeLog(11 , request::$post , $this->user->db->getSql());
	        }

        } else {
            publicFunction::error('0005');
        }
        //json 轉出
        publicFunction::json($response);
    }

    /**
     * authenticate 驗證登入
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @return string
     */
    public function authenticate() {
        //檢查header是否有 Authorization
        $headerData = getallheaders();
        if (empty($headerData['Authorization'])) {
            publicFunction::error('0003');
        }

        //驗證
        $auth = $this->{Identity}->auth($headerData['Authorization']);

        $response['status'] = 'success';
        $response['account'] = $auth['account'];
        $response['name'] = $auth['name'];
        $response['token'] = $headerData['Authorization'];

        if(Identity == 'admin') {
            $response['picture'] = $auth['picture'];
        }

        //導出json
        publicFunction::json($response);
    }

    //S ==== Google 登入 ====================

	/**
	 * loginGoogle Google登入功能
	 *
	 * @since 0.1.0
	 * @return string json
	 * @todo 操作記錄
	 */
	public function loginGoogle() {
		$googleService = new Libraries\Google();
		//是否有啟用模組
		if(!$googleService->active) {
			publicFunction::error('0300');
		}

		//導出登入連結
		publicFunction::json([
			'status' => 'success',
			'url' => $googleService->login()
		]);
	}

	/**
	 * authenticateGoogle Google驗證登入
	 *
	 * @since 0.1.0
	 * @return json
	 */
	public function authenticateGoogle() {
		$googleService = new Libraries\Google();
		//是否有啟用模組
		if(!$googleService->active) {
			publicFunction::error('0300');
		}

		if(!$response = $googleService->auth(publicFunction::token())) {
            //執行登出
            $this->logout();
			publicFunction::error($googleService->errorCode);
		}

		//導出登入token
		publicFunction::json([
			'status' => 'success',
			'account' => $response['account'],
			'token' => $response['Authorization'],
		]);

	}

	//E ==== Google 登入 ====================

    /**
     * logout 登出
     * @return array
     */
    public function logout() {
        //將該token狀態設為N
        $sentData['status'] = 'N';

        //沒有驗證直接登出
        if(!getallheaders()['Authorization']) {
	        publicFunction::json([
		        'status' => 'success',
		        'msg' => language::getFile()['common']['login']['logoutSuccess']
	        ]);
        }

	    //先驗證
	    $this->{Identity}->auth(getallheaders()['Authorization']);
        if(Identity == 'admin') {
	        define('AdminID', $this->admin->getId());
	        define('Account', $this->admin->getAccount());
        } else {
	        define('UserID', $this->user->getId());
	        define('Account', $this->user->getAccount());
        }

        //執行登出
	    $this->{Identity}->db->table(Identity . 'Token')->update($sentData)->where(['token' , '=' , getallheaders()['Authorization']]);

	    //操作記錄
	    if(Identity == 'admin') {
		    $this->writeLog(12 , $sentData , $this->{Identity}->db->getSql());
	    } else {
		    $this->writeLog(13 , $sentData , $this->{Identity}->db->getSql());
	    }

	    //回傳
        publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['login']['logoutSuccess']
        ]);
    }

    //----------------------------------------------------------------
    //EndRegion API
    //----------------------------------------------------------------

}
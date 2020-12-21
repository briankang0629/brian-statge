<?php
/**
 * AuthMiddleware 驗證登入中介層
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/11/17
 * @since 0.1.0 2020/11/17
 */

use Libraries\Language as language;

/**
 * 【Middleware】驗證登入中介層
 */
class AuthMiddleware extends Middleware {
    /** @var array exclude 不執行的接口 */
    public $exclude = [
        '/api/auth',
        '/api/auth/google',
        '/api/login',
        '/api/login/google',
        '/api/logout',
    ];

	/** @var array include 執行的接口 */
	public $include = [];

    /**
     * handle 處理中介層的請求
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $router
     */
	public function handle($router) {
        //request header
        $requestHeaders = getallheaders();

		//判斷不驗證的網址
        foreach ($this->exclude as $route) {
            $route = preg_replace('/\/{(.*?)}/', '/(.*?)', $route);
            if (preg_match_all('#^' . $route . '$#', $router->getCurrentUri(), $matches, PREG_OFFSET_CAPTURE)) {
                return true;
            }
        }

        //登入驗證
        if ( !isset($requestHeaders['Authorization'])) {
            header('HTTP/1.0 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            $response = [
                'status'    => 'error',
                'errorCode' => '0003',
                'msg'       => language::getFile()['error']['0003'],
            ];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        //根據子網域判定請求位置
        switch (explode('.',$requestHeaders['Request-Host'])[0]) {
            case 'admin':
                $auth = new \Libraries\Admin();
                break;
            default:
                $auth = new \Libraries\User();
                break;
        }

        //登入狀況
        if (!$auth->auth($requestHeaders['Authorization'])) {
            header('HTTP/1.0 401 Unauthorized');
            header('Content-Type: application/json; charset=utf-8');
            $response = [
                'status'    => 'error',
                'errorCode' => '0003',
                'msg'       => language::getFile()['error']['0003'],
            ];
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit;
        }

        //通過驗證 存放變數 @todo -> middleware
        if($auth->identity == 'admin') {
	        //定義AdminID
            define('AdminID', $auth->getId());

	        //定義AdminID
            define('Permission', $auth->getPermission());

	        //定義AdminID
            define('IsSub', $auth->getSub());
        } else {
	        //定義UserID
            define('UserID', $auth->getId());
        }

        //定義名稱
        define('Name', $auth->getName());

		//定義帳號
        define('Account', $auth->getAccount());

		//定義email
        define('Email', $auth->getEmail());
    }

}

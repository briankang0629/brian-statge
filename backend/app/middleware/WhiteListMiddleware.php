<?php
/**
 * WhiteListMiddleware 白名單中介層
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/11/18
 * @since 0.1.0 2020/11/18
 */

use Libraries\PublicFunction as publicFunction;
/**
 * 【Middleware】白名單中介層
 */
class WhiteListMiddleware extends Middleware {
    /** @var array exclude 不執行的接口 */
    public $exclude = [];

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

        //考慮之後加入middleware CORS @todo 白名單
        if (isset($requestHeaders['Request-Host'])) {
            header('Access-Control-Allow-Origin: ' . $requestHeaders['Request-Host']);
            // 響應型別
            header('Access-Control-Allow-Methods:POST, GET, OPTIONS, DELETE, PUT');
            // 響應頭設定
            header('Access-Control-Allow-Headers:Origin, Methods, Content-Type, Authorization, Accept');
        }


		//根據Domain位置 判定要起用哪隻library @todo 未來加上系統端口
		$domainArray = explode('.', getallheaders()['Request-Host']);
        switch($domainArray[0]) {
	        case 'admin':
		        define('Identity', 'admin');
		        break;
	        case 'www' :
	        case 'm' :
	        case 'mobile' :
		        define('Identity', 'user');
		        break;
	        case 'super':
	        	define('Identity' , 'super');
	        	break;
	        default:
		        publicFunction::error('9999-1','',999);
	        	break;
        }
    }
}

<?php
/**
 * PublicFunction 頁碼
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author  Brian
 * @date 2020/11/07
 * @since 0.1.0 2020/11/07
 */

namespace Libraries;

use Libraries\Language as language;

/**
 * PublicFunction class
 */
class PublicFunction {
	/** @var array $language 語系 */
    private static $language = [];

	/** @var array $language 系統設定參數 */
	private static $systemCode = [];

	/**
	 * init 初始化
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	public static function init() {
        self::$language = language::getFile();
        self::$systemCode = require_once BASE_PATH . '/system/code.php';
	}

    /**
     * error 錯誤訊息跟錯誤碼
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param string $errorCode 錯誤碼
     * @param string $errorMsg 錯誤訊息
     * @param string $httpStatus HTTP status
     */
    public static function error( $errorCode , $errorMsg = '' , $httpStatus = 403) {
        //將要回傳的錯誤碼跟訊息組合
        $response['status'] = 'error';
        $response['errorCode'] = $errorCode;
        $response['msg'] = !empty($errorMsg) ? $errorMsg : self::$language['error'][$errorCode];

        switch($httpStatus) {
	        default:
	        case '403':
	            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
	        	break;
            case '998':
                header($_SERVER['SERVER_PROTOCOL'] . ' 998 Permission Deny');
                break;
	        case '999':
		        header($_SERVER['SERVER_PROTOCOL'] . ' 999 Illegal Request');
		        break;
        }

        //json 導出
        self::json($response);
    }

    /**
     * json 資料轉成json格式
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param string $response
     */
    public static function json( $response = [] , $status = '') {
        header('Content-Type: application/json; charset=utf-8');
        switch ($status) {
            default:
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                break;
            case 'success':
                echo json_encode(array_merge(['status' => 'success'] ,  $response), JSON_UNESCAPED_UNICODE);
                break;
        }

        exit();
    }

	/**
	 * emptyOutput 回傳空資料
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return string
	 */
	public static function emptyOutput( ) {
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['status' => 'success', 'data' => []], JSON_UNESCAPED_UNICODE);
		exit();
	}

    /**
     * token 產生隨機碼
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param string $length
     * @return string
     */
    public static function token( $length = 32 ) {
        //產稱隨機碼
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        //max
        $max = strlen($string) - 1;

        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $token .= $string[mt_rand(0, $max)];
        }

        return $token;
    }

    /**
     * encode 自定義 加密
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $string
     * @param $secretKey
     * @return string
     */
    public static function encode( $string , $secretKey ) {
        return base64_encode($string . '_' . md5($secretKey));
    }

    /**
     * decode 自定義 解密
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $string
     * @param $secretKey
     * @return string
     */
    public static function decode( $string , $secretKey ) {
        return str_replace('_' . md5($secretKey) , '' , base64_decode($string));
    }

	/**
	 * getSystemCode 取得系統設定參數
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return mixed
	 */
	public static function getSystemCode() {
		return self::$systemCode;
	}

	/**
	 * getIP 取得當前請求IP
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return string
	 */
	public static function getIP() {
		$ip = false;
		if(!empty($_SERVER["HTTP_CLIENT_IP"])) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}

		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode("," , str_replace(" " , "" , $_SERVER['HTTP_X_FORWARDED_FOR']));
			if($ip) {
				array_unshift($ips , $ip);
				$ip = false;
			}

			for($i = 0 ; $i < count($ips) ; $i++) {
				if(filter_var($ips[$i] , FILTER_VALIDATE_IP , FILTER_FLAG_IPV4) == true) {
					if(!preg_match("/^(10\.|172\.16\.|192\.168\.)/i" , $ips[$i])) {
						$ip = $ips[$i];
						break;
					}
				} elseif(filter_var($ips[$i] , FILTER_VALIDATE_IP , FILTER_FLAG_IPV6) == true) {
					//這邊如果有需要排除某些ipv6的ip,需要補上
					$ip = $ips[$i];
					break;
				}
			}
		}

		($ip ? $ipa['REMOTE_ADDR'] = $ip : $ipa['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR']);
		$ipa['SERVER_ADDR'] = $_SERVER['SERVER_ADDR'];
		return $ipa;
	}

	/**
	 * fillArray 使用一個陣列填充成爲另外一個陣列
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $resource 來源陣列
	 * @param array $response 回傳陣列需求鍵
	 * @return array 回傳資料
	 */
	public static function fillArray( $resource , $response ) {
		return array_intersect_key($resource , array_fill_keys($response , ''));
	}
}
<?php
/**
 * Validator
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/10/03
 * @since 0.1.0 2020/10/03
 */

namespace Libraries;

use Libraries\Language as language;

/**
 * Request class
 */
class Validator
{
    private static $language = array();
    private static $errorCode;
    private static $errorMsg;

	/**
	 * init 初始化
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
    public static function init() {
        self::$language = language::getFile();
    }

    /**
     * make 進行驗證
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $data
     * @param $validator
     */
    public static function make($data, $validator) {
        foreach ($validator as $key => $value) {
            //驗證的規則
            $rules = explode('|', $value);

            //若無必填 且請求欄位併無此欄位則跳過
            if((!in_array('required' , $rules)) && (empty($data[$key]))) {
            	continue;
            }

            //一條一條驗證
            foreach ($rules as $rule) {
                if(!self::checkFormat($key, $data, $rule)) {
                    PublicFunction::error(self::$errorCode, self::$errorMsg);
                }
            }
        }

        return true;
    }


    /**
     * validator 驗證
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $key
     * @param $data
     * @param $rule
     * @return bool
     */
    private static function checkFormat($key, $data, $rule) {
	    self::$language['common'][$key] = isset(self::$language['common'][$key]) ? self::$language['common'][$key] : $key;//防報錯 無此設定字典擋欄位
        switch ($rule) {
            case 'required':
                if (!isset($data[$key])) {
                    self::$errorCode = '0006';
                    self::$errorMsg = sprintf(self::$language['error']['0006'], self::$language['common'][$key]);
                    return false;
                }
                break;
            case 'string':
                if (empty($data[$key])) {
                    self::$errorCode = '0007';
                    self::$errorMsg = sprintf(self::$language['error']['0007'], self::$language['common'][$key]);
                    return false;
                }
                break;
            case 'email':
                if ( !preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $data[$key])) {
                    self::$errorCode = '0008';
                    self::$errorMsg = sprintf(self::$language['error']['0008'], self::$language['common'][$key]);
                    return false;
                }
                break;
            case 'int':
            case 'integer':
                if (( !is_numeric($data[$key])) || ((int)$data[$key] != $data[$key])) {
                    self::$errorCode = '0009';
                    self::$errorMsg = sprintf(self::$language['error']['0009'], self::$language['common'][$key]);
                    return false;
                }
                break;
	        case 'number':
		        if (!is_numeric($data[$key])) {
			        self::$errorCode = '0024';
			        self::$errorMsg = sprintf(self::$language['error']['0024'], self::$language['common'][$key]);
			        return false;
		        }
		        break;
            case 'array':
                if ( !is_array($data[$key])) {
                    self::$errorCode = '0010';
                    self::$errorMsg = sprintf(self::$language['error']['0010'], self::$language['common'][$key]);
                    return false;
                }
                break;
            case 'url':
                if ( !preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $_POST['url'])) {
                    self::$errorCode = '0011';
                    self::$errorMsg = sprintf(self::$language['error']['0011'], self::$language['common'][$key]);
                    return false;
                }
                break;
            case 'dateTimeRange':
                $dataTime = explode(' - ', $data[$key]);
                //判斷是否有開始結束
                if (count($dataTime) != 2) {
                    self::$errorCode = '0012';
                    self::$errorMsg = sprintf(self::$language['error']['0012'], self::$language['common'][$key]);
                    return false;
                }
                //判斷日期格式
                foreach ($dataTime as $check) {
                    $format = 'Y-m-d H:i:s';
                    $unixTime = strtotime($check);
                    if ( !$unixTime || date($format, $unixTime) != $check) {
                        self::$errorCode = '0013';
                        self::$errorMsg = sprintf(self::$language['error']['0013'], self::$language['common'][$key]);
                        return false;
                    }
                }
                break;
            case 'date' :
                //判斷日期格式
                $format = 'Y-m-d';
                $unixTime = strtotime($data[$key]);
                if ( !$unixTime || date($format, $unixTime) != $data[$key]) {
                    self::$errorCode = '0014';
                    self::$errorMsg = sprintf(self::$language['error']['0014'], self::$language['common'][$key]);
                    return false;
                }
                break;
            case 'time' :
                //判斷時間格式
                $format = 'H:i:s';
                $unixTime = strtotime($data[$key]);
                if ( !$unixTime || date($format, $unixTime) != $data[$key]) {
                    self::$errorCode = '0015';
                    self::$errorMsg = sprintf(self::$language['error']['0015'], self::$language['common'][$key]);
                    return false;
                }
                break;
            case 'percent':
                if (($data[$key] < 0) || ($data[$key] > 100) || ( !is_numeric($data[$key]))) {
                    self::$errorCode = '0016';
                    self::$errorMsg = sprintf(self::$language['error']['0016'], self::$language['common'][$key]);
                    return false;
                }
                break;
            case 'IP':
                $format = [
                    'IPv4' => '/^((25[0-5]|2[0-4]\d|[0-1]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[0-1]?\d\d?)$/',
                    'IPv6' => '/^\s*((([0-9A-Fa-f]{1,4}:){7}(([0-9A-Fa-f]{1,4})|:))|(([0-9A-Fa-f]{1,4}:){6}(:|((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})|(:[0-9A-Fa-f]{1,4})))|(([0-9A-Fa-f]{1,4}:){5}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){4}(:[0-9A-Fa-f]{1,4}){0,1}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){3}(:[0-9A-Fa-f]{1,4}){0,2}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){2}(:[0-9A-Fa-f]{1,4}){0,3}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)(:[0-9A-Fa-f]{1,4}){0,4}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(:(:[0-9A-Fa-f]{1,4}){0,5}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})))(%.+)?\s*$/',
                ];
                if (( !preg_match($format['IPv4'], $data[$key])) && ( !preg_match($format['IPv6'], $data[$key]))) {
                    self::$errorCode = '0017';
                    self::$errorMsg = sprintf(self::$language['error']['0017'], self::$language['common'][$key]);
                    return false;
                }
                break;
            case strtolower(substr($rule, 0, 3)) == 'max' :
                //取得要比對的數值
                $explode = explode(':', $rule);
                //比對大小
                if ($data[$key] > $explode[1]) {
                    self::$errorCode = '0018';
                    self::$errorMsg = sprintf(self::$language['error']['0018'], self::$language['common'][$key], $explode[1]);
                    return false;
                }
                break;
            case strtolower(substr($rule, 0, 3)) == 'min' :
                //取得要比對的數值
                $explode = explode(':', $rule);
                //比對大小
                if ($data[$key] < $explode[1]) {
                    self::$errorCode = '0019';
                    self::$errorMsg = sprintf(self::$language['error']['0019'], self::$language['common'][$key], $explode[1]);
                    return false;
                }
                break;
            case strtolower(substr($rule, 0, 6)) == 'lenmax' :
                //取得要比對的數值
                $explode = explode(':', $rule);
                //比對大小
                if (strlen($data[$key]) > $explode[1]) {
                    self::$errorCode = '0020';
                    self::$errorMsg = sprintf(self::$language['error']['0020'], self::$language['common'][$key], $explode[1]);
                    return false;
                }
                break;
            case strtolower(substr($rule, 0, 6)) == 'lenmin' :
                //取得要比對的數值
                $explode = explode(':', $rule);
                //比對大小
                if (strlen($data[$key]) < $explode[1]) {
                    self::$errorCode = '0021';
                    self::$errorMsg = sprintf(self::$language['error']['0021'], self::$language['common'][$key], $explode[1]);
                    return false;
                }
                break;
	        case strtolower(substr($rule, 0, 2)) == 'in' :
		        //取得要比對的數值
		        $explode = explode('&', explode(':', $rule)[1]);

		        //比對有無在此陣列值
		        if (!in_array($data[$key] , $explode)) {
			        self::$errorCode = '0022';
			        self::$errorMsg = sprintf(self::$language['error']['0022'], self::$language['common'][$key], $explode[1]);
			        return false;
		        }
		        break;
	        case strtolower(substr($rule, 0, 6)) == 'sameas' :
		        $same = explode(':', $rule)[1];
		        //是否相同
		        if ($data[$key] != $data[$same]) {
			        self::$errorCode = '0023';
			        self::$errorMsg = sprintf(self::$language['error']['0023'], self::$language['common'][$same], $same);
			        return false;
		        }
		        break;
            default:
                break;
        }

        return true;
    }

}
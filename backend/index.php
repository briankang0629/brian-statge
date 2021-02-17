<?php
/**
 *  所有網址將會強制後讀取index後依路徑分配檔案位置
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2019/04/13
 * @since 0.1.0 2019/04/13
 */
// Version 定義版本
define('VERSION', '1.0.0');
ini_set('display_errors', 'on');     # 開啟錯誤輸出2020-09-29 加上 以後要拿掉
date_default_timezone_set("Asia/Taipei");
header("Set-Cookie: name=value; httpOnly");//

//autoload
require_once 'vendor/autoload.php';
//config 設定檔
require_once 'config.php';

/*--------------------------- Library 註冊 -----------------------------*/
//宣告 物件


//request
Libraries\Request::init();

//language @todo
if (isset(Libraries\Request::$cookie['localize'])) {
	Libraries\Language::init(Libraries\Request::$cookie['localize']);
} else {
//    setcookie('localize', 'zh-tw');
	Libraries\Language::init('zh-tw');//Language
}

//PublicFunction
Libraries\PublicFunction::init();

//Validator
Libraries\Validator::init();

//route 路由設定
require_once 'app/route.php';
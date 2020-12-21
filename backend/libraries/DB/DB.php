<?php
/**
 * DB 資料庫連線
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2019/06/21
 * @since 0.1.0 2019/06/21
 */

namespace Libraries;

use DB\PDO;

class DB
{
    /** @var $instance */
    private static $instance;

    /**
     * constructor.
     * 使用 private 建構子避免在外面被意外地初始化
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    private function __construct() {}

    /**
     * Initialize 內部初始化
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @throws \Exception
     */
    private static function Initialize() {
        if ( !isset(self::$instance)) {
            self::$instance = new PDO(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
        }
    }

    /**
     * getInitialize 外部啟用
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public static function getInitialize() {
        self::Initialize();
        if (isset(self::$instance)) {
            return self::$instance;
        } else {
            return NULL;
        }
    }

    /**
     * __destruct 解構子 中斷連線
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
	public function __destruct() {
        if (isset(self::$instance)) {
            self::$instance = null; // 會自動執行解構子 close link
        }
    }
}

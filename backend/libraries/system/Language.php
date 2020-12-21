<?php
/**
 * Language 語系
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author  Brian
 * @date 2019/07/21
 * @since 0.1.0 2019/07/21
 */

namespace Libraries;

/**
 * Class Language
 * @package Libraries
 */
class Language {
    /** @var $language array 語系變數 */
    private static $language;

    /**
     * __construct
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    private function __construct() {}

	/**
	 * __destruct()
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	private function __destruct() {}

	/**
	 * initialize 語系檔初始化
	 *
	 * @param string $language
	 * @since 0.0.1
	 * @version 0.0.1
	 */
    public static function init($language = 'zh-tw') {
	    //資料夾所有語系檔案
	    $files = glob(BASE_PATH . '/app/language/' . $language . '/*.php');
	    //組成語系陣列
	    foreach ($files as $file) {
		    //檔案名做索引
		    $file_name = str_replace('/', '', str_replace('.php', '', strrchr($file, '/')));
		    self::$language[$file_name] = require_once $file;
	    }
    }

    /**
     * getFile 取得語系黨
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @return $language 語系變數
     */
    public static function getFile() {
        return self::$language;
    }

}
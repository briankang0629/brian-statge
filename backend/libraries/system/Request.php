<?php
/**
 * Request
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2019/12/04
 * @since 0.1.0 2019/12/04
 */
namespace Libraries;
/**
* Request class
*/
class Request {
	public static $get = array();
	public static $post = array();
	public static $put = array();
	public static $cookie = array();
	public static $files = array();
	public static $server = array();
	public static $delete = array();

	/**
	 * __construct
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	private function __construct() {}

	/**
	 * init
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	public function init() {
		self::$get = self::clean($_GET);
		self::$post = self::clean($_POST);
		self::$cookie = self::clean($_COOKIE);
		self::$files = self::clean($_FILES);
		self::$server = self::clean($_SERVER);

        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            $_PUT = [];
            parse_str(file_get_contents('php://input'), $_PUT);
            self::$put = self::clean($_PUT);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $_DELETE = [];
            parse_str(file_get_contents('php://input'), $_DELETE);
            self::$delete = self::clean($_DELETE);
        }
	}
	
	/**
     * clean
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param	array	$data
     * @return	array
     */
	public static function clean($data) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				unset($data[$key]);
				$data[self::clean($key)] = self::clean($value);
			}
		} else {
			$data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
		}

		return $data;
	}
}
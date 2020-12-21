<?php
/**
 * Middleware
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/11/17
 * @since 0.1.0 2020/11/17
 */


/**
 * Class Middleware
 * @package Middleware
 */
abstract class Middleware {
    /** @var array exclude 不執行的接口 */
    protected $exclude = [];

	/** @var array include 執行的接口 */
	protected $include = [];

    /** @var object $db DB連線變數*/
    protected $db;

    /** @var object $router router變數*/
    protected $router;

	/** @var array $requestHeaders 請求的header*/
	protected static $requestHeaders = [];

    /**
     * Middleware constructor.
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function __construct() {}

    public function handle($router) {}

    public function match() {

    }
}

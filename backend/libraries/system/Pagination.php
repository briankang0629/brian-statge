<?php
/**
 * Pagination 頁碼
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author  Brian
 * @date 2020/10/23
 * @since 0.1.0 2020/10/23
 */

namespace Libraries;

/**
 * Pagination class
 */
class Pagination {
	/** @var @var $total 總數 */
	public $total = 0;

	/** @var @var $page 目前第幾頁 */
	public $page;

	/** @var @var $perPage 限制一頁幾筆 */
	public $perPage;

	/** @var @var $start 從第幾頁開始 */
	public $start;


	/**
	 * Pagination constructor.
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param array $data
	 */
	public function __construct( $data = array() ) {
		$this->page = !empty($data['page']) ? (int)$data['page'] : 1;
		$this->perPage = !empty($data['perPage']) ? (int)$data['perPage'] : 10;
		$this->start = ($this->page - 1) * $this->perPage;
	}

	/**
	 * output
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return array
	 */
	public function output () {
		return [
			'page' => $this->page,
			'total' => $this->total,
			'perPage' => $this->perPage,
			'totalPage' => ceil($this->total / $this->perPage),
		];
	}
}
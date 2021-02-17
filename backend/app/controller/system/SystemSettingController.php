<?php
/**
 * SystemSettingController 系統設定控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2021/02/08
 */

use Libraries\PublicFunction as publicFunction;
use Libraries\Validator as validator;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】系統設定控制器
 */
class SystemSettingController extends Controller
{

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

    /**
     * lists 系統設定清單
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function lists() {}

    /**
     * info 系統設定資訊
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function info($id) {}

	/**
	 * store 新增系統設定
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function store() {}

    /**
     * update 修改系統設定
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param int $systemMenuID 系統設定id
     * @return string|array
     */
    public function update($id) {}

    /**
     * delete 刪除系統設定
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $id
     * @return string
     */
    public function delete ($id) {}

	/**
	 * config 取系統設定設定檔
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return string
	 */
	public function config() {}

    //----------------------------------------------------------------
    //EndRegion API
    //----------------------------------------------------------------
}
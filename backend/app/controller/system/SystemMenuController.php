<?php
/**
 * SystemMenuController 系統選單控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/12/04
 */

use Libraries\PublicFunction as publicFunction;
use Libraries\Validator as validator;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】系統選單控制器
 */
class SystemMenuController extends Controller
{

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

    /**
     * lists 系統選單清單
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function lists() {}

    /**
     * info 系統選單資訊
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function info($id) {}

	/**
	 * store 新增系統選單
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function store() {}

    /**
     * update 修改系統選單
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param int $systemMenuID 系統選單id
     * @return string|array
     */
    public function update($id) {}

    /**
     * delete 刪除系統選單
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $id
     * @return string
     */
    public function delete ($id) {}

	/**
	 * config 取系統選單設定檔
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return string
	 */
	public function config() {
		//宣告
		$menu = [];
		$subMenu = [];
		$permission = Permission;
		$systemMenuModel = new SystemMenuModel();

		//取系統選單列表
		$lists = $systemMenuModel->lists(['status' => 'Y']);

		//取母選單
		foreach($lists as $key => $list) {
			if($list['parentId'] == 0) {
				$menu[$list['code']] = $list;
			} else {
				$subMenu[] = $list;
			}
		}

		//取子選單
		foreach($menu as $key => $value) {
			foreach($subMenu as $sub) {
				if($value['systemMenuId'] == $sub['parentId']) {
                    //判斷權限 若無開放則移除
				    if(!isset($permission[$value['code']][$sub['code']]) || ($permission[$value['code']][$sub['code']] == 'N')) {
				        continue;
                    }
				    //存到子選單
					$menu[$key]['subMenu'][$sub['code']] = $sub;
				}
			}
		}

		//回傳
		return publicFunction::json([
			'data' => $menu
		], 'success');
	}

    //----------------------------------------------------------------
    //EndRegion API
    //----------------------------------------------------------------
}
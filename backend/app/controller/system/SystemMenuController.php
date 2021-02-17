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
		$children = [];
		$permission = Permission;
		$systemMenuModel = new SystemMenuModel();

		//取系統選單列表
		$lists = $systemMenuModel->lists(['status' => 'Y']);

		//取母選單
		foreach($lists as $key => $list) {
			if($list['parentId'] == 0) {
                $list['toggle'] = false;
                $menu[] = $list;
			} else {
				$children[] = $list;
			}
		}

		//取子選單
		foreach($menu as $key => $value) {
            //預設無任何子選單
            $noChildrenList = true;

			foreach($children as $sub) {
				if($value['systemMenuId'] == $sub['parentId']) {
                    $noChildrenList = false;
                    //判斷權限 若無開放則移除
				    if(!isset($permission[$value['code']][$sub['code']]) || ($permission[$value['code']][$sub['code']] == 'N')) {
				        continue;
                    }
				    //存到子選單
					$menu[$key]['children'][] = $sub;
				}
			}

            //檢視母選單下的子選單有無任一開放 若子選單都未開放 母選單則移除 ＠todo寫法需優化
			if((!$noChildrenList) && (!isset($menu[$key]['children']))) {
			    unset($menu[$key]);
            }
		}

		//重新排序
		sort($menu);

		//回傳
		publicFunction::json([
			'data' => $menu
		], 'success');
	}

    //----------------------------------------------------------------
    //EndRegion API
    //----------------------------------------------------------------
}

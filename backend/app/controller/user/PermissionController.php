<?php
/**
 * PermissionController 權限端控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/11/07
 */

use Libraries\PublicFunction as publicFunction;
use Libraries\Validator as validator;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】權限端控制器
 */
class PermissionController extends Controller
{

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

    /**
     * lists 權限清單
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function lists() {
        //驗證權限
        $this->permission(['A'] , 'admin/permission' , 'V');

	    //宣告
	    $data = [];
	    $permissionModel = new PermissionModel();
	    $adminModel = new AdminModel();

        //取權限列表
        $permissions = $permissionModel->lists(request::$get);

        //計算該權限下有多少管理者
        foreach ($permissions as $key => $permission) {
	        $permission['adminCount'] = $adminModel->getAdminCountByPermissionId($permission['permissionId']);
	        $data[] = $permission;
        }

        //回傳
        publicFunction::json([
        	'data' => $data,
	        'pagination' => $permissionModel->getPagination()
        ] , 'success');
    }

    /**
     * info 權限資訊
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function info($id) {
        //驗證權限
	    $this->permission(['A'] , 'admin/permission' , 'V');

	    //宣告
	    $permissionModel = new PermissionModel();

        //取權限資訊
	    if(!$permission = $permissionModel->info($id)) { return publicFunction::emptyOutput(); }

        //回傳
        publicFunction::json([
        	'data' => $permission
        ] , 'success');
    }

	/**
	 * store 新增權限
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function store() {
        //驗證權限
		$this->permission(['A'] , 'admin/permission' , 'E');

		//宣告
		$permissionModel = new PermissionModel();

		//規則
		$require = [
			'name' => 'required|string',
			'permission' => 'required|string',
			'status' => 'required|in["Y" , "N"]',
		];

		//驗證
        validator::make(request::$post , $require);

		//檢查名稱有無重覆
		if(!$permissionModel->checkExist('name', request::$post['name'])) {
			publicFunction::error('0104');
		}

		//傳送參數
        $sentData = [
            'name' => request::$post['name'],
            'permission' => json_encode(request::$post['permission']),
            'status' => request::$post['status'],
            'createTime' => date('Y-m-d H:i:s'),
        ];

		//執行更新
        $permissionModel->store($sentData);

		//操作記錄
		$this->writeLog(7 , $sentData , $permissionModel->db->getSql());

		//回傳
        publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['create']['success'],
        ]);

	}

    /**
     * update 修改權限
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param int $permissionID 權限id
     * @return string|array
     */
    public function update($id) {
        //驗證權限
	    $this->permission(['A'] , 'admin/permission' , 'E');

	    //宣告
	    $permissionModel = new PermissionModel();

	    //規則
        $require = [
            'name' => 'required|string',
            'permission' => 'required|string',
            'status' => 'required|in["Y" , "N"]',
        ];

        //驗證
        validator::make(request::$put , $require);

        //傳送參數
        $sentData = [
            'permission' => json_encode(request::$put['permission']),
            'name' => request::$put['name'],
            'status' => request::$put['status'],
        ];

        //執行更新
        $permissionModel->update($id, $sentData);

	    //操作記錄
	    $this->writeLog(8 , $sentData , $permissionModel->db->getSql());

	    //回傳
        publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['update']['success'],
        ]);
    }

    /**
     * delete 刪除權限
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $id
     * @return string
     */
    public function delete ($id) {
        //驗證權限
	    $this->permission(['A'] , 'admin/permission' , 'E');

	    //宣告
	    $permissionModel = new PermissionModel();

	    //單筆或多筆刪除
        $permissionModel->delete(['permissionId', 'IN', '( ' . $id . ' )']);

	    //操作記錄
	    $this->writeLog(9 , [] , $permissionModel->db->getSql());

	    //回傳
        publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['delete']['success'],
        ]);
    }

    /**
     * getPermissionConfig 依系統預設權限
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return mixed
     */
    public function getPermissionConfig() {
        publicFunction::json([
            'status' => 'success',
            'data' => publicFunction::getSystemCode()['permission'],
        ]);
    }

    //----------------------------------------------------------------
    //EndRegion API
    //----------------------------------------------------------------
}

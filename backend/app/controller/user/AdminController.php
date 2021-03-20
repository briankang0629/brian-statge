<?php
/**
 * AdminController 管理端控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/11/10
 */

use Libraries\PublicFunction as publicFunction;
use Libraries\Validator as validator;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】管理端控制器
 */
class AdminController extends Controller
{

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

    /**
     * lists 管理者清單
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function lists() {
        //驗證權限
        $this->permission(['A'] , 'admin/adminList' , 'V');

        //宣告
	    $data = [];
	    $mediaModel = new MediaModel();
	    $adminModel = new AdminModel();

	    //管理者列表
	    $adminLists = $adminModel->lists(request::$get);

	    //處理管理者圖片
	    foreach($adminLists as $admin) {
	    	//若管理者沒有外部圖片
	    	if(!$admin['picture']) {
	    		//有上傳圖片
			    $admin['picture'] = $mediaModel->getMedia($admin['uploadId']);
		    }

		    //管理者列表
		    $data[] = $admin;
	    }

        //取管理者列表
        publicFunction::json([
		    'data' => $data,
		    'pagination' => $adminModel->getPagination()
	    ] , 'success');
    }

    /**
     * info 管理者資訊
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function info($id) {
        //驗證權限
	    $this->permission(['A'] , 'admin/adminList' , 'V');

	    //宣告
	    $adminModel = new AdminModel();

        //管理者資料
        if(!$admin = $adminModel->info($id)) { return publicFunction::emptyOutput(); }

        //有無個人圖片
        if(!$admin['picture']) {
            //宣告
            $mediaModel = new MediaModel();

            //有上傳圖片
	        $admin['picture'] = $mediaModel->getMedia($admin['uploadId']);
        }

        //取管理者
        publicFunction::json([
        	'data' => $admin
        ] , 'success');
    }

	/**
	 * store 新增管理者
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function store() {
        //驗證權限
		$this->permission(['A'] , 'admin/adminList' , 'E');

		//宣告
		$adminModel = new AdminModel();

		//規則
		$require = [
			'permissionId' => 'required|integer|lenMax:10',
			'name' => 'required|string',
			'account' => 'required|string|lenMax:32|lenMin:3',
			'password' => 'required|string|lenMax:32|lenMin:3',
			'confirm' => 'required|string|lenMax:32|lenMin:3|sameAs:password',
			'email' => 'required|email',
			'status' => 'required|in["Y" , "N"]',
		];

		//驗證
        validator::make(request::$post , $require);

		//密碼加密
        $secretKey = publicFunction::token();
        request::$post['password'] = publicFunction::encode(request::$post['password'] , $secretKey);

		//檢查帳號有無重覆
		if(!$adminModel->checkExist('account', request::$post['account'])) {
			publicFunction::error('0102');
		}

		//檢查email有無重覆
		if(!$adminModel->checkExist('email', request::$post['email'])) {
			publicFunction::error('0103');
		}

		//傳送參數
        $sentData = [
            'account' => request::$post['account'],
            'password' => request::$post['password'],
            'name' => request::$post['name'],
            'permissionId' => request::$post['permissionId'],
            'status' => request::$post['status'],
            'email' => request::$post['email'],
            'secretKey' => $secretKey,
            'sub' => 'Y',
            'createTime' => date('Y-m-d H:i:s'),
        ];

		//執行更新
        $adminModel->store($sentData);

		//操作記錄
		$this->writeLog(4 , $sentData , $adminModel->db->getSql());

		//回傳
		publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['create']['success'],
        ]);

	}

    /**
     * update 修改管理者
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param int $adminID 管理者id
     * @return string|array
     */
    public function update($id) {
        //驗證權限
	    $this->permission(['A'] , 'admin/adminList' , 'E');

	    //宣告
	    $adminModel = new AdminModel();

	    //規則
        $require = [
	        'permissionId' => 'required|integer|lenMax:10',
            'password' => 'string|lenMax:32|lenMin:3',
            'confirm' => 'string|lenMax:32|lenMin:3|sameAs:password',//@todo if !isset password
            'name' => 'string',
            'status' => 'in["Y" , "N"]',
        ];

        //驗證
        validator::make(request::$put , $require);

        //傳送參數
        $sentData = [
            'permissionId' => request::$put['permissionId'],
            'name' => request::$put['name'],
            'status' => request::$put['status'],
        ];

        //密碼加密
	    if(!empty(request::$put['password'])) {
	        //取用戶金鑰
            $secretKey = $adminModel->item($adminModel->table, 'secretKey', ['adminId' , '=' , $id])['secretKey'];
            $sentData['password'] = publicFunction::encode(request::$put['password'], $secretKey);
        }

        //執行更新
        $adminModel->update($id, $sentData);

	    //操作記錄
	    $this->writeLog(5 , $sentData , $adminModel->db->getSql());

	    //回傳
        publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['update']['success'],
        ]);
    }

    /**
     * delete 刪除管理者
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $id
     * @return string
     */
    public function delete ($id) {
        //驗證權限
	    $this->permission(['A'] , 'admin/adminList' , 'E');

	    //宣告
	    $adminModel = new AdminModel();

	    //單筆或多筆刪除
        $adminModel->delete(['adminId', 'IN', '( ' . $id . ' )']);

	    //操作記錄
	    $this->writeLog(6 , [] , $adminModel->db->getSql());

	    //回傳
        publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['delete']['success'],
        ]);
    }
    //----------------------------------------------------------------
    //EndRegion API
    //----------------------------------------------------------------
}

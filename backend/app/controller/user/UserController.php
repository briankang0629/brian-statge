<?php
/**
 * UserController 使用者控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2019/07/01
 */

use Libraries\PublicFunction as publicFunction;
use Libraries\Validator as validator;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】使用者控制器
 */
class UserController extends Controller
{

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

    /**
     * lists 會員清單
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function lists() {
        //驗證權限
        $this->permission('user/userList','V','A');

        //宣告
	    $userModel = new UserModel();

	    //取會員列表
        return publicFunction::json([
        	'data' => $userModel->lists(request::$get),
        	'pagination' =>  $userModel->getPagination()
        ] , 'success');
    }

    /**
     * info 會員資訊
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function info($id) {
        //驗證權限
        $this->permission('user/userList','V','A');

	    //宣告
	    $userModel = new UserModel();

        //取會員資料
        if(!$user = $userModel->info($id)) { return publicFunction::emptyOutput(); }

        //回傳
        return publicFunction::json([
        	'data' => $user
        ] , 'success');
    }

	/**
	 * store 新增會員
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function store() {
        //驗證權限
        $this->permission('user/userList','E','A');

		//宣告
		$userModel = new UserModel();

		//規則
		$require = [
			'userGroupId' => 'required|integer|lenMax:10',
			'name' => 'required|string',
			'account' => 'required|string|lenMax:32|lenMin:3',
			'password' => 'required|string|lenMax:32|lenMin:3',
			'confirm' => 'required|string|lenMax:32|lenMin:3|sameAs:password',
			'email' => 'required|email',
			'status' => 'required|in:Y&N',
		];

		//驗證
        validator::make(request::$post , $require);

		//密碼加密
        $secretKey = publicFunction::token();
        request::$post['password'] = publicFunction::encode(request::$post['password'] , $secretKey);

		//檢查帳號有無重覆
		if(!$userModel->checkExist('account', request::$post['account'])) {
			publicFunction::error('0102');
		}

		//檢查email有無重覆
		if(!$userModel->checkExist('email', request::$post['email'])) {
			publicFunction::error('0103');
		}

		//傳送參數
        $sentData = [
            'account' => request::$post['account'],
            'password' => request::$post['password'],
            'name' => request::$post['name'],
            'userGroupId' => request::$post['userGroupId'],
            'status' => request::$post['status'],
            'email' => request::$post['email'],
            'mobile' => request::$post['mobile'],
            'secretKey' => $secretKey,
            'createTime' => date('Y-m-d H:i:s'),
        ];

		//新增
        $userModel->store($sentData);

        //操作記錄
		$this->writeLog(1 , $sentData , $userModel->db->getSql());

		//回傳
        publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['create']['success'],
        ]);
	}

    /**
     * update 修改會員
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param int $userID 會員id
     * @return string|array
     */
    public function update($id) {
        //驗證權限
        $this->permission('user/userList','E','A');

	    //宣告
	    $userModel = new UserModel();

        //規則
        $require = [
	        'userGroupId' => 'required|integer|lenMax:10',
            'password' => 'string|lenMax:32|lenMin:3',
            'confirm' => 'string|lenMax:32|lenMin:3|sameAs:password',//@todo if !isset password
            'name' => 'string',
            'mobile' => 'string|lenMax:10',
            'status' => 'in:Y&N',
        ];

        //驗證
        validator::make(request::$put , $require);

        //傳送參數
        $sentData = [
            'userGroupId' => request::$put['userGroupId'],
            'name' => request::$put['name'],
            'status' => request::$put['status'],
            'mobile' => request::$put['mobile'],
        ];

        //密碼加密
	    if(!empty(request::$put['password'])) {
	        //取用戶金鑰
            $secretKey = $userModel->item($userModel->table, 'secretKey', ['userId' , '=' , $id])['secretKey'];
            $sentData['password'] = publicFunction::encode(request::$put['password'], $secretKey);
        }

        //執行更新
        $userModel->update($id, $sentData);

	    //操作記錄
	    $this->writeLog(2 , $sentData , $userModel->db->getSql());

	    //回傳
        publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['update']['success'],
        ]);
    }

    /**
     * delete 刪除會員
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $id
     * @return string
     */
    public function delete ($id) {
        //驗證權限
        $this->permission('user/userList','E','A');

	    //宣告
	    $userModel = new UserModel();

        //單筆或多筆刪除
        $userModel->delete(['userId', 'IN', '( ' . $id . ' )']);

	    //操作記錄
	    $this->writeLog(3 , [] , $userModel->db->getSql());

        //回傳
        return publicFunction::json([
            'status' => 'success',
            'msg' => language::getFile()['common']['delete']['success'],
        ]);
    }
    //----------------------------------------------------------------
    //EndRegion API
    //----------------------------------------------------------------
}
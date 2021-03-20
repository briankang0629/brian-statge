<?php
/**
 * UserGroupController 使用者群組控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/10/24
 */
use Libraries\PublicFunction as publicFunction;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】使用者群組控制器
 */
class UserGroupController extends Controller
{

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

    /**
     * lists 會員群組清單
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function lists() {
        //驗證權限
        $this->permission(['A'] , 'user/userGroup' , 'V');

	    //宣告
	    $userGroupModel = new UserGroupModel();

        //取會員列表
        return publicFunction::json([
        	'data' => $userGroupModel->lists(request::$get),
	        'pagination' => $userGroupModel->getPagination()
        ] , 'success');
    }

    /**
     * info 會員群組資訊
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param int $id
     * @return  string
     */
    public function info($id) {
        //驗證權限
	    $this->permission(['A'] , 'user/userGroup' , 'V');

	    //宣告
	    $userGroupModel = new UserGroupModel();

        //取會員群組
	    if($userGroup = $userGroupModel->info($id)) { return publicFunction::emptyOutput(); }

        //回傳
        return publicFunction::json([
        	'data' => $userGroup
        ] , 'success');
    }

	/**
	 * store 新增會員群組
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function store() {
        //驗證權限
		$this->permission(['A'] , 'user/userGroup' , 'E');
    }

    /**
     * update 修改會員群組
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param int $id 會員群組id
     * @return boolean
     */
    public function update($id) {
        //驗證權限
	    $this->permission(['A'] , 'user/userGroup' , 'E');
    }
    //----------------------------------------------------------------
    //EndRegion API
    //----------------------------------------------------------------
}

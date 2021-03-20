<?php
/**
 * LogRecordController 操作記錄控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/11/28
 */

use Libraries\PublicFunction as publicFunction;
use Libraries\Validator as validator;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】操作記錄控制器
 */
class LogRecordController extends Controller
{

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

    /**
     * lists 操作記錄清單
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function lists() {
        //驗證權限
        $this->permission(['A'] , 'logRecord/logRecordList' , 'V');

        //宣告
	    $logRecordModel = new LogRecordModel();

        //取操作記錄列表
        publicFunction::json([
        	'data' => $logRecordModel->lists(request::$get),
	        'pagination' => $logRecordModel->getPagination()
	    ] , 'success');
    }

    /**
     * info 操作記錄資訊
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function info($id) {
        //驗證權限
        $this->permission(['A'] , 'logRecord/logRecordList' , 'V');

	    //宣告
	    $logRecordModel = new LogRecordModel();

        //取操作記錄列表
        publicFunction::json([
            'data' => $logRecordModel->info($id)
        ], 'success');
    }

	/**
	 * store 新增操作記錄
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function store() {}

    /**
     * update 修改操作記錄
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param int $logRecordID 操作記錄id
     * @return string|array
     */
    public function update($id) {}

    /**
     * delete 刪除操作記錄
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $id
     * @return string
     */
    public function delete ($id) {}

	/**
	 * getLogRecordSetting 取操作記錄設定檔
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return string
	 */
	public function getLogRecordSetting () {
		//驗證權限
		$this->permission(['A'] , 'logRecord/logRecordList' , 'V');

		//取操作記錄列表
		publicFunction::json([
			'data' => publicFunction::getSystemCode()['logRecordSetting']
		] , 'success');
	}

    //----------------------------------------------------------------
    //EndRegion API
    //----------------------------------------------------------------
}

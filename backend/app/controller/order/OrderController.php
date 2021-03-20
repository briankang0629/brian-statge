<?php
/**
 * OrderController 訂單管理控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2021/02/21
 */

use Libraries\PublicFunction as publicFunction;
use Libraries\Validator as validator;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】訂單管理控制器
 */
class OrderController extends Controller
{

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

    /**
     * lists 訂單管理清單
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function lists() {}

    /**
     * info 訂單管理資訊
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function info($id) {}

	/**
	 * store 新增訂單管理
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function store() {}

    /**
     * update 修改訂單管理
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param int $adminID 訂單管理id
     * @return string|array
     */
    public function update($id) {}

    /**
     * delete 刪除訂單管理
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param $id
     * @return string
     */
    public function delete ($id) {}
    //----------------------------------------------------------------
    //EndRegion API
    //----------------------------------------------------------------

    //----------------------------------------------------------------
    // 客製化功能 API Start
    //----------------------------------------------------------------

    /**
     * getOrderGroupByMonth 取月份訂單 @todo此功能屬於首頁數據模組
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return string
     */
    public function getOrderGroupByMonth() {
        //驗證權限@todo
//        $this->permission(['A'] , '' , 'V');

        //宣告
        $data = [];
        $orderModel = new OrderModel();

        for ($int = 1; $int <= 12; $int++) {
            $data[] = array(
                'month' => date('Y-m', mktime(0, 0, 0, $int)),
                'total' => 0
            );
        }

        //取月份訂單
        foreach ($orderModel->getOrderGroupByMonth() as $key => $result) {
            $data[$key] = array(
                'month' => date('Y-m', strtotime($result['createTime'])),
                'total' => (int)$result['total']
            );
        }

        //取月份訂單
        publicFunction::json([
            'data' => $data,
        ] , 'success');
    }

    /**
     * getOrderTotal 取訂單總數
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return string
     */
    public function getOrderTotal() {
        //驗證權限@todo
//        $this->permission(['A'] , '' , 'V');

        //宣告
        $data = [];
        $orderModel = new OrderModel();
        $data['total'] = $orderModel->getOrderTotal();

        //訂單總數
        publicFunction::json([
            'data' => $data,
        ] , 'success');
    }

    /**
     * getOrderSale 取訂單銷售總額
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return string
     */
    public function getOrderSale() {
        //驗證權限@todo
//        $this->permission(['A'] , '' , 'V');

        //宣告
        $data = [];
        $orderModel = new OrderModel();
        $data['total'] = number_format($orderModel->getOrderSale());

        //訂單總數
        publicFunction::json([
            'data' => $data,
        ] , 'success');
    }

    //----------------------------------------------------------------
    // 客製化功能 API End
    //----------------------------------------------------------------

    //----------------------------------------------------------------
    // 附屬函示 API Start
    //----------------------------------------------------------------

    //----------------------------------------------------------------
    // 附屬函示 API End
    //----------------------------------------------------------------
}

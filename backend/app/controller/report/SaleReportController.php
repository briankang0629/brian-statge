<?php
/**
 * SaleReportController 銷售報表控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2021/03/19
 */

use Libraries\PublicFunction as publicFunction;
use Libraries\Validator as validator;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】銷售報表控制器
 */
class SaleReportController extends Controller
{
    /**
     * @var array 各單位總計
     */
    private $total = [];

    /**
     * @var array 全部小計
     */
    private $sumTotal = [
        'count' => 0,
        'total' => 0,
        'commission' => 0,
        'cash' => 0,
        'credit' => 0,
        'ATM' => 0,
    ];
	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

    /**
     * lists 銷售報表清單
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function lists() {}

    /**
     * info 銷售報表資訊
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function info($id) {}

	/**
	 * store 新增銷售報表
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function store() {}

    /**
     * update 修改銷售報表
     *
	 * @since 0.0.1
	 * @version 0.0.1
     * @param int $adminID 銷售報表id
     * @return string|array
     */
    public function update($id) {}

    /**
     * delete 刪除銷售報表
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
     * getSaleReport 取銷售報表
     *
     * @since 0.0.1
     * @version 0.0.1
     * @return string
     */
    public function getSaleReport() {
        //驗證權限
        $this->permission(['A'] , 'report/saleReport' , 'V');

        //驗證
        validator::make(request::$get , [
            'timeType' => 'required|in["year" , "month" , "day" , "season"]',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'orderStatus' => 'in["Y", "N", "U", "R", "P", "C"]',
        ]);

        //宣告
        $data = [];
        $orderModel = new OrderModel();

        //取訂單資料
        $orderData = $orderModel->getOrderByDateRange(request::$get);

        //判斷請求時間類型
        switch (request::$get['timeType']) {
            case 'year':
                $this->makeYearReport($orderData);
                break;
            case 'season':
                $this->makeSeasonReport($orderData);
                break;
            case 'month':
                $this->makeMonthReport($orderData);
                break;
            case 'day':
                $this->makeDayReport($orderData);
                break;
        }

        //累計加總資料
        $data['total'] = $this->total;
        $data['sumTotal'] = $this->sumTotal;

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

    /**
     * makeYearReport 製作年報表
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param array $data
     * @return string
     */
    private function makeYearReport($data) {
        foreach ($data as $key => $order) {
            // S ============== 各時間內區間計算 =================//
            $time = date('Y', strtotime($order['createTime']));

            if(!isset($this->total[$time])) {
                $this->total[$time] = [
                    'time' => $time,
                    'count' => 0,
                    'total' => 0,
                    'commission' => 0,

                    //@todo 未來要設計走支付模組
                    'cash' => 0,
                    'credit' => 0,
                    'ATM' => 0,
                ];
            }
            $this->total[$time]['count'] += 1;
            $this->total[$time]['total'] += $order['total'];

            //抽傭
            $this->total[$time]['commission'] += $order['commission'];

            //支付方式
            $this->total[$time][$order['paymentCode']] += 1;
            // E ============== 各時間內區間計算 =================//

            // S ============== 總額小計加總 =================//
            $this->sumTotal['count'] += 1;
            $this->sumTotal['total'] += $order['total'];
            $this->sumTotal['commission'] += $order['commission'];
            $this->sumTotal[$order['paymentCode']] += 1;
            // E ============== 總額小計加總 =================//
        }
    }

    /**
     * makeSeasonReport 製作季報表
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param array $data
     * @return string
     */
    private function makeSeasonReport($data) {
        //月份對應季報表
        $seasonArray = [
            '01' => 1,
            '02' => 1,
            '03' => 1,
            '04' => 2,
            '05' => 2,
            '06' => 2,
            '07' => 3,
            '08' => 3,
            '09' => 3,
            '10' => 4,
            '11' => 4,
            '12' => 4,
        ];

        foreach ($data as $key => $order) {
            // S ============== 各時間內區間計算 =================//
            $time = explode('-' , date('Y-m', strtotime($order['createTime'])));

            //進行時間轉季節
            $season = $time[0] . '-' . $seasonArray[$time[1]];
            if(!isset($this->total[$season])) {
                $this->total[$season] = [
                    'time' => $time[0] . '(' . sprintf(language::getFile()['common']['report']['season'] , $seasonArray[$time[1]]) . ')',
                    'count' => 0,
                    'total' => 0,
                    'commission' => 0,

                    //@todo 未來要設計走支付模組
                    'cash' => 0,
                    'credit' => 0,
                    'ATM' => 0,
                ];
            }
            $this->total[$season]['count'] += 1;
            $this->total[$season]['total'] += $order['total'];

            //抽傭
            $this->total[$season]['commission'] += $order['commission'];

            //支付方式
            $this->total[$season][$order['paymentCode']] += 1;
            // E ============== 各時間內區間計算 =================//

            // S ============== 總額小計加總 =================//
            $this->sumTotal['count'] += 1;
            $this->sumTotal['total'] += $order['total'];
            $this->sumTotal['commission'] += $order['commission'];
            $this->sumTotal[$order['paymentCode']] += 1;
            // E ============== 總額小計加總 =================//
        }
    }

    /**
     * makeMonthReport 製作月報表
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param array $data
     * @return string
     */
    private function makeMonthReport($data) {
        foreach ($data as $key => $order) {
            // S ============== 各時間內區間計算 =================//
            $time = date('Y-m', strtotime($order['createTime']));

            if(!isset($this->total[$time])) {
                $this->total[$time] = [
                    'time' => $time,
                    'count' => 0,
                    'total' => 0,
                    'commission' => 0,

                    //@todo 未來要設計走支付模組
                    'cash' => 0,
                    'credit' => 0,
                    'ATM' => 0,
                ];
            }
            $this->total[$time]['count'] += 1;
            $this->total[$time]['total'] += $order['total'];

            //抽傭
            $this->total[$time]['commission'] += $order['commission'];

            //支付方式
            $this->total[$time][$order['paymentCode']] += 1;
            // E ============== 各時間內區間計算 =================//

            // S ============== 總額小計加總 =================//
            $this->sumTotal['count'] += 1;
            $this->sumTotal['total'] += $order['total'];
            $this->sumTotal['commission'] += $order['commission'];
            $this->sumTotal[$order['paymentCode']] += 1;
            // E ============== 總額小計加總 =================//
        }
    }

    /**
     * makeDayReport 製作日報表
     *
     * @since 0.0.1
     * @version 0.0.1
     * @param array $data
     * @return string
     */
    private function makeDayReport($data) {
        foreach ($data as $key => $order) {
            // S ============== 各時間內區間計算 =================//
            $time = date('Y-m-d', strtotime($order['createTime']));

            if(!isset($this->total[$time])) {
                $this->total[$time] = [
                    'time' => $time,
                    'count' => 0,
                    'total' => 0,
                    'commission' => 0,

                    //@todo 未來要設計走支付模組
                    'cash' => 0,
                    'credit' => 0,
                    'ATM' => 0,
                ];
            }
            $this->total[$time]['count'] += 1;
            $this->total[$time]['total'] += $order['total'];

            //抽傭
            $this->total[$time]['commission'] += $order['commission'];

            //支付方式
            $this->total[$time][$order['paymentCode']] += 1;
            // E ============== 各時間內區間計算 =================//

            // S ============== 總額小計加總 =================//
            $this->sumTotal['count'] += 1;
            $this->sumTotal['total'] += $order['total'];
            $this->sumTotal['commission'] += $order['commission'];
            $this->sumTotal[$order['paymentCode']] += 1;
            // E ============== 總額小計加總 =================//
        }
    }
    //----------------------------------------------------------------
    // 附屬函示 API End
    //----------------------------------------------------------------
}

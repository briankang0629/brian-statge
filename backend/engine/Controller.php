<?php
/**
 * Controller
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2019/05/26
 * @since 0.1.0 2019/05/26
 */


use Libraries\PublicFunction as publicFunction;
use Libraries\Request as request;

/**
 * Class Controller
 * @package Controller
 */
abstract class Controller {

    /**
     * Controller constructor.
     *
	 * @since 0.0.1
	 * @version 0.0.1
     */
    public function __construct() {
        //判定身分
        switch(Identity) {
	        case 'admin':
		        $this->admin = new Libraries\Admin();//Admin
	            break;
	        case 'user':
		        $this->user = new Libraries\User();//User
		        break;
	        default:
	        	//error @todo
	        	break;
        }

    }

    /**
     * permission 權限驗證
     *
     * 參數說明 :
     *      路徑 $path menu/submenu
     *      身分 $identity = [S,A,U] S => 系統 , A => 管理者 , U => 會員使用者 | 順位 : S > A > U
     *      權限 $permission = [E,V] E => 要求寫入權限 , V => 要求查看權限, 同時允許查看權限, N => 無權限 | 順位 : E > V > N
     * @since 0.0.1
     * @version 0.0.1
     * @param array $identity
     * @param string $path
     * @param string $permission
     */
    protected function permission( $identify = [] , $path = '' , $permission = '' ) {
        //取得路徑
        $route = explode('/', $path);

        //驗證身份
        if(!defined('Identity')) {
            publicFunction::error('9999-2', '', 999);
        }

        //開始身份驗證
        switch (Identity) {
            case 'user':
                $class = 'U';
                break;
            case 'admin':
                $class = 'A';
                break;
            case 'super':
                $class = 'S';
                break;
            default:
                publicFunction::error('9999-3', '', 999);
                break;
        }

        //請求的身份未開放
        if(!in_array($class, $identify)) {
            publicFunction::error('9999-4', '', 999);
        }

        //管理者權限
        if(Identity == 'admin') {
            //E == 網站開放選單設定 =========================
            //後台選單model
            $systemMenuModel = new SystemMenuModel();

            //母選單
            if(!$menu = $systemMenuModel->getSystemMenuByCode($route[0])) {
                publicFunction::error('9999-6', '', 999);
            }

            //取子選單
            if(!$subMenu = $systemMenuModel->getSubMenuByParentId($menu['systemMenuId'])) {
                publicFunction::error('9999-7', '', 999);
            }

            //網站設定未開放指定子選單
            foreach ($subMenu as $key => $menu) {
                if($menu['code'] == $route[1]) {
                    break;
                }

                //最後一筆都沒有配對到 -> 網站未開放
                if($key === array_key_last($subMenu)) {
                    publicFunction::error('9999-8', '', 999);
                }
            }

            //E == 網站開放選單設定 =========================

            //S == 開始判定權限 ============================

            //無此路徑
            if(empty(Permission[$route[0]][$route[1]])) {
                publicFunction::error('9999-5', '', 999);
            }

            //無權限
            if(Permission[$route[0]][$route[1]] == 'N') {
                publicFunction::error('9998-1', '', 998);
            }

            //要求寫入權限 卻只有閱讀權限
            if(($permission == 'E') && (Permission[$route[0]][$route[1]] == 'V')) {
                publicFunction::error('9998-2', '', 998);
            }
            //E == 開始判定權限 ============================
        }
    }

	/**
	 * writeLog 寫操作記錄日誌
	 *
	 * @since 0.1.0
	 * @param int $logId 操作類型ID
	 * @param array $content 操作者內容
	 * @param string|array $sql SQL語法
	 * @todo 需改版SQL
	 */
	protected function writeLog( $logId , $content = [] , $sql = '' ) {
		//宣告
		$logRecordModel = new LogRecordModel();

		//S == 相關設定資訊設定
		//sever 資訊
		$serverInfo = json_encode(request::$server);

		//處理sql @todo 尚未處理多句sql
		if(is_array($sql)) {
			$sql = join(";" , $sql);
		}

		$sql = str_replace("'" , "" , $sql);

		//處理PDO bind SQL
		if($content) {
			foreach($content as $field => $value) {
				$sql = str_replace(":" . $field , $value , $sql);
			}
		}

		//記錄內容
		$content = json_encode($content);

		//使用者ID -> 判定身分
		switch(Identity) {
			case 'user':
				$class = 'U';
				break;
			case 'admin':
				$class = 'A';
				break;
			case 'super':
				$class = 'S';
				break;
		}

		//操作帳號判定
		$account = defined('Account') ? Account : 'system';

		//E == 相關設定資訊設定

		//傳送參數
		$sentData = [
			'logId' => $logId ,
			'adminId' => defined('AdminID') ? AdminID : 0 ,
			'userId' => defined('UserID') ? UserID : 0 ,
			'account' => $account ,
			'class' => $class ,
			'remoteIP' => publicFunction::getIP()['REMOTE_ADDR'] ,
			'serverAddr' => publicFunction::getIP()['SERVER_ADDR'] ,
			'serverInfo' => $serverInfo ,
			'host' => request::$server['HTTP_REQUEST_HOST'] ,
			'content' => $content ,
			'path' => request::$server['REQUEST_URI'] ,
			'sqlString' => $sql ,
			'newDate' => date('Y-m-d') ,
			'createTime' => date('Y-m-d H:i:s') ,
		];

		$logRecordModel->store($sentData);
	}
}

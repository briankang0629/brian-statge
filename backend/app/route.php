<?php
/**
 * Route
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2019-05-11
 */

$router = new \Bramus\Router\Router();

/**
 * 管理端後台用
 */
$router->mount('/api', function () use ($router) {
    //common
    $router->get('/auth', 'LoginController@authenticate');
    $router->get('/auth/google', 'LoginController@authenticateGoogle');
    $router->post('/login', 'LoginController@login');
    $router->post('/login/google', 'LoginController@loginGoogle');
    $router->post('/logout', 'LoginController@logout');

    //user
    $router->mount('/user', function () use ($router) {
        //基本五大接口
        $router->get('/', 'UserController@lists');
        $router->get('/(\d+)', 'UserController@info');
        $router->post('/store', 'UserController@store');
        $router->put('/update/(\d+)', 'UserController@update');
	    $router->delete('/([\d+,]*)', 'UserController@delete');

        //銷售總額 @todo此功能屬於首頁數據模組
        $router->get('/report/total', 'UserController@getUserTotal');
    });

    //userGroup
    $router->mount('/userGroup', function () use ($router) {
        //基本五大接口
        $router->get('/', 'UserGroupController@lists');
        $router->get('/(\d+)', 'UserGroupController@info');
        $router->post('/store', 'UserGroupController@store');
        $router->put('/update/(\d+)', 'UserGroupController@update');
        $router->delete('/([\d+,]*)', 'UserGroupController@delete');
    });

    //user
    $router->mount('/admin', function () use ($router) {
        //基本五大接口
        $router->get('/', 'AdminController@lists');
        $router->get('/(\d+)', 'AdminController@info');
        $router->post('/store', 'AdminController@store');
        $router->put('/update/(\d+)', 'AdminController@update');
        $router->delete('/([\d+,]*)', 'AdminController@delete');
    });

    //permission
    $router->mount('/permission', function () use ($router) {
        //基本五大接口
        $router->get('/', 'PermissionController@lists');
        $router->get('/(\d+)', 'PermissionController@info');
        $router->post('/store', 'PermissionController@store');
        $router->put('/update/(\d+)', 'PermissionController@update');
        $router->delete('/([\d+,]*)', 'PermissionController@delete');
        //系統預設權限列表
        $router->get('/config', 'PermissionController@getPermissionConfig');
    });

    //product
    $router->mount('/product', function () use ($router) {
        //基本五大接口
        $router->get('/', 'ProductController@lists');
        $router->get('/(\d+)', 'ProductController@info');
        $router->post('/store', 'ProductController@store');
        $router->put('/update/(\d+)', 'ProductController@update');
        $router->delete('/([\d+,]*)', 'ProductController@delete');

        //S ============== 客製化 ================ //
        //取商品觀看數@todo此功能屬於首頁數據模組
        $router->get('/view', 'ProductController@getProductViewed');

        //取商品熱銷排行
        $router->get('/sale/hot', 'ProductController@getSaleHotProduct');

        //依商品分類取商品
        $router->get('/getProductByCategory/(\d+)', 'ProductController@getProductByCategory');

        //會員端依商品ID取商品
        $router->get('/getProduct/(\d+)', 'ProductController@getProduct');

        //E ============== 客製化 ================ //

        //category
        $router->mount('/category', function () use ($router) {
            //基本五大接口
            $router->get('/', 'ProductCategoryController@lists');
            $router->get('/(\d+)', 'ProductCategoryController@info');
            $router->post('/store', 'ProductCategoryController@store');
            $router->put('/update/(\d+)', 'ProductCategoryController@update');
            $router->delete('/([\d+,]*)', 'ProductCategoryController@delete');

            //S ============== 客製化 ================ //
            //取客戶端的商品分類列表
	        $router->get('/getProductCategories', 'ProductCategoryController@getProductCategories');

	        //會員端取指定商品分類
	        $router->get('/getProductCategory/(\d+)', 'ProductCategoryController@getProductCategory');

	        //會員端取商品分類家族數
            $router->get('/tree/(\d+)', 'ProductCategoryController@getProductCategoryTree');

            //E ============== 客製化 ================ //
        });

	    //option
	    $router->mount('/option', function () use ($router) {
		    //基本五大接口
		    $router->get('/', 'ProductOptionController@lists');
		    $router->get('/(\d+)', 'ProductOptionController@info');
		    $router->post('/store', 'ProductOptionController@store');
		    $router->put('/update/(\d+)', 'ProductOptionController@update');
		    $router->delete('/([\d+,]*)', 'ProductOptionController@delete');
		    $router->delete('/value/([\d+,]*)', 'ProductOptionController@deleteProductOptionValue');
	    });

	    //specification
	    $router->mount('/specification', function () use ($router) {
		    //基本五大接口
		    $router->get('/', 'ProductSpecificationController@lists');
		    $router->get('/(\d+)', 'ProductSpecificationController@info');
		    $router->post('/store', 'ProductSpecificationController@store');
		    $router->put('/update/(\d+)', 'ProductSpecificationController@update');
		    $router->delete('/([\d+,]*)', 'ProductSpecificationController@delete');
	    });
    });

	//logRecord
	$router->mount('/logRecord', function () use ($router) {
		//基本接口
		$router->get('/', 'LogRecordController@lists');
		$router->get('/(\d+)', 'LogRecordController@info');
		$router->get('/getLogRecordSetting', 'LogRecordController@getLogRecordSetting');
	});

	//system
	$router->mount('/systemMenu', function () use ($router) {
		//基本接口
		$router->get('/config', 'SystemMenuController@config');
	});

	//system setting
	$router->mount('/systemSetting', function () use ($router) {
		//基本接口
		$router->get('/config', 'SystemSettingController@config');
	});

	//media 多媒體
	$router->mount('/media', function () use ($router) {
		//圖片
		$router->mount('/image', function () use ($router) {
			//基本接口
			$router->get('/', 'ImageController@lists');
			$router->get('/(\d+)', 'ImageController@info');
			$router->post('/store', 'ImageController@store');
			$router->delete('/([\d+,]*)', 'ImageController@delete');

			//取設定
			$router->get('/setting', 'ImageController@setting');

			//創建資料夾
            $router->post('/create/folder', 'ImageController@createFolder');
		});

	});

    //order
    $router->mount('/order', function () use ($router) {
        //月報表（線圖）@todo此功能屬於首頁數據模組
        $router->get('/report/groupBy/month', 'OrderController@getOrderGroupByMonth');
        //訂單總量 @todo此功能屬於首頁數據模組
        $router->get('/report/total', 'OrderController@getOrderTotal');
        //銷售總額 @todo此功能屬於首頁數據模組
        $router->get('/report/sale', 'OrderController@getOrderSale');
    });


    //report 報表
    $router->mount('/report', function () use ($router) {
        //月報表（線圖）@todo此功能屬於首頁數據模組
        $router->get('/saleReport', 'SaleReportController@getSaleReport');
    });
});

//middleware
$router->before('GET|POST|PUT|DELETE', '/.*', function () use ($router) {

	//驗證白名單
	$whitelistMiddleware = new WhiteListMiddleware();
	$whitelistMiddleware->handle($router);

	//驗證登入
	$authMiddleware = new AuthMiddleware();
	$authMiddleware->handle($router);

});

$router->run();

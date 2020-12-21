<?php
/**
 * Route
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date: 2019-05-11
 * @since 0.1.0
 */

$router = new \Bramus\Router\Router;

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

        //category
        $router->mount('/category', function () use ($router) {
            //基本五大接口
            $router->get('/', 'ProductCategoryController@lists');
            $router->get('/(\d+)', 'ProductCategoryController@info');
            $router->post('/store', 'ProductCategoryController@store');
            $router->put('/update/(\d+)', 'ProductCategoryController@update');
            $router->delete('/([\d+,]*)', 'ProductCategoryController@delete');

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


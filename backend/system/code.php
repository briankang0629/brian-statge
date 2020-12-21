<?php
/**
 * 系統設定參數
 *
 * @version 1.0.0
 * @author Brian
 * @date 2020/11/17
 * @since 0.1.0 2020/11/17
 */

return [
	/**
	 * 權限控管設定檔
	 * 權限 $permission = [E,V] E => 要求寫入權限 , V => 要求查看權限, 同時允許查看權限, N => 無權限 | 順位 : E > V > N
	 */
    'permission' => [
        'admin' => [
            'adminList' => 'N',
            'permission' => 'N',
        ],
        'user' => [
            'userList' => 'N',
            'userGroup' => 'N',
        ],
        'product' => [
            'productList' => 'N',
            'productCategory' => 'N',
            'productOption' => 'N',
            'productSpecification' => 'N',
        ],
        'stock' => [
            'stockCategory' => 'N',
        ],
        'report' => [],
        'order' => [
            'orderList' => 'N',
            'orderStatus' => 'N',
        ],
        'system' => [
            'system' => 'N',
        ],
        'module' => [
            'moduleList' => 'N',
        ],
        'media' => [
            'image' => 'N',
        ],
        'logRecord' => [
            'logRecordList' => 'N',
        ],
    ],
	/**
	 * 操作記錄設定檔
	 * 操作介面 S=System(系統)、A=Admin(管理者)、U=User(會員)
	 */
	'logRecordSetting' => [
		1 => ['type' => 'A', 'class' => 'createUser'],
		2 => ['type' => 'A', 'class' => 'updateUser'],
		3 => ['type' => 'A', 'class' => 'deleteUser'],
		4 => ['type' => 'A', 'class' => 'createAdmin'],
		5 => ['type' => 'A', 'class' => 'updateAdmin'],
		6 => ['type' => 'A', 'class' => 'deleteAdmin'],
		7 => ['type' => 'A', 'class' => 'createPermission'],
		8 => ['type' => 'A', 'class' => 'updatePermission'],
		9 => ['type' => 'A', 'class' => 'deletePermission'],
		10 => ['type' => 'A', 'class' => 'adminLogin'],
		11 => ['type' => 'U', 'class' => 'userLogin'],
		12 => ['type' => 'A', 'class' => 'adminLogout'],
		13 => ['type' => 'U', 'class' => 'userLogout'],
		14 => ['type' => 'A', 'class' => 'uploadImage'],
		15 => ['type' => 'A', 'class' => 'deleteImage'],
		16 => ['type' => 'A', 'class' => 'createImageFolder'],
		17 => ['type' => 'A', 'class' => 'createProduct'],
		18 => ['type' => 'A', 'class' => 'updateProduct'],
		19 => ['type' => 'A', 'class' => 'deleteProduct'],
	],
	//系統預設語系
	'defaultLanguage' => 'zh-tw',
	//系統開放語系
	'language' => [
		'zh-tw',
		'en-us'
	],
];

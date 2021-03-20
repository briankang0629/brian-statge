<?php
/**
 * ImageController 圖片控制器
 *
 * @since 0.0.1
 * @version 0.0.1
 * @author Brian
 * @date 2020/12/09
 */

use Libraries\PublicFunction as publicFunction;
use Libraries\Validator as validator;
use Libraries\Language as language;
use Libraries\Request as request;

/**
 * 【Controller】圖片控制器
 */
class ImageController extends Controller {

	//----------------------------------------------------------------
	//Region API
	//----------------------------------------------------------------

	/**
	 * lists 圖片清單
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	public function lists() {
		//驗證權限
		$this->permission(['A'] , 'media/image' , 'V');

		//宣告
		$data = [];
		$mediaModel = new MediaModel();

		//取所有圖片
		$imageDatas = $mediaModel->lists(request::$get);

		//依照資料夾將圖片做分類
		foreach($imageDatas as $image) {
			$image['url'] = IMAGE_URL . $image['folder'] . '/' . $image['extension'] .  '/' . $image['fileName'] . '.' . $image['extension'];
			$data[$image['folder']][] = $image;
		}

		//取圖片列表
		return publicFunction::json([
			'data' => $data
		] , 'success');
	}

	/**
	 * info 圖片資訊
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 */
	public function info( $id ) {
		//驗證權限
		$this->permission(['A'] , 'media/image' , 'V');

		//宣告
		$mediaModel = new MediaModel();

		//取圖片資料
		if(!$image = $mediaModel->info($id)) { return publicFunction::emptyOutput(); }

		//回傳
		return publicFunction::json([
			'data' => $image
		] , 'success');
	}

	/**
	 * store 新增圖片
	 *
	 * @since 0.1.0
	 * @return boolean
	 */
	public function store() {
		//驗證權限
		$this->permission(['A'] , 'media/image' , 'E');

		//宣告
		$mediaModel = new MediaModel();

		//規則
		$require = [
			'folder' => 'string' ,
		];

		//允許的副檔名
		$filterExtension = ['jpg' , 'jpeg' , 'png' , 'gif' , 'heif'];

		//驗證
        $folder = isset(request::$server['HTTP_FOLDER']) ? request::$server['HTTP_FOLDER'] : 'default';
		validator::make(['folder' => $folder], $require);

		//先取得附檔名
		$extension = strtolower(pathinfo(request::$files["file"]["name"] , PATHINFO_EXTENSION));

		//判斷檔案格式
		if(!in_array($extension , $filterExtension)) {
			publicFunction::error('0200' , sprintf(language::getFile()['error']['0200'] , $extension));
		}

		//判斷有無資料夾
		if($folder != '') {
			//資料夾類型
			$categoryFolder = IMAGE_PATH . $folder; //路徑需修改 => 資料夾名稱
			if(!file_exists($categoryFolder)) {
				mkdir($categoryFolder , 0777);
			}

			//副檔名資料夾
			$extensionFolder = IMAGE_PATH . $folder . '/' . $extension; //路徑需修改 => 副檔名
			if(!file_exists($extensionFolder)) {
				mkdir($extensionFolder , 0777);
			}
		}

		//組成檔名
		$randomName = publicFunction::token();
		$fileName = $randomName . '.' . $extension;

		// 取得圖片大小
		$imageInfo = getimagesize(request::$files["file"]["tmp_name"]);

		try {
			if(!move_uploaded_file(request::$files["file"]["tmp_name"] , IMAGE_PATH . $folder . '/' . $extension . '/' . $fileName)) {
				// 判斷錯誤狀況
				switch(request::$files["file"]['error']) {
					case 1:
					case 2:
						throw new Exception('exceed max size');
						break;
					case 3:
					case 4:
					case 6:
					case 7:
					case 8:
					default:
						throw new Exception('uploads failed');
						break;
				}
			}
		} catch(Exception $error) {
			publicFunction::error('0201' , sprintf(language::getFile()['error']['0201'] , $error->getMessage()));
		}

		//傳送參數
		$sentData = [
			'fileName' => $randomName ,
			'originName' => request::$files["file"]['name'] ,
			'type' => 'image' ,
			'extension' => $extension ,
			'size' => request::$files['file']['size'] ,
			'height' => $imageInfo[1] ,// 取得圖片寬度
			'width' => $imageInfo[0] , // 取得圖片高度
			'folder' => $folder ,
			'createTime' => date('Y-m-d H:i:s') ,
		];

		//執行更新
		$mediaModel->store($sentData);

		//上傳ID
		$uploadId = $mediaModel->db->getLastId();

		//操作記錄
		$this->writeLog(14 , $sentData , $mediaModel->db->getSql());

		//回傳
		publicFunction::json([
            'status' => 'success' ,
            'msg' => language::getFile()['common']['upload']['success'] ,
			'uploadId' => $uploadId,
            'picture' => IMAGE_URL . $folder . '/' . $extension . '/' . $fileName
		]);
	}

	/**
	 * update 修改圖片
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param int $imageId 圖片id
	 * @return string|array
	 */
	public function update( $id ) {
	}

	/**
	 * delete 刪除圖片
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @param $id
	 * @return string
	 */
	public function delete( $id ) {
		//驗證權限
		$this->permission(['A'] , 'media/image' , 'E');

		//宣告
		$mediaModel = new MediaModel();

		//取要刪除得圖片
        $filed = ['uploadId' , 'fileName' , 'type' , 'extension' , 'folder'];
		if(!$uploadImages = $mediaModel->items( $mediaModel->table, $filed, ['uploadId', 'IN', '( ' . $id . ' )'])) {
            publicFunction::error('0203');
        }

		//transaction sql
		try {
		    //宣告交易語法
			$mediaModel->db->beginTransaction();

			//執行刪除
			$mediaModel->delete(['uploadId', 'IN', '( ' . $id . ' )']);

			//寫遭操作紀錄
			$this->writeLog(15 , [] , $mediaModel->db->getSql());

            //刪除圖片
            foreach ($uploadImages as $key => $image) {
                if(!unlink(IMAGE_PATH . $image['folder'] . '/' . $image['extension'] . '/' . $image['fileName'] . '.' . $image['extension'] )) {
                    throw new Exception(language::getFile()['common']['delete']['failed'], '0202');
                }
            }

			//commit
			$mediaModel->db->commit();

		} catch(exception $exception) {
            //rollback
			$mediaModel->db->rollback();

			//回傳錯誤訊息
            publicFunction::error($exception->getCode(), $exception->getMessage());
		}

        //回傳
        publicFunction::json([
            'status' => 'success' ,
            'msg' => language::getFile()['common']['delete']['success'] ,
        ]);

	}
	//----------------------------------------------------------------
	//EndRegion API
	//----------------------------------------------------------------

	//S == 客製化區塊 ========================================//

	/**
	 * setting 取上傳圖片設定
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return string
	 */
	public function setting() {
		//驗證權限
		$this->permission(['A'] , 'media/image' , 'V');

		//宣告
		$mediaModel = new MediaModel();

		//取圖片資料夾
		return publicFunction::json([
			'data' => $mediaModel->getMediaFolder('image')
		] , 'success');
	}

	/**
	 * createFolder 新增資料夾
	 *
	 * @since 0.0.1
	 * @version 0.0.1
	 * @return string
	 */
	public function createFolder() {
		//驗證權限
		$this->permission(['A'] , 'media/image' , 'E');

		//宣告
		$mediaModel = new MediaModel();

		//規則
		$require = [
			'code' => 'required|string|lenMax:32',
			'name' => 'required|string|lenMax:32',
		];

		//驗證
		validator::make(request::$post , $require);

		//檢查帳號有無重覆
		if($mediaModel->getMediaFolderInfo('image', request::$post['code'])['data']) {
			publicFunction::error('0204');
		}

		//傳送參數
		$sentData = [
			'type' => 'image',
			'code' => request::$post['code'],
			'name' => request::$post['name'],
			'createTime' => date('Y-m-d H:i:s'),
		];

		//執行寫入
		$mediaModel->storeFolder($sentData);

		//操作記錄
		$this->writeLog(16 , $sentData , $mediaModel->db->getSql());

		//回傳
		publicFunction::json([
			'status' => 'success' ,
			'msg' => language::getFile()['common']['create']['success'] ,
		]);
	}
	//E == 客製化區塊 ========================================//
}

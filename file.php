<?php 

class File {

	private $_dir;

	const EXT = '.txt'; // kuozhanming 

	/**
	* 构造方法
	* dirname(__FILE__) 当前路径
	*/
	public function __construct() {
		$this->_dir = dirname(__FILE__).'/files/';
	}

	public function cacheData($key, $value = '', $cacheTime = 0) {
		$filename = $this->_dir . $key . self::EXT;

		if ($value !== '') { // 将value值写入缓存
			if (is_null($value)) {
				return @unlink($filename); // 删除文件
			}
			$dir = dirname($filename); // 获取文件目录
			if (!is_dir($dir)) {
				mkdir($dir, 0777);
			}

			$cacheTime = sprintf('%011d', $cacheTime);
			return file_put_contents($filename, $cacheTime . json_encode($value));
		}

		// 获取文件内容
		if (!is_file($filename)) { // 如果文件不存在
			return FALSE;
		} 

		// 获取文件总内容
		$contents = file_get_contents($filename);
		// 获取文件缓存时长
		$cacheTime = (int)substr($contents, 0, 11);


		// 获取文件有效内容
		$value = substr($contents, 11);

		// 文件是否有效判断 上次访问时间
		if ($cacheTime != 0 && $cacheTime + fileatime($filename) < time()) { // 如果缓存时长+文件创建时间 < 当前时间，则文件无效
			unlink($filename); // 删除文件
			return FALSE;
		}
		return json_decode($value, true);
	}

	/**
	 * 移除文件
	 */
	public function removeCache($key) {
		$filename = $this->_dir . $key . self::EXT;

		if (is_file($filename)) {
			unlink($filename);
			return true;
		} else {
			return false;
		}
	}
}

// $file = new File();

// $file->cacheData('test1');

 ?>
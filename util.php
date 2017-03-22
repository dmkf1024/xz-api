<?php 
class Util {

	/**
	 * 生成六位随机数作为token
	 */
	static public function genToken() {
		return mt_rand(100000, 999999);
	}

	/**
	 * 验证token是否有效
	 */
	static public function isTokenValid($token, $id) {
		// 验证token是否有效
		$file = new File();
		// 获取token缓存文件的内容
		$data = $file->cacheData('token_' . $id);

		// 如果缓存文件不存在，说明未处于登录状态
		if ($data == null) {
			Response::show(418, '未处于登录状态或因长时间操作，退出登录');
			return false;
		}

		// 如果输入的token与获取的token一直
		if ($data == $token) {
			return true;
		}
		// 如果不一致
		Response::show(419, '无效的token');
		return false;
	}

	/**
	 * 退出登录操作
	 */
	static public function logout($token, $id) {
		// 声明一个文件
		$file = new File();
		// 文件名
		$fileName = 'token_' . $id;
		// 获取token缓存文件的内容
		$data = $file->cacheData($fileName);

		// 如果缓存文件不存在，说明未处于登录状态
		if ($data == null) {
			Response::show(418, '未处于登录状态');
		}

		// 如果输入的token与获取的token一直
		if ($data == $token) {
			if ($file->removeCache($fileName)) { // 如果token有效
				Response::show(200, "退出登录成功");
			} else { // 如果token已过时
				Response::show(418, '未处于登录状态');
			}
		} else {
			// 如果不一致
			Response::show(419, '无效的token');
		}
	}
}

 ?>
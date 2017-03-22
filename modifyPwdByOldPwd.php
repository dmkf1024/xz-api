<?php 

require_once('./DB.php');
require_once('./response.php');
require_once('./file.php');
require_once('./util.php');

$id = $_GET['id'];
$oldPwd = $_GET['old_pwd'];
$newPwd = $_GET['new_pwd'];
$token = $_GET['token'];

// 验证上传的数据是否合法
if (!isset($id) || !isset($oldPwd) || !isset($newPwd) || !isset($token)) {
	Response::show(410, '请求的数据不合法');
}

// 验证token是否有效
if (Util::isTokenValid($token, $id) == false) {
	return;
}

// 修改密码的sql语句
$sql = "update person set password = '" . $newPwd . "' where id = '" . $id . "'";

try {
	// 连接数据库
	$connect = DB::getInstance()->connect();
} catch (Exception $e) {
	// 如果连接数据库失败，在界面中显示约定好的报错信息
	return Response::show(411, "连接数据库失败");
}

// 获取修改信息的结果
$result = mysql_query($sql, $connect);
if ($result == 1) {
	return Response::show(200, "修改密码成功");
} else {
	return Response::show(416, "修改密码失败，数据库中存在相同的id，请联系数据库管理人员核查数据库");
}

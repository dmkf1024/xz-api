<?php 
/**
 * 
 */
require_once('./response.php');
require_once('./DB.php');
require_once('./util.php');
require_once('./file.php');

$id = $_GET['id'];
$token = $_GET['token'];
$email = $_GET['email'];

if (!isset($id) || !isset($token) || !isset($email)) {
	Response::show(410, "请求的数据不合法");
}

try {
	// 连接数据库
	$connect = DB::getInstance()->connect();
} catch (Exception $e) {
	// 如果连接数据库失败，在界面中显示约定好的报错信息
	return Response::show(411, "连接数据库失败");
}

// 验证token是否有效
if (Util::isTokenValid($token, $id) == false) {
	return;
}

// 修改email的sql语句
$sql = "update person set email = '" . $email . "' where id = '" . $id . "'";

// 获取修改信息的结果
$result = mysql_query($sql, $connect);
if ($result > 0) {
	return Response::show(200, "邮箱修改成功");
}
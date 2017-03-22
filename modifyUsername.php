<?php

require_once('./util.php');
require_once('./file.php');
require_once('./response.php');
require_once('./DB.php');

$id = $_GET['id'];
$token = $_GET['token'];
$username = $_GET['username'];

// 如果传入的数据不完整
if (!isset($id) || !isset($token) || !isset($username)) {
	Response::show(410, "请求的数据不合法");
}

// 验证token是否有效
if (!Util::isTokenValid($token, $id)) {
	return;
}

try {
	// 连接数据库
	$connect = DB::getInstance()->connect();
} catch (Exception $e) {
	// 如果连接数据库失败，在界面中显示约定好的报错信息
	return Response::show(411, "连接数据库失败");
}

// 修改用户名sql语句
$sql = "UPDATE person SET username = '" . $username . "' WHERE id = " . $id;

// 执行sql语句
$result = mysql_query($sql);
if ($result) {
	Response::show(200, "修改用户名成功");
} else {
	Response::show(416, "修改用户名失败");
}
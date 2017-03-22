<?php
require_once('./DB.php');
require_once('./response.php');
require_once('./file.php');
require_once('./util.php');

$id = $_GET['id'];
$token = $_GET['token'];
$prjType = $_GET['project_type'];
$figure = $_GET['figure'];

if (!isset($id) || !isset($token) || !isset($prjType) || !isset($figure)) { // 如果传入的参数缺少
	Response::show(410, "请求的数据不合法");
}

// 验证token是否存在或有效
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

// 资金表插入新资金记录的sql语句
$sql = "INSERT INTO funds (figure, intention_project, person) VALUES ('" . $figure . "', " . $prjType . ", " . $id . ")";

// 执行插入语句
$result = mysql_query($sql, $connect);

if ($result == true) {
	Response::show(200, "发布资金成功");
} else {
	Response::show(413, "发布资金失败");
}
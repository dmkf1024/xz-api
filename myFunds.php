<?php
require_once('./DB.php');
require_once('./file.php');
require_once('./util.php');
require_once('./response.php');

$id = $_GET['id'];
$token = $_GET['token'];

$output = array();

if (!isset($id) || !isset($token)) {  // 如果传入参数不完整
	Response::show(410, "请求的数据不合法");
}

if (!Util::isTokenValid($token, $id)) { // 验证token是否存在或有效
	return;
}

try {
	// 连接数据库
	$connect = DB::getInstance()->connect();
} catch (Exception $e) {
	// 如果连接数据库失败，在界面中显示约定好的报错信息
	return Response::show(411, "连接数据库失败");
}

// 查询资金表的sql语句
$sql = "SELECT intention_project AS type_id, figure, funds_date FROM funds WHERE person = " . $id;

// 执行查询语句
$result = mysql_query($sql, $connect);
// 返回查询结果
while ($funds = mysql_fetch_assoc($result)) {
	// 获取相关投资类的名称的sql语句
	$sql1 = "SELECT project_type FROM project_type WHERE id = " . $funds['type_id'];
	// 执行查询语句
	$r1 = mysql_query($sql1);
	// 返回执行结果
	$typeName = mysql_fetch_assoc($r1);
	// 将类型名放入资金对象中
	$funds['project_type'] = $typeName['project_type'];
	// 将资金对象放入输出数组中
	$output[] = $funds;
}

if ($output == null) {
	Response::show(420, "数据库表无相关记录");
} else {
	Response::show(200, "查询成功", $output);
}
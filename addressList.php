<?php
require_once('./DB.php');
require_once('./response.php');

$output = array();

$pid = isset($_GET['pid'])?$_GET['pid']:1;

try {
	// 连接数据库
	$connect = DB::getInstance()->connect();
} catch (Exception $e) {
	// 如果连接数据库失败，在界面中显示约定好的报错信息
	return Response::show(411, "连接数据库失败");
}

// 查询当前国家的所有项目
$sql = "SELECT id, value AS name FROM address WHERE pid = " . $pid;
// 执行查询语句
$result = mysql_query($sql, $connect);

while ($province = mysql_fetch_assoc($result)) {
	// 获取城市名和ID
	$output[] = $province;
}

if ($output == null) {
	Response::show(420, "数据库表无相关记录");
} else {
	Response::show(200, "查询成功", $output);
}



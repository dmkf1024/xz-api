<?php
require_once('./response.php');
require_once('./file.php');
require_once('./util.php'); 
require_once('./DB.php');

$id = $_GET['id'];
$token = $_GET['token'];

$output = array();

// 验证输入
if (!isset($id) || !isset($token)) {
	Response::show(410, "请求的数据不合法");
}

// 验证token是否有效
if (!Util::isTokenValid($token, $id)) {
	return;
}

// 连接数据库
try {
	$connect = DB::getInstance()->connect();
} catch(Exception $e) {
	// 如果连接数据库失败，返回错误信息
	return Response::show(411, "连接数据库失败");
}

// 查询通知语句
$sql =  "SELECT * FROM project_introduction WHERE to_person = " . $id;

// 返回查询的结果
$result = mysql_query($sql, $connect);

// 将结果解析放入显示的数组中
while ($notify = mysql_fetch_assoc($result)) {
	// 获取消息发起人的id
	$fromId = $notify['from_person'];
	// 获取消息发起人头像sql语句
	$sql1 = "SELECT picture FROM person WHERE id = " . $fromId;
	$r1 = mysql_query($sql1);
	$person = mysql_fetch_assoc($r1);
	$notify['avatar'] = $person['picture'];
	$output[] = $notify;
}
// 将结果返回到网页中
if ($output == null) { // 如果没有查询到数据
	Response::show(420, "数据库表无相关记录");
} else { // 如果发寻到数据
	Response::show(200, "查询成功", $output);
}



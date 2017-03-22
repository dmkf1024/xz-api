<?php 

require_once('./response.php');
require_once('./DB.php');

$output = array();

// 接受参数
$mobile = $_GET['mobile'];

if (!isset($mobile)) {
	Response::show(410, "请求的数据不合法");
}

try {
	// 连接数据库
	$connect = DB::getInstance()->connect();
} catch (Exception $e) {
	// 如果连接数据库失败，在界面中显示约定好的报错信息
	return Response::show(411, "连接数据库失败");
}

// 判断手机号有没有被注册
if (DB::isExist($connect, 'person', 'mobile', $mobile)) {
	return Response::show(414, "该手机号已被注册");
} else {
	return Response::show(200, "该手机号未被注册");
}



?>
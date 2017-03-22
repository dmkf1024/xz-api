<?php 
/**
 * 通过向手机发送验证码的形式认证用户来修改密码
 */
require_once('./response.php');
require_once('./DB.php');

$output = array();

// 接受参数
$mobile = $_GET['mobile'];
$password = $_GET['password'];

if (!isset($password) || !isset($mobile)) {
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
$exist = DB::isExist($connect, 'person', 'mobile', $mobile);
if ($exist == 0) {
	return Response::show(414, "该手机号未被注册");
} else if ($exist > 1) {
	return Response::show(416, "密码修改失败，原因为数据库中有不同用户绑定了相同的手机号，请联系管理员进行核查");
}

// 数据库查询语句，插叙对应手机号的用户
$sql = "update person set password = '" . $password . "' where mobile = '" . $mobile . "'";

// 获取修改信息的结果
$result = mysql_query($sql, $connect);
if ($result == 1) { // 修改了一条数据
	return Response::show(200, "密码修改成功");
} else {
	return Response::show(416, "密码修改失败");
}

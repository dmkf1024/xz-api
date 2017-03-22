<?php 

require_once('./response.php');
require_once('./DB.php');

$output = array();

// 接受参数
$name = $_GET['name'];
$idCard = $_GET['id_card'];
$userName = $_GET['username'];
$password = $_GET['password'];
$mobile = $_GET['mobile'];
$idMark = $_GET['identity_mark'];

if (!isset($name) || !isset($idCard) || !isset($userName) || !isset($password) || !isset($mobile) || !isset($idMark)) {
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
}

// 判断身份证号有没有被注册
if (DB::isExist($connect, 'person', 'identity_card', $idCard)) {
	return Response::show(414, "该身份证号已被注册");
}

// 往数据库中插入新注册的用户的信息的语句
$sql = "insert into person (name, identity_card, username, password, mobile, identity_mark) values ('" . $name . "', '" . $idCard . "', '" . $userName . "', '" . $password . "', '" . $mobile . "', '" . $idMark . "')";

// 获取数据库插入的结果，true:成功，false:失败
$result = mysql_query($sql, $connect);

if ($result) { // 如果数据成功插入
	$output['registerSuccess'] = true;
} else { // 如果数据插入失败
	$output['registerSuccess'] = false;
	return Response::show(413, "插入数据失败", $output);
}

// 数据库查询语句
$sql = "select id, name, identity_card, username, mobile, identity_mark from person where identity_card = " . $idCard;

// 如果插入数据成功，获取插入的数据
$result = mysql_query($sql, $connect);

// 将从数据库中获取的数据放入$videos中
while ($person = mysql_fetch_assoc($result)) {
	$output['registerSuccess'] = true;
	$output['persons'] = $person;
}

if ($output['persons']) { // 如果获取到新增用户的信息
	return Response::show(200, "用户注册成功", $output);
} else { // 如果未获取到新增用户的信息
	return Response::show(414, "用户注册成功，但获取用户数据失败", $output);
}

 ?>
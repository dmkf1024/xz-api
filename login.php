<?php 

require_once('./response.php');
require_once('./file.php');
require_once('./DB.php');
require_once('./util.php');

$output = array();

$mobile = $_GET['mobile'];
$password = $_GET['password'];

// 如果手机号或密码未输入
if (!isset($mobile) || !isset($password)) {
	Response::show(410, '请求的数据不合法');
}

// 查询语句
$sql = "select id from person where mobile = " . $mobile . " and password = " . $password;

try {
	// 连接数据库
	$connect = DB::getInstance()->connect();
} catch (Exception $e) {
	// 如果连接数据库失败，在界面中显示约定好的报错信息
	return Response::show(411, "连接数据库失败");
}

// 数据库查询的结果
$result = mysql_query($sql, $connect);

// 将从数据库中获取的数据放入$person中
while ($person = mysql_fetch_assoc($result)) {
	// 生成token
	$token = Util::genToken();
	$output['token'] = $token;
	$output['persons'] = $person;
	// 获取id
	$id = $person['id'];
}

if ($output) {
	// --将token存到缓存文件中
	// 声明缓存文件
	$cache = new File();
	// 缓存文件名称：token_手机号
	$filename = "token_" . $id;
	if ($cache->cacheData($filename, $token,  4 * 60 * 60)) {
		return Response::show(200, "获取数据成功", $output);
	} else {
		return Response::show(417, "缓存数据失败");
	}
} else {
	return Response::show(414, "获取数据失败");
}

?>
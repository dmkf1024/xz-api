<?php
require_once('./DB.php');
require_once('./response.php');
require_once('./util.php');
require_once('./file.php');

$id = $_GET['id'];
$token = $_GET['token'];
$module = $_GET['module'];

$output = array();

if (!isset($id) || !isset($token) || !isset($module)) { // 如果没有传入module数据
	Response::show(410, "请求的数据不合法");
}

// 如果token不正确或无效
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

if ($module == "project") {
	// 查找所有关注项目的sql语句
	$sql = "SELECT concern_project FROM concern_project WHERE concern_people = " . $id;
	// 执行查询语句
	$result = mysql_query($sql, $connect);
	// 执行返回的结果
	while ($prjId = mysql_fetch_assoc($result)) {
		// 查询关注项目的sql语句
		$sql1 = "SELECT id, project_type, project_name, logo, project_status, project_sum AS money_sum, start_date, deadline, end_date FROM project WHERE id = " . $prjId['concern_project'];
		// 查询关注项目的总投资金额的语句
		$sql2 = "SELECT sum(amount) AS money_invested FROM lot_whereabouts WHERE project_id = " . $prjId['concern_project'];

		// 执行查看关注项目的基本信息
		$r1 = mysql_query($sql1, $connect);
		// 执行查看关注项目的投资总金额
		$r2 = mysql_query($sql2, $connect);

		// 返回关注项目的基本信息
		$project = mysql_fetch_assoc($r1);
		// 返回项目总金额
		$invest = mysql_fetch_assoc($r2);
		// 将总金额放入项目中
		$project['money_invested'] = $invest['money_invested'];
		// 将项目放入输出数组中
		$output[] = $project;
	}
} else if ($module == "person") {
	// 获取当前关注的所有用户的id的sql语句
	$sql = "SELECT concerned_person FROM concern_person WHERE concern_person = " . $id;
	// 执行查询当前用户关注的用户语句
	$result = mysql_query($sql);
	// 返回执行的结果
	while ($personId = mysql_fetch_assoc($result)) {
		// 获取当前给关注的用户的基本信息的sql语句
		$sql1 = "SELECT id, picture, username, identity_mark, special_identity FROM person WHERE id = " . $personId['concerned_person'];

		// 执行查询语句
		$r1 = mysql_query($sql1);

		// 返回执行的结果
		$person = mysql_fetch_assoc($r1);

		if ($person['identity_mark'] == 1) { // 如果该用户为投资人
			// 查询该投资人最近投资的项目
			$sql2 = "SELECT project_id FROM lot_whereabouts WHERE investor_id = " . $person['id'] . " ORDER BY id DESC LIMIT 1";
			// 执行查询
			$r2 = mysql_query($sql2, $connect);
			// 返回执行结果
			$prjId = mysql_fetch_assoc($r2);
			// 查询投资的项目的基本信息的sql语句
			$sql3 = "SELECT id, project_name FROM project WHERE id = " . $prjId['project_id'] . " ORDER BY id DESC LIMIT 1";
			// 执行结果
			$r3 = mysql_query($sql3, $connect);
			// 结果返回
			$project = mysql_fetch_assoc($r3);
			// 将项目的id存入投资人数组中
			$person['project_id'] = $project['id'];
			// 将项目的名称存入投资人数组中
			$person['project_name'] = $project['project_name'];
			// 将被关注的用户存入输出数组中
			$output[] = $person;
		} else if ($person['identity_mark'] == 2) { // 如果该用户为项目人	
			// 查询该项目人最近发布的项目
			$sql4 = "SELECT id, project_name FROM project WHERE id = " . $person['id'] . " ORDER BY id DESC LIMIT 1";
			// 执行查询
			$r4 = mysql_query($sql4, $connect);
			// 返回查询结果
			$project = mysql_fetch_assoc($r4);
			// 将项目的id存入项目人的数组中
			$person['project_id'] = $project['id'];
			// 将项目的名称存入项目人的数组中
			$person['project_name'] = $project['project_name'];
			// 将被关注的用户存入输出数组中
			$output[] = $person;
		}
	}
} else {
	Response::show(410, "请求的数据不合法");
}

if ($output == null) {
	Response::show(420, "数据库表无相关记录");
} else {
	Response::show(200, "查询成功", $output);
}
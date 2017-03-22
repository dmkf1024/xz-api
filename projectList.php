<?php

require_once('./DB.php');
require_once('./response.php');
require_once('./file.php');
require_once('./util.php');

$module = $_GET['module'];
$size = isset($_GET['size'])?$_GET['size']:3;
$id = $_GET['id'];
$token = $_GET['token'];

if (!isset($module)) { // 如果没有传入module数据
	Response::show(410, "请求的数据不合法");
}

try {
	// 连接数据库
	$connect = DB::getInstance()->connect();
} catch (Exception $e) {
	// 如果连接数据库失败，在界面中显示约定好的报错信息
	return Response::show(411, "连接数据库失败");
}


// 获取项目列表的sql语句, 根据请求的参数不同来分
if ($module == "newest") { // 最新项目
	// 查询最新项目的sql语句
	$sql = "SELECT id, logo, project_name, project_status, start_date FROM project ORDER BY start_date DESC LIMIT " . $size;
	// 最新项目的查询结果
	$result = mysql_query($sql, $connect);
	// 遍历，获取每个项目的相关信息
	while ($project = mysql_fetch_assoc($result)) {
		// 获取当前项目的id
		$id = $project['id'];

		// 当前项目查询投资的sql语句
		$sql1 = "SELECT count(*) AS invest_sum, sum(amount) AS money_invested FROM lot_whereabouts WHERE project_id = " . $id;

		// 当前项目查询关注的sql语句
		$sql2 = "SELECT count(*) AS concern_sum FROM concern_project WHERE concern_project = " . $id;
		
		// --执行查询语句
		$r1 = mysql_query($sql1);
		$r2 = mysql_query($sql2);

		// 投资该项目的人数
		$lot = mysql_fetch_assoc($r1);
		// 关注该项目的人数
		$sum2 = mysql_fetch_assoc($r2);

		// 添加投资人数的输出
		$project['invest_sum'] = $lot['invest_sum'];
		// 添加投资人的投资总金额
		$project['money_invested'] = isset($lot['money_invested'])?$lot['money_invested']:"0";
		// 添加关注人数的输出
		$project['concern_sum'] = $sum2['concern_sum'];

		// 添加当前项目相关信息的输出
		$output[] = $project;
	}
	// 输出最新项目的相关信息
	if ($output == null) {
		Response::show(420, "数据库表无相关记录");
	} else {
		Response::show(200, "查询成功", $output);
	}
} else if ($module == "hot") { // 热门项目, 关注数最多的项目
	// 查询关注表记录最多的项目id
	$sql = "SELECT concern_project AS id, count(*) AS concern_sum FROM concern_project GROUP BY concern_project ORDER BY concern_sum DESC LIMIT " . $size;
	
	// 数据库查询的结果
	$result = mysql_query($sql, $connect);

	// 遍历，获取每个项目的相关信息
	while ($concernProject = mysql_fetch_assoc($result)) {
		// 获取项目的id
		$id = $concernProject['id'];
		// 获取关注的数量
		$concernSum = $concernProject['concern_sum'];

		// 当前项目查询投资的sql语句
		$sql1 = "SELECT count(*) AS invest_sum, sum(amount) AS money_invested FROM lot_whereabouts WHERE project_id = " . $id;
		
		// 查询相关的项目详情
		$sql2 = "SELECT id, logo, project_name, project_status, start_date FROM project WHERE id = " . $id;

		// 执行结果
		$r1 = mysql_query($sql1, $connect);
		$r2 = mysql_query($sql2, $connect);
		
		// 投资该项目的人数
		$lot = mysql_fetch_assoc($r1);
		$project = mysql_fetch_assoc($r2);

		// 关注数量
		$project['concern_sum'] = $concernSum;
		// 投资数量
		$project['invest_sum'] = $lot['invest_sum'];
		// 已投资金额
		$project['money_invested'] = isset($lot['money_invested'])?$lot['money_invested']:"0";

		// 将项目相关信息添加到输出中
		$output[] = $project;
	}
	if ($output == null) {
		Response::show(420, "数据库表无相关记录");
	} else {
		Response::show(200, "查询成功", $output);
	}
} else if ($module == "concern") { // 如果是我的关注
	if (!isset($id) || !isset($token)) { // 如果没有传入ID或Token
		Response::show(410, "请求的数据不合法");
	} else if (!Util::isTokenValid($token, $id)) { // 如果token无效
		return;
	}

	// 查询当前用户关注的所有项目的id的sql语句
	$sql = "SELECT concern_project FROM concern_project WHERE concern_people = " . $id . " LIMIT " . $size;
	
	$result = mysql_query($sql, $connect);
	
	while ($ids = mysql_fetch_assoc($result)) {
		// 获取到当前用户关注的每一个ID
		$prjId = $ids['concern_project'];
		
		// 根据项目的id查询项目的相关信息
		$sql1 = "SELECT id, logo, project_name, project_status, start_date FROM project WHERE id = " . $prjId;
		// 根据项目的id查询当前项目的投资数
		$sql2 = "SELECT count(*) AS invest_sum, sum(amount) AS money_invested FROM lot_whereabouts WHERE project_id = " . $prjId;
		// 根据项目的id产讯当前项目的关注数
		$sql3 = "SELECT count(*) AS concern_sum FROM concern_project WHERE concern_people = " . $prjId;

		// 项目相关信息查询结果
		$r1 = mysql_query($sql1, $connect);
		$project = mysql_fetch_assoc($r1);
		// 项目投资数及投资金额查询结果
		$r2 = mysql_query($sql2, $connect);
		$investInfo = mysql_fetch_assoc($r2);
		// 项目关注数的查询结果
		$r3 = mysql_query($sql3, $connect);
		$concernInfo = mysql_fetch_assoc($r3);

		$project['concern_sum'] = $concernInfo['concern_sum'];
		$project['invest_sum'] = $investInfo['invest_sum'];
		$project['money_invested'] = isset($investInfo['money_invested'])?$investInfo['money_invested']:"0";

		$output[] = $project;
	}
	if ($output == null) {
		Response::show(420, "数据库表无相关记录");
	} else {
		Response::show(200, "查询成功", $output);
	}
} else {
	Response::show(410, "请求的数据不合法");
}
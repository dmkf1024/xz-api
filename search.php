<?php
require_once('./DB.php');
require_once('./response.php');

$keyword = $_GET['keyword'];

$output = array();

if (!isset($keyword)) {
	Response::show(410, "请输入搜索的关键字");
}

try {
	// 连接数据库
	$connect = DB::getInstance()->connect();
} catch (Exception $e) {
	// 如果连接数据库失败，在界面中显示约定好的报错信息
	return Response::show(411, "连接数据库失败");
}

// 关键字搜索项目的sql语句
$sql = "SELECT id, logo, project_name, project_status FROM project WHERE project_name LIKE '%" . $keyword . "%'";
// 执行查询语句
$result = mysql_query($sql, $connect);
// 返回执行结果
while ($project = mysql_fetch_assoc($result)) {
	// 获取项目的id
	$prjId = $project['id'];
	// 获取关注数的sql语句
	$sql1 = "SELECT * FROM concern_project WHERE concern_project = " . $prjId;
	// 执行查询语句
	$r1 = mysql_query($sql1, $connect);
	// 获取关注数目
	$concernSum = mysql_num_rows($r1);
	// 将关注数添加到项目中
	$project['concern_sum'] = "$concernSum";
	// 查询投资信息的sql语句
	$sql2 = "SELECT count(*) AS invest_sum, sum(amount) AS money_invested FROM lot_whereabouts WHERE project_id = " . $prjId;
	// 执行查询语句
	$r2 = mysql_query($sql2);
	// 返回查询结果
	$invest = mysql_fetch_assoc($r2);
	// 将投资人数添加到项目中
	$project['invest_sum'] = $invest['invest_sum'];
	// 将投资总金额添加到项目中
	$project['money_invested'] = $invest['invest_sum'];
	// 将项目添加到输出数组中
	$output[] = $project;
}
if ($output == null) {
	Response::show(420, "数据库表无相关记录");
} else {
	Response::show(200, "查询成功", $output);
}
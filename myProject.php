<?php

require_once('./DB.php');
require_once('./response.php');
require_once('./file.php');
require_once('./util.php');

// 1：投资人， 2：项目人
$type = $_GET['type'];
$id = $_GET['id'];
$token = $_GET['token'];

$output = array();

// 如果请求的数据不全
if (!isset($type) || !isset($id) || !isset($token)) {
	Response::show(410, "请求的数据不合法");
}

try { // 连接数据库
	$connect = DB::getInstance()->connect();
} catch(Exception $e) { // 返回访问失败信息
	Response::show(410, "请求的数据不合法");
}

if ($type == "1") { // 投资人
	// 根据投资人id查找投资相关信息
	$sql = "SELECT project_id, invest_date, amount FROM lot_whereabouts WHERE investor_id = " . $id;
	// 投资人投资相关信息查询结果
	$result = mysql_query($sql);
	// 遍历投资人投资的各个项目
	while ($invest = mysql_fetch_assoc($result)) {
		// 查询投资人所有投资的投资项目sql语句
		$sql1 = "SELECT id, logo, project_name, project_type, project_status FROM project WHERE id = " . $invest['project_id'];
		// 执行查询语句
		$r1 = mysql_query($sql1);
		// 返回查询结果
		$project = mysql_fetch_assoc($r1);
		// 将投资时间存入项目中
		$project['invest_date'] = $invest['invest_date'];
		// 将投资金额放入项目中
		$project['money_invested'] = $invest['amount'];
		// 将项目放入输出数组中
		$output[] = $project;
	}
} else if ($type == "2") { // 项目人
	// 查询项目人发布的所有项目的sql语句
	$sql = "SELECT id, project_name, project_type, project_status, logo, start_date, project_sum FROM project WHERE person_id = " . $id;

	// 所有项目的查询结果
	$result = mysql_query($sql, $connect);
	// 遍历每一个项目的查询结果
	while ($project = mysql_fetch_assoc($result)) {
		// 当前项目所有投资人相关情况的数组声明
		$investors = array();

		// 获取当前项目的id
		$prjId = $project['id'];

		// 获取当前项目已筹集资金的总金额
		$sql1 = "SELECT sum(amount) AS money_invested FROM lot_whereabouts WHERE project_id = " . $prjId;
		
		// 当前项目所有的投资人
		$sql2 = "SELECT investor_id, amount FROM lot_whereabouts WHERE project_id = " . $prjId;
		

		// 当前项目已筹集资金的查询结果
		$r1 = mysql_query($sql1, $connect);
		// 当前项目所有投资人ID的查询结果
		$r2 = mysql_query($sql2, $connect);

		// 已筹集资金的结果返回的资源
		$fund = mysql_fetch_assoc($r1);
		// 将已筹集资金的总和放入项目输出结果中
		$project['money_invested'] = isset($fund['money_invested'])?$fund['money_invested']:"0";

		// 当前项目所有投资人信息的返回结果
		while ($person = mysql_fetch_assoc($r2)) {
			// 当前投资人的信息查询语句
			$sql3 = "SELECT id, name, special_identity, picture FROM person WHERE id = " . $person['investor_id'];
			
			// 当前投资人的信息查询结果
			$r3 = mysql_query($sql3, $connect);
			// 当前投资人结果返回
			$investor = mysql_fetch_assoc($r3);
			// 将当前投资人的投资金额加入到投资人对象中
			$investor['money_invested'] = $person['amount'];
			// 将投资人对象存入投资人列表中
			$investors[] = $investor;
		}
		// 将投资人列表存入当前关注的项目中
		$project['investors'] = $investors;
		// 将当前的项目存入输出数组中
		$output[] = $project;
	}
}

if ($output == null) { // 如果输出结果为空
	Response::show(420, "数据库表无相关记录");
} else { // 如果输出有结果
	Response::show(200, "查询成功", $output);
}
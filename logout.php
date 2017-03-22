<?php

require_once('./file.php');
require_once('./file.php');
require_once('./util.php');
require_once('./response.php');

$id = $_GET['id'];
$token = $_GET['token'];

if (!isset($id) || !isset($token)) {
	Response::show(410, "请求的数据不合法");
}

Util::logout($token, $id);

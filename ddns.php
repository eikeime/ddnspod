<?php
include_once 'dns.operator.class.php';
//初始化参数和组件
$username = NULL;
$password = NULL;
$o = NULL;
if ($argc >= 1) {
	//接受命令行参数
	$username = $argv[1];
	$password = $argv[2];
} else if (!empty($_GET)) {
	//接受GET参数
	$username = $_GET['username'];
	$password = $_GET['password'];
} else {
	exit();
}
if (empty($password) || empty($username)) {
	exit();
} else {
	$o = new DNSOperator($username, $password);
}
$mc = new Memcache();
$mc -> connect('localhost', 11211);
$log = null;

$c = curl_init();
//操作curl获取当前ip
curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'http://teatreek.sinaapp.com/dnspod/getip.php'));
$newIp = curl_exec($c);
//处理获取到的ip
if ($newIp != $mc -> get('ddnsIp')) {
	$ddnsinfo = $o -> recordInfo(1836472, 17037860);
	if ($newIp != $ddnsinfo['record']['value']) {
		$o -> recordDdns(1836472, 17037860, 'ddns');
		$log = array('updatetime' => new MongoDate(), 'ip' => $newIp);

	}
	$mc -> set('ddnsIp', $newIp);
	$mc -> set('ddnsTime', time());
} else {
	$time = time();
	if (($time - $mc -> get('ddnsTime')) > 360) {
		$ddnsinfo = $o -> recordInfo(1836472, 17037860);
		if ($newIp != $ddnsinfo['record']['value']) {
			$o -> recordDdns(1836472, 17037860, 'ddns');
			$log = array('updatetime' => new MongoDate(), 'ip' => $newIp);
		}
	}
	$mc -> set('ddnsTime', $time);
}
if (!$mc -> get('ddnsLog')) {
	$mc -> add('ddnsLog', 1);
}
$mc -> increment('ddnsLog');
if ($mc -> get('ddnsLog') > 1440) {
	$mc -> set('ddnsLog', 1);
}
curl_close($c);
$mc -> close();
if ($log) {
	try {
		$mlink = new Mongo();
		$mongolog = $mlink -> ddns -> log;
		$mongolog -> insert($log);
		$mlink -> close();
	} catch(exception $e) {
		// gotcha
	}
}
?>
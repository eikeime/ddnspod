<?php
$mc = new Memcache();
$mc -> connect('localhost', 11211);
echo '循环计数器：' . $mc -> get('ddnsLog') . '<br>';
$mc -> close();
echo "<br><a href = 'mongoinit.php'>初始化数据库</a><br>";
$mlink = new Mongo();
$mlog = $mlink -> ddns -> log;
echo 'Log总数：' . $mlog -> count() . '<br>';
$cursor = $mlog -> find();
while ($cursor -> hasNext()) {
	$log = $cursor -> current();
	if ($log) {
		echo "-------------------------------<br>";
		echo $log['_id'] . '<br>';
		echo date('Y-M-d H:i:s', $log['updatetime'] -> sec) . '<br>';
		echo $log['ip'] . '<br>';
		//var_dump($log);
	}
	$cursor -> next();
}
$mlink -> close();

?>
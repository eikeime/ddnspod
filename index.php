<?php
$mc = new Memcache();
$mc -> connect('localhost', 11211);

echo '循环计数器：' . $mc -> get('ddnsLog') . '<br>';
$mc -> close();
$mlink = new Mongo();
$mlog = $mlink -> ddns -> log;
echo 'Log总数：' . $mlog -> count() . '<br>';
//$mlog -> drop();
//$mlog -> insert(array('updatetime' => new MongoDate(time() - 7 * 24 * 60 * 60 - 60), 'ip' => '192.168.1.1'));
//$mlog -> deleteIndex('ttl');
//$mlog -> ensureIndex('updatetime', array('expireAfterSeconds' => 7 * 24 * 60 * 60, 'name' => 'ttl'));
//$mlog -> remove(array('_id' => new MongoId('需要移除的key')));
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